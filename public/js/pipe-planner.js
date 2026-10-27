/* ============================================================
   ALKOBLAN - PIPE-PLANNER.JS
   Guided wizard for the AI Pipe Planner. All AI work happens on the
   server; this script only talks to the /pipe-planner JSON endpoints.
   Strings come from window.PIPE_PLANNER.i18n (resources/lang/*.json, "pp." keys).
   ============================================================ */

(function () {
    'use strict';

    const CFG = window.PIPE_PLANNER || {};
    const I18N = CFG.i18n || {};
    const RTL = document.documentElement.dir === 'rtl';
    const STORE_KEY = 'pipePlanner.reference';
    const STAGES = ['uploading', 'analyzing_images', 'checking_products', 'preparing_report'];
    const POLL_MS = 3000;
    const CLIENT_TIMEOUT_MS = 8 * 60 * 1000;

    const t = (key, vars) => {
        let s = I18N[key] || key;
        if (vars) Object.keys(vars).forEach(k => { s = s.replace(':' + k, vars[k]); });
        return s;
    };
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const num = (v) => (v === '' || v === null || v === undefined || isNaN(Number(v))) ? null : Number(v);
    const fmt = (n) => n === null || n === undefined ? '—' : Number(n).toLocaleString(RTL ? 'ar-SA' : 'en-US', { maximumFractionDigits: 2 });

    const state = { project: null, analysis: null, step: 1, pollTimer: null, pollStarted: 0 };

    // ------------------------------------------------------------------
    // HTTP
    // ------------------------------------------------------------------
    async function api(method, url, body, isForm) {
        // X-Requested-With marks these as AJAX so Laravel doesn't store them as the "previous URL" used by back().
        const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]')?.content || '' };
        if (body && !isForm) headers['Content-Type'] = 'application/json';
        let res;
        try {
            res = await fetch(url, { method, headers, credentials: 'same-origin', body: body ? (isForm ? body : JSON.stringify(body)) : undefined });
        } catch (e) {
            throw { status: 0, message: t('pp.error.network') };
        }
        let data = null;
        try { data = await res.json(); } catch (e) { /* non-JSON */ }
        if (!res.ok) throw { status: res.status, message: data?.message || t('pp.error.generic'), errors: data?.errors || {}, code: data?.error };
        return data;
    }

    function uploadWithProgress(url, formData, onProgress) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]')?.content || '');
            xhr.upload.onprogress = (e) => { if (e.lengthComputable) onProgress(Math.round(e.loaded / e.total * 100)); };
            xhr.onload = () => {
                let data = null;
                try { data = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }
                if (xhr.status >= 200 && xhr.status < 300) resolve(data);
                else {
                    const firstErr = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
                    reject({ status: xhr.status, message: firstErr || data?.message || (xhr.status === 413 ? t('pp.photos.too_large_server') : t('pp.error.generic')) });
                }
            };
            xhr.onerror = () => reject({ status: 0, message: t('pp.error.network') });
            xhr.send(formData);
        });
    }

    const projectUrl = (suffix = '') => `${CFG.base}/projects/${encodeURIComponent(state.project.reference)}${suffix}`;

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------
    function goTo(step, scroll = true) {
        if (step > 1 && !state.project) step = 1;
        state.step = step;
        $$('.pp-step').forEach(el => { el.hidden = Number(el.dataset.step) !== step; });
        $$('.pp-progress li').forEach(li => {
            const n = Number(li.dataset.step);
            li.classList.toggle('active', n === step);
            li.classList.toggle('done', n < step);
            if (n === step) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
        });
        const heading = $(`.pp-step[data-step="${step}"] h2`);
        if (heading && scroll) { heading.setAttribute('tabindex', '-1'); heading.focus({ preventScroll: true }); }
        if (scroll) $('#ppWizard').scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (step === 2) renderImages();
        if (step === 3) renderQuestionsInMeasurements();
    }

    function setBusy(btn, busy) {
        if (!btn) return;
        btn.disabled = busy;
        btn.classList.toggle('pp-busy', busy);
    }

    function showAlert(container, message, kind = 'error') {
        container.innerHTML = message ? `<div class="pp-alert pp-alert-${kind}" role="alert">${esc(message)}</div>` : '';
    }

    function clearFieldErrors(form) {
        $$('.pp-field-error', form).forEach(e => e.remove());
        $$('[aria-invalid="true"]', form).forEach(e => e.removeAttribute('aria-invalid'));
    }

    function showFieldErrors(form, errors) {
        Object.entries(errors || {}).forEach(([field, msgs]) => {
            const name = field.replace(/\.(\w+)/g, '[$1]');
            const input = form.querySelector(`[name="${name}"]`) || form.querySelector(`[data-field="${field}"]`);
            const msg = document.createElement('small');
            msg.className = 'pp-field-error';
            msg.textContent = msgs[0];
            if (input) {
                input.setAttribute('aria-invalid', 'true');
                (input.closest('.pp-field') || input.parentElement).appendChild(msg);
            }
        });
    }

    // ------------------------------------------------------------------
    // Step 1: project details
    // ------------------------------------------------------------------
    const FIXTURE_RULES = {
        bathrooms: ['apartment', 'villa', 'bathroom', 'commercial', 'other'],
        kitchens: ['apartment', 'villa', 'kitchen', 'commercial', 'other'],
        washing_machines: ['apartment', 'villa', 'other'],
        outdoor_taps: ['villa', 'commercial', 'other'],
    };

    function refreshRelevantFields() {
        const form = $('#ppDetailsForm');
        const type = form.querySelector('[name="project_type"]:checked')?.value;
        const system = form.querySelector('[name="system_type"]:checked')?.value;
        Object.entries(FIXTURE_RULES).forEach(([field, types]) => {
            const wrap = form.querySelector(`[data-fixture="${field}"]`);
            if (wrap) wrap.hidden = !!type && !types.includes(type);
        });
        const heater = form.querySelector('[data-fixture="water_heaters"]');
        if (heater) heater.hidden = !(system === 'hot' || system === 'combined');
        const heaterLoc = $('#ppMeasurementsForm [data-field="measurements.heater_location"]');
        if (heaterLoc) heaterLoc.closest('.pp-field').hidden = !(system === 'hot' || system === 'combined');
    }

    function detailsPayload() {
        const form = $('#ppDetailsForm');
        const val = (n) => form.querySelector(`[name="${n}"]:checked`)?.value || form.querySelector(`[name="${n}"]`)?.value || null;
        const fixtures = {};
        $$('[data-fixture]', form).forEach(wrap => {
            const input = $('input', wrap);
            if (!wrap.hidden && input.value !== '') fixtures[wrap.dataset.fixture] = input.type === 'number' ? num(input.value) : input.value;
        });
        const other = form.querySelector('[name="fixtures[other]"]').value.trim();
        if (other) fixtures.other = other;
        return {
            project_type: form.querySelector('[name="project_type"]:checked')?.value,
            project_status: form.querySelector('[name="project_status"]:checked')?.value,
            system_type: form.querySelector('[name="system_type"]:checked')?.value,
            scope: val('scope') || 'complete',
            fixtures,
            location: form.querySelector('[name="location"]').value.trim() || null,
            notes: form.querySelector('[name="notes"]').value.trim() || null,
        };
    }

    function fillDetails(p) {
        const form = $('#ppDetailsForm');
        ['project_type', 'project_status', 'system_type', 'scope'].forEach(n => {
            const r = form.querySelector(`[name="${n}"][value="${p[n]}"]`);
            if (r) r.checked = true;
        });
        Object.entries(p.fixtures || {}).forEach(([k, v]) => {
            const i = form.querySelector(`[name="fixtures[${k}]"]`);
            if (i) i.value = v ?? '';
        });
        form.querySelector('[name="location"]').value = p.location || '';
        form.querySelector('[name="notes"]').value = p.notes || '';
        refreshRelevantFields();
    }

    async function saveDetails(e) {
        e.preventDefault();
        const form = $('#ppDetailsForm');
        const btn = e.submitter || $('button[type="submit"]', form);
        clearFieldErrors(form);
        showAlert($('#ppDetailsAlert'), '');
        const data = detailsPayload();
        const missing = {};
        ['project_type', 'project_status', 'system_type'].forEach(k => { if (!data[k]) missing[k] = [t('pp.validation.required')]; });
        if (Object.keys(missing).length) { showFieldErrors(form, missing); return; }

        setBusy(btn, true);
        try {
            const res = state.project
                ? await api('PATCH', projectUrl(), data)
                : await api('POST', `${CFG.base}/projects`, data);
            setProject(res.data);
            goTo(2);
        } catch (err) {
            if (err.errors) showFieldErrors(form, err.errors);
            showAlert($('#ppDetailsAlert'), err.message);
        } finally {
            setBusy(btn, false);
        }
    }

    function setProject(p) {
        state.project = p;
        try { localStorage.setItem(STORE_KEY, p.reference); } catch (e) { /* storage unavailable */ }
        const url = new URL(location.href);
        url.searchParams.set('project', p.reference);
        history.replaceState(null, '', url);
        $$('.pp-reference').forEach(el => { el.textContent = p.reference; el.closest('[hidden]')?.removeAttribute('hidden'); });
    }

    // ------------------------------------------------------------------
    // Step 2: photos
    // ------------------------------------------------------------------
    function validateFile(file) {
        if (!CFG.limits.mimes.includes(file.type)) return t('pp.photos.bad_type', { name: file.name });
        if (file.size > CFG.limits.max_image_kb * 1024) return t('pp.photos.too_large', { name: file.name, max: Math.round(CFG.limits.max_image_kb / 1024) });
        return null;
    }

    async function handleFiles(files, replaceId) {
        const alert = $('#ppPhotosAlert');
        showAlert(alert, '');
        const list = Array.from(files);
        const errors = [];
        for (const file of list) {
            const err = validateFile(file);
            if (err) { errors.push(err); continue; }
            if (!replaceId && state.project.images.length >= CFG.limits.max_images) {
                errors.push(t('pp.photos.too_many', { max: CFG.limits.max_images }));
                break;
            }
            const tmpId = 'up' + Math.random().toString(36).slice(2);
            const row = document.createElement('li');
            row.className = 'pp-photo pp-photo-uploading';
            row.id = tmpId;
            row.innerHTML = `<div class="pp-photo-thumb"></div><div class="pp-photo-body"><strong>${esc(file.name)}</strong>
                <div class="pp-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="${esc(t('pp.photos.uploading'))}"><span style="width:0%"></span></div></div>`;
            $('#ppPhotoList').appendChild(row);

            const fd = new FormData();
            fd.append('image', file);
            try {
                const res = await uploadWithProgress(projectUrl('/images'), fd, (pct) => {
                    $('.pp-bar span', row).style.width = pct + '%';
                    $('.pp-bar', row).setAttribute('aria-valuenow', pct);
                });
                if (replaceId) {
                    const old = state.project.images.find(i => i.id === replaceId);
                    res.data.caption = old?.caption || null;
                    res.data.has_scale_reference = !!old?.has_scale_reference;
                    await api('PATCH', projectUrl(`/images/${res.data.id}`), { caption: res.data.caption, has_scale_reference: res.data.has_scale_reference });
                    await api('DELETE', projectUrl(`/images/${replaceId}`));
                    state.project.images = state.project.images.map(i => i.id === replaceId ? res.data : i);
                } else {
                    state.project.images.push(res.data);
                }
            } catch (err) {
                errors.push(`${file.name}: ${err.message}`);
            } finally {
                row.remove();
                renderImages();
            }
        }
        if (errors.length) alert.innerHTML = `<div class="pp-alert pp-alert-error" role="alert"><ul>${errors.map(e => `<li>${esc(e)}</li>`).join('')}</ul></div>`;
    }

    function renderImages() {
        if (!state.project) return;
        const list = $('#ppPhotoList');
        $$('.pp-photo:not(.pp-photo-uploading)', list).forEach(el => el.remove());
        state.project.images.forEach((img, idx) => {
            const li = document.createElement('li');
            li.className = 'pp-photo';
            li.innerHTML = `
                <img class="pp-photo-thumb" src="${esc(img.url)}" alt="${esc(t('pp.photos.photo_n', { n: idx + 1 }))}" loading="lazy">
                <div class="pp-photo-body">
                    <label class="pp-field"><span>${esc(t('pp.photos.caption'))}</span>
                        <input type="text" class="form-control" maxlength="300" value="${esc(img.caption || '')}" data-caption="${img.id}" placeholder="${esc(t('pp.photos.caption_ph'))}"></label>
                    <label class="pp-check"><input type="checkbox" data-scale="${img.id}" ${img.has_scale_reference ? 'checked' : ''}> ${esc(t('pp.photos.has_scale'))}</label>
                    <div class="pp-photo-actions">
                        <label class="btn btn-sm btn-outline pp-file-btn">${esc(t('pp.photos.replace'))}
                            <input type="file" accept="${CFG.limits.mimes.join(',')}" data-replace="${img.id}" class="pp-visually-hidden"></label>
                        <button type="button" class="btn btn-sm btn-outline pp-danger" data-remove="${img.id}">${esc(t('pp.photos.remove'))}</button>
                    </div>
                </div>`;
            list.appendChild(li);
        });
        $('#ppPhotoCount').textContent = t('pp.photos.count', { n: state.project.images.length, max: CFG.limits.max_images });
        $('#ppPhotosEmpty').hidden = state.project.images.length > 0;
    }

    function bindPhotoEvents() {
        const drop = $('#ppDropzone');
        ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.add('dragging'); }));
        ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.remove('dragging'); }));
        drop.addEventListener('drop', (e) => handleFiles(e.dataTransfer.files));
        $$('#ppFileInput, #ppCameraInput').forEach(inp => inp.addEventListener('change', (e) => { handleFiles(e.target.files); e.target.value = ''; }));

        const list = $('#ppPhotoList');
        list.addEventListener('click', async (e) => {
            const id = Number(e.target.dataset.remove);
            if (!id) return;
            if (!confirm(t('pp.photos.confirm_remove'))) return;
            try {
                await api('DELETE', projectUrl(`/images/${id}`));
                state.project.images = state.project.images.filter(i => i.id !== id);
                renderImages();
            } catch (err) { showAlert($('#ppPhotosAlert'), err.message); }
        });
        list.addEventListener('change', async (e) => {
            const el = e.target;
            if (el.dataset.replace) { handleFiles(el.files, Number(el.dataset.replace)); return; }
            const id = Number(el.dataset.caption || el.dataset.scale);
            if (!id) return;
            const body = el.dataset.caption ? { caption: el.value.trim() || null } : { has_scale_reference: el.checked };
            try {
                const res = await api('PATCH', projectUrl(`/images/${id}`), body);
                state.project.images = state.project.images.map(i => i.id === id ? res.data : i);
            } catch (err) { showAlert($('#ppPhotosAlert'), err.message); }
        });
    }

    // ------------------------------------------------------------------
    // Step 3: measurements & clarifications
    // ------------------------------------------------------------------
    function distanceRow(d = {}) {
        const id = d.id || 'd' + Math.random().toString(36).slice(2, 8);
        const unknown = d.value === null && d.from;
        const li = document.createElement('li');
        li.className = 'pp-distance';
        li.dataset.id = id;
        li.innerHTML = `
            <label class="pp-field"><span>${esc(t('pp.measure.from'))}</span><input class="form-control" data-k="from" maxlength="100" value="${esc(d.from || '')}" placeholder="${esc(t('pp.measure.from_ph'))}"></label>
            <label class="pp-field"><span>${esc(t('pp.measure.to'))}</span><input class="form-control" data-k="to" maxlength="100" value="${esc(d.to || '')}" placeholder="${esc(t('pp.measure.to_ph'))}"></label>
            <label class="pp-field pp-narrow"><span>${esc(t('pp.measure.distance'))}</span><input class="form-control" type="number" min="0" step="any" inputmode="decimal" data-k="value" value="${d.value ?? ''}" ${unknown ? 'disabled' : ''}></label>
            <label class="pp-check"><input type="checkbox" data-k="unknown" ${unknown ? 'checked' : ''}> ${esc(t('pp.measure.dont_know'))}</label>
            <button type="button" class="pp-icon-btn" data-remove-distance aria-label="${esc(t('pp.measure.remove_distance'))}"><i class="bi bi-x-lg"></i></button>`;
        return li;
    }

    function fillMeasurements(p) {
        const form = $('#ppMeasurementsForm');
        const m = p.measurements || {};
        form.querySelector('[name="unit"]').value = p.unit || 'm';
        ['length', 'width', 'height'].forEach(k => {
            const v = m.room?.[k];
            const input = form.querySelector(`[name="room_${k}"]`);
            const unknown = form.querySelector(`[data-unknown="room_${k}"]`);
            input.value = v ?? '';
            unknown.checked = m.room && k in m.room && v === null;
            input.disabled = unknown.checked;
        });
        ['water_source', 'heater_location', 'known_diameters', 'elevations', 'details'].forEach(k => {
            form.querySelector(`[name="${k}"]`).value = m[k] || '';
        });
        const list = $('#ppDistances');
        list.innerHTML = '';
        (m.distances || []).forEach(d => list.appendChild(distanceRow(d)));
    }

    function measurementsPayload() {
        const form = $('#ppMeasurementsForm');
        const room = {};
        ['length', 'width', 'height'].forEach(k => {
            const unknown = form.querySelector(`[data-unknown="room_${k}"]`).checked;
            const v = num(form.querySelector(`[name="room_${k}"]`).value);
            if (unknown) room[k] = null; else if (v !== null) room[k] = v;
        });
        const distances = $$('#ppDistances .pp-distance').map(li => ({
            id: li.dataset.id,
            from: $('[data-k="from"]', li).value.trim(),
            to: $('[data-k="to"]', li).value.trim(),
            value: $('[data-k="unknown"]', li).checked ? null : num($('[data-k="value"]', li).value),
            unit: form.querySelector('[name="unit"]').value,
        })).filter(d => d.from || d.to);
        const text = (k) => form.querySelector(`[name="${k}"]`).value.trim() || null;
        return {
            unit: form.querySelector('[name="unit"]').value,
            measurements: {
                room, distances,
                water_source: text('water_source'), heater_location: text('heater_location'),
                known_diameters: text('known_diameters'), elevations: text('elevations'), details: text('details'),
            },
            answers: collectAnswers(),
        };
    }

    function collectAnswers() {
        const existing = (state.project?.answers || []).slice();
        $$('[data-answer]').forEach(input => {
            const qid = input.dataset.answer;
            const answer = input.value.trim();
            const idx = existing.findIndex(a => a.question_id === qid);
            const entry = { question_id: qid, question: input.dataset.question, answer: answer || null };
            if (idx >= 0) existing[idx] = entry; else if (answer) existing.push(entry);
        });
        return existing.slice(-30);
    }

    function questionsHtml(questions) {
        if (!questions?.length) return '';
        const answers = state.project?.answers || [];
        return `<ol class="pp-questions">${questions.map(q => {
            const prev = answers.find(a => a.question_id === q.id)?.answer || '';
            return `<li><label class="pp-field"><span>${esc(q.question)}</span>
                ${q.why ? `<small class="pp-muted">${esc(q.why)}</small>` : ''}
                <textarea class="form-control" rows="2" maxlength="1000" data-answer="${esc(q.id)}" data-question="${esc(q.question)}">${esc(prev)}</textarea></label></li>`;
        }).join('')}</ol>`;
    }

    function renderQuestionsInMeasurements() {
        const box = $('#ppOpenQuestions');
        const qs = state.analysis?.clarification_questions || [];
        box.hidden = !qs.length;
        $('#ppOpenQuestionsList').innerHTML = questionsHtml(qs);
    }

    async function saveMeasurements(e) {
        e.preventDefault();
        const form = $('#ppMeasurementsForm');
        const btn = e.submitter || $('button[type="submit"]', form);
        clearFieldErrors(form);
        showAlert($('#ppMeasureAlert'), '');
        setBusy(btn, true);
        try {
            const res = await api('PATCH', projectUrl(), measurementsPayload());
            setProject(res.data);
            goTo(4);
            renderAnalysisIntro();
        } catch (err) {
            if (err.errors) showFieldErrors(form, err.errors);
            showAlert($('#ppMeasureAlert'), err.message);
        } finally {
            setBusy(btn, false);
        }
    }

    function bindMeasurementEvents() {
        const form = $('#ppMeasurementsForm');
        $('#ppAddDistance').addEventListener('click', () => {
            const row = distanceRow();
            $('#ppDistances').appendChild(row);
            $('input', row).focus();
        });
        form.addEventListener('change', (e) => {
            if (e.target.dataset.unknown) {
                const input = form.querySelector(`[name="${e.target.dataset.unknown}"]`);
                input.disabled = e.target.checked;
                if (e.target.checked) input.value = '';
            }
            if (e.target.dataset.k === 'unknown') {
                const input = $('[data-k="value"]', e.target.closest('.pp-distance'));
                input.disabled = e.target.checked;
                if (e.target.checked) input.value = '';
            }
        });
        form.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-remove-distance]');
            if (btn) btn.closest('.pp-distance').remove();
        });
        form.addEventListener('submit', saveMeasurements);
    }

    // ------------------------------------------------------------------
    // Step 4: analysis
    // ------------------------------------------------------------------
    function renderAnalysisIntro() {
        const p = state.project;
        $('#ppAnalyzeSummary').innerHTML = `
            <li>${esc(t('pp.summary.photos'))}: <strong>${p.images.length}</strong></li>
            <li>${esc(t('pp.summary.distances'))}: <strong>${(p.measurements?.distances || []).filter(d => d.value !== null).length}</strong></li>
            <li>${esc(t('pp.summary.answers'))}: <strong>${(p.answers || []).filter(a => a.answer).length}</strong></li>`;
        $('#ppAnalyzeNoPhotos').hidden = p.images.length > 0;
        $('#ppStartAnalysis').disabled = p.images.length === 0 || !CFG.available;
        renderStages(null);
        showAlert($('#ppAnalyzeAlert'), '');
    }

    function renderStages(analysis) {
        const current = analysis ? STAGES.indexOf(analysis.stage) : -1;
        const done = analysis?.status === 'completed';
        $('#ppStages').innerHTML = STAGES.map((s, i) => {
            const cls = done || i < current ? 'done' : (i === current ? 'active' : '');
            return `<li class="${cls}">${cls === 'active' ? '<span class="pp-spinner" aria-hidden="true"></span>' : (cls === 'done' ? '<i class="bi bi-check-circle-fill" aria-hidden="true"></i>' : '<i class="bi bi-circle" aria-hidden="true"></i>')} ${esc(t('pp.stage.' + s))}</li>`;
        }).join('');
        $('#ppStagesWrap').hidden = !analysis;
    }

    async function startAnalysis() {
        const btn = $('#ppStartAnalysis');
        showAlert($('#ppAnalyzeAlert'), '');
        setBusy(btn, true);
        try {
            const res = await api('POST', projectUrl('/analyses'));
            state.analysis = res.data;
            if (res.reused && res.data.status === 'completed') showAlert($('#ppAnalyzeAlert'), t('pp.analyze.reused'), 'info');
            handleAnalysisState();
        } catch (err) {
            showAlert($('#ppAnalyzeAlert'), err.message);
            setBusy(btn, false);
        }
    }

    function handleAnalysisState() {
        const a = state.analysis;
        renderStages(a);
        if (a.status === 'completed') {
            stopPolling();
            setBusy($('#ppStartAnalysis'), false);
            renderResults();
            goTo(5);
        } else if (a.status === 'failed') {
            stopPolling();
            setBusy($('#ppStartAnalysis'), false);
            const key = 'pp.failure.' + (a.failure_category || 'internal_error');
            showAlert($('#ppAnalyzeAlert'), (I18N[key] || t('pp.failure.internal_error')) + ' ' + t('pp.analyze.retry_hint'));
        } else {
            startPolling();
        }
    }

    function startPolling() {
        if (state.pollTimer) return;
        state.pollStarted = Date.now();
        state.pollTimer = setInterval(async () => {
            if (Date.now() - state.pollStarted > CLIENT_TIMEOUT_MS) {
                stopPolling();
                setBusy($('#ppStartAnalysis'), false);
                showAlert($('#ppAnalyzeAlert'), t('pp.analyze.slow'));
                return;
            }
            try {
                const res = await api('GET', projectUrl(`/analyses/${state.analysis.version}`));
                state.analysis = res.data;
                handleAnalysisState();
            } catch (err) { /* transient: keep polling until timeout */ }
        }, POLL_MS);
    }

    function stopPolling() {
        clearInterval(state.pollTimer);
        state.pollTimer = null;
    }

    // ------------------------------------------------------------------
    // Step 5: results
    // ------------------------------------------------------------------
    const badge = (key, cls) => `<span class="pp-badge pp-badge-${cls || key}">${esc(t('pp.status.' + key))}</span>`;
    const QTY_CLASS = { calculated: 'ok', preliminary_estimate: 'warn', partial: 'warn', assumption: 'warn', cannot_calculate: 'bad' };

    function renderResults() {
        const a = state.analysis;
        const p = state.project;
        const root = $('#ppResults');

        const readinessCls = { ready_for_preliminary_quote: 'ok', needs_more_information: 'warn', requires_professional_review: 'bad' }[a.readiness] || 'warn';
        const fixtures = Object.entries(p.fixtures || {}).filter(([, v]) => v !== null && v !== '' && v !== 0)
            .map(([k, v]) => `${esc(t('pp.fixture.' + k))}: ${esc(v)}`).join(' · ') || '—';
        const room = p.measurements?.room || {};
        const roomTxt = ['length', 'width', 'height'].map(k => room[k] === undefined ? null : `${t('pp.measure.room_' + k)}: ${room[k] === null ? t('pp.measure.unknown') : fmt(room[k]) + ' ' + p.unit}`).filter(Boolean).join(' · ') || '—';

        root.innerHTML = `
        <section class="pp-card" aria-labelledby="ppA">
            <h3 id="ppA">${esc(t('pp.results.summary'))}</h3>
            <dl class="pp-dl">
                <dt>${esc(t('pp.results.reference'))}</dt><dd><code>${esc(p.reference)}</code> · ${esc(t('pp.results.version', { v: a.version }))}</dd>
                <dt>${esc(t('pp.details.project_type'))}</dt><dd>${esc(t('pp.type.' + p.project_type))} · ${esc(t('pp.pstatus.' + p.project_status))}</dd>
                <dt>${esc(t('pp.details.system_type'))}</dt><dd>${esc(t('pp.system.' + p.system_type))} · ${esc(t('pp.scope.' + p.scope))}</dd>
                <dt>${esc(t('pp.results.fixtures'))}</dt><dd>${fixtures}</dd>
                <dt>${esc(t('pp.results.room'))}</dt><dd>${esc(roomTxt)}</dd>
                <dt>${esc(t('pp.summary.photos'))}</dt><dd>${p.images.length}</dd>
                <dt>${esc(t('pp.results.status'))}</dt><dd><span class="pp-badge pp-badge-${readinessCls}">${esc(t('pp.readiness.' + a.readiness))}</span></dd>
            </dl>
            ${a.safety_flags.length ? `<div class="pp-alert pp-alert-error"><strong>${esc(t('pp.results.safety'))}</strong><ul>${a.safety_flags.map(f => `<li>${esc(f)}</li>`).join('')}</ul></div>` : ''}
        </section>

        <section class="pp-card" aria-labelledby="ppB">
            <h3 id="ppB">${esc(t('pp.results.layout'))}</h3>
            <p class="pp-muted">${esc(t('pp.results.layout_note'))}</p>
            ${layoutHtml(a)}
        </section>

        <section class="pp-card" aria-labelledby="ppC">
            <h3 id="ppC">${esc(t('pp.results.bom'))}</h3>
            <div class="pp-waste">
                <label class="pp-field pp-narrow"><span>${esc(t('pp.results.waste'))}</span>
                    <input type="number" class="form-control" id="ppWaste" min="0" max="30" step="0.5" inputmode="decimal" value="${a.waste_allowance_percent ?? ''}" placeholder="0"></label>
                <button type="button" class="btn btn-sm btn-outline" id="ppApplyWaste">${esc(t('pp.results.apply_waste'))}</button>
                <small class="pp-muted">${esc(a.waste_allowance_percent ? t('pp.results.waste_applied', { p: a.waste_allowance_percent }) : t('pp.results.waste_none'))}</small>
            </div>
            ${bomHtml(a)}
        </section>

        <section class="pp-card" aria-labelledby="ppE">
            <h3 id="ppE">${esc(t('pp.results.confidence'))}</h3>
            ${observationsHtml(a)}
            ${listBlock(t('pp.results.customer_facts'), customerFacts(p))}
            ${listBlock(t('pp.results.assumptions'), a.assumptions)}
            ${listBlock(t('pp.results.missing'), a.missing_information)}
            ${listBlock(t('pp.results.contradictions'), a.contradictions)}
            ${listBlock(t('pp.results.corrections'), a.corrections)}
        </section>

        ${a.clarification_questions.length ? `
        <section class="pp-card" aria-labelledby="ppQ">
            <h3 id="ppQ">${esc(t('pp.results.questions'))}</h3>
            <p class="pp-muted">${esc(t('pp.results.questions_note'))}</p>
            <form id="ppAnswersForm">${questionsHtml(a.clarification_questions)}
                <div id="ppAnswersAlert"></div>
                <button class="btn btn-primary" type="submit">${esc(t('pp.results.save_answers_rerun'))}</button></form>
        </section>` : ''}

        <section class="pp-card" aria-labelledby="ppF">
            <h3 id="ppF">${esc(t('pp.results.next'))}</h3>
            <div class="pp-actions-grid">
                <button type="button" class="btn btn-outline" data-goto="1"><i class="bi bi-pencil"></i> ${esc(t('pp.next.edit'))}</button>
                <button type="button" class="btn btn-outline" data-goto="2"><i class="bi bi-images"></i> ${esc(t('pp.next.photos'))}</button>
                <button type="button" class="btn btn-outline" data-goto="3"><i class="bi bi-rulers"></i> ${esc(t('pp.next.measure'))}</button>
                <button type="button" class="btn btn-outline" id="ppRerun"><i class="bi bi-arrow-repeat"></i> ${esc(t('pp.next.rerun'))}</button>
                <a class="btn btn-outline" target="_blank" rel="noopener" href="${esc(projectUrl(`/analyses/${a.version}/report`))}"><i class="bi bi-file-earmark-pdf"></i> ${esc(t('pp.next.report'))}</a>
                <a class="btn btn-outline" href="${esc(CFG.contactUrl)}"><i class="bi bi-headset"></i> ${esc(t('pp.next.contact'))}</a>
            </div>
            <p class="pp-muted pp-saved"><i class="bi bi-cloud-check"></i> ${esc(t(CFG.authenticated ? 'pp.next.saved_account' : 'pp.next.saved_guest', { ref: p.reference }))}</p>
        </section>

        <section class="pp-card pp-quote" aria-labelledby="ppG">
            <h3 id="ppG">${esc(t('pp.quote.title'))}</h3>
            ${a.quote_submitted ? `<div class="pp-alert pp-alert-ok">${esc(t('pp.quote.already'))}</div>` : quoteFormHtml(a)}
        </section>`;
        bindResultEvents();
    }

    function listBlock(title, items) {
        if (!items || !items.length) return '';
        return `<h4>${esc(title)}</h4><ul class="pp-list">${items.map(i => `<li>${esc(i)}</li>`).join('')}</ul>`;
    }

    function customerFacts(p) {
        const facts = [];
        const m = p.measurements || {};
        (m.distances || []).forEach(d => facts.push(`${d.from} → ${d.to}: ${d.value === null ? t('pp.measure.unknown') : fmt(d.value) + ' ' + (d.unit || p.unit)}`));
        ['water_source', 'heater_location', 'known_diameters', 'elevations'].forEach(k => { if (m[k]) facts.push(`${t('pp.measure.' + k)}: ${m[k]}`); });
        (p.answers || []).filter(a => a.answer).forEach(a => facts.push(`${a.question} — ${a.answer}`));
        return facts;
    }

    function observationsHtml(a) {
        if (!a.observations.length) return `<p class="pp-muted">${esc(t('pp.results.no_observations'))}</p>`;
        return `<h4>${esc(t('pp.results.observations'))}</h4><div class="pp-table-wrap"><table class="pp-table">
            <thead><tr><th>${esc(t('pp.obs.item'))}</th><th>${esc(t('pp.obs.basis'))}</th><th>${esc(t('pp.obs.confidence'))}</th><th>${esc(t('pp.obs.photo'))}</th><th>${esc(t('pp.obs.notes'))}</th></tr></thead>
            <tbody>${a.observations.map(o => `<tr>
                <td data-label="${esc(t('pp.obs.item'))}">${esc(o.label)} <small class="pp-muted">(${esc(t('pp.obstype.' + o.type))})</small></td>
                <td data-label="${esc(t('pp.obs.basis'))}">${badge(o.basis, o.basis === 'observed' ? 'ok' : 'warn')}</td>
                <td data-label="${esc(t('pp.obs.confidence'))}">${badge('conf_' + o.confidence, { high: 'ok', medium: 'warn', low: 'bad' }[o.confidence])}</td>
                <td data-label="${esc(t('pp.obs.photo'))}">${o.image_index === null ? '—' : '#' + (o.image_index + 1)}</td>
                <td data-label="${esc(t('pp.obs.notes'))}">${esc(o.notes || '')}</td></tr>`).join('')}</tbody></table></div>`;
    }

    function bomHtml(a) {
        if (!a.items.length) return `<p class="pp-muted">${esc(t('pp.results.no_items'))}</p>`;
        const rows = a.items.map(it => {
            const dia = it.diameter_mm ? `${it.diameter_mm}${it.secondary_diameter_mm ? ' × ' + it.secondary_diameter_mm : ''} mm` : t('pp.bom.dia_unknown');
            const qty = it.quantity === null ? `<span class="pp-muted">${esc(t('pp.bom.not_calculated'))}</span>`
                : `${fmt(it.quantity)} ${esc(t('pp.unit.' + it.unit))}${it.unit === 'm' && it.base_quantity !== it.quantity ? `<br><small class="pp-muted">${esc(t('pp.bom.base', { q: fmt(it.base_quantity) }))}</small>` : ''}${it.derivation?.stock_lengths ? `<br><small class="pp-muted">${esc(t('pp.bom.bars', { n: it.derivation.stock_lengths, l: it.derivation.stock_length_m }))}</small>` : ''}`;
            const product = it.product
                ? `<a href="${esc(it.product.url)}" target="_blank" rel="noopener">${esc(it.product.name)}</a>${it.product.price !== null ? `<br><small>${fmt(it.product.price)} ${esc(it.product.currency)} / ${esc(t('pp.unit.catalog'))}</small>` : ''}${it.product.stock_status !== 'in_stock' ? `<br><small class="pp-muted">${esc(t('pp.stock.' + it.product.stock_status))}</small>` : ''}`
                : `<span class="pp-verify">${esc(t('pp.bom.requires_verification'))}</span>`;
            const unknownSegs = it.derivation?.unknown_segments ? `<br><small class="pp-muted">${esc(t('pp.bom.unknown_segments', { n: it.derivation.unknown_segments }))}</small>` : '';
            return `<tr>
                <td data-label="${esc(t('pp.bom.category'))}">${esc(t('pp.cat.' + it.category))}${it.water ? ` <small class="pp-water-${it.water}">(${esc(t('pp.water.' + it.water))})</small>` : ''}</td>
                <td data-label="${esc(t('pp.bom.product'))}">${product}</td>
                <td data-label="${esc(t('pp.bom.sku'))}"><code>${esc(it.product?.sku || '—')}</code></td>
                <td data-label="${esc(t('pp.bom.diameter'))}">${esc(dia)}</td>
                <td data-label="${esc(t('pp.bom.quantity'))}">${qty}${unknownSegs}</td>
                <td data-label="${esc(t('pp.bom.compat'))}">${badge(it.match_status, it.match_status === 'matched' ? 'ok' : 'warn')}</td>
                <td data-label="${esc(t('pp.bom.source'))}">${esc(t('pp.source.' + it.source))}</td>
                <td data-label="${esc(t('pp.bom.verification'))}">${badge(it.quantity_status, QTY_CLASS[it.quantity_status])}</td>
                <td data-label="${esc(t('pp.bom.notes'))}">${esc(it.notes || '')}</td></tr>`;
        }).join('');
        return `<div class="pp-table-wrap"><table class="pp-table pp-bom"><thead><tr>
            <th>${esc(t('pp.bom.category'))}</th><th>${esc(t('pp.bom.product'))}</th><th>${esc(t('pp.bom.sku'))}</th><th>${esc(t('pp.bom.diameter'))}</th>
            <th>${esc(t('pp.bom.quantity'))}</th><th>${esc(t('pp.bom.compat'))}</th><th>${esc(t('pp.bom.source'))}</th><th>${esc(t('pp.bom.verification'))}</th><th>${esc(t('pp.bom.notes'))}</th>
            </tr></thead><tbody>${rows}</tbody></table></div>
            <p class="pp-muted pp-legend">${esc(t('pp.bom.legend'))}</p>`;
    }

    // Conceptual schematic: columns by graph depth from the source, rows by order.
    function layoutHtml(a) {
        const opt = a.routing_options[0];
        if (!opt || !opt.nodes.length || !opt.segments.length) {
            return `<div class="pp-alert pp-alert-info">${esc(t('pp.layout.insufficient'))}</div>`;
        }
        const nodes = opt.nodes;
        const depth = {};
        const roots = nodes.filter(n => n.kind === 'source').map(n => n.id);
        (roots.length ? roots : [nodes[0].id]).forEach(r => { depth[r] = 0; });
        let changed = true, guard = 0;
        while (changed && guard++ < 100) {
            changed = false;
            opt.segments.forEach(s => {
                if (depth[s.from] !== undefined && (depth[s.to] === undefined || depth[s.to] > depth[s.from] + 1)) { depth[s.to] = depth[s.from] + 1; changed = true; }
            });
        }
        const maxDepth = Math.max(0, ...Object.values(depth));
        nodes.forEach(n => { if (depth[n.id] === undefined) depth[n.id] = maxDepth + 1; });
        const cols = {};
        nodes.forEach(n => { (cols[depth[n.id]] = cols[depth[n.id]] || []).push(n); });
        const colCount = Object.keys(cols).length;
        const rowCount = Math.max(...Object.values(cols).map(c => c.length));
        const W = Math.max(640, colCount * 190), H = Math.max(220, rowCount * 90 + 40);
        const pos = {};
        Object.entries(cols).forEach(([d, list]) => {
            list.forEach((n, i) => {
                let x = 90 + Number(d) * ((W - 180) / Math.max(1, colCount - 1));
                if (RTL) x = W - x;
                pos[n.id] = { x, y: (H / (list.length + 1)) * (i + 1) };
            });
        });
        const colour = { cold: '#1f6fb2', hot: '#c0392b' };
        const lines = opt.segments.map(s => {
            const p1 = pos[s.from], p2 = pos[s.to];
            const dash = s.status === 'observed' ? '' : (s.status === 'proposed' ? '8 5' : '3 4');
            const label = [s.nominal_diameter_mm ? `Ø${s.nominal_diameter_mm}` : null, s.length_m !== null && s.length_source !== 'unknown' ? `${s.length_m} m${s.length_source === 'estimated' ? '≈' : ''}` : null].filter(Boolean).join(' · ');
            const mx = (p1.x + p2.x) / 2, my = (p1.y + p2.y) / 2 - (s.water === 'hot' ? 8 : -14);
            return `<line x1="${p1.x}" y1="${p1.y}" x2="${p2.x}" y2="${p2.y}" stroke="${colour[s.water]}" stroke-width="${s.line === 'main' ? 4 : 2.5}" stroke-dasharray="${dash}" marker-end="url(#ppArrow-${s.water})"/>
                ${label ? `<text x="${mx}" y="${my}" class="pp-svg-seg" text-anchor="middle">${esc(label)}</text>` : ''}`;
        }).join('');
        const icons = { source: '#0f3460', heater: '#c0392b', main: '#1a6b3c', branch_point: '#6c757d', fixture: '#ac9273' };
        const dots = nodes.map(n => {
            const p = pos[n.id];
            return `<g><circle cx="${p.x}" cy="${p.y}" r="${n.kind === 'branch_point' ? 7 : 12}" fill="${icons[n.kind]}" stroke="#fff" stroke-width="2" ${n.status !== 'observed' ? 'stroke-dasharray="3 2"' : ''}/>
                <text x="${p.x}" y="${p.y + 28}" text-anchor="middle" class="pp-svg-node">${esc(n.label)}</text>
                <text x="${p.x}" y="${p.y + 42}" text-anchor="middle" class="pp-svg-status">${esc(t('pp.status.' + n.status))}</text></g>`;
        }).join('');
        const options = a.routing_options.length > 1 ? `<p class="pp-muted">${esc(t('pp.layout.other_options', { n: a.routing_options.length - 1 }))}</p><ul class="pp-list">${a.routing_options.slice(1).map(o => `<li><strong>${esc(o.title)}</strong> — ${esc(o.summary || '')}</li>`).join('')}</ul>` : '';
        return `<p><strong>${esc(opt.title)}</strong> — ${esc(opt.summary || '')}</p>
            <div class="pp-svg-wrap"><svg viewBox="0 0 ${W} ${H}" role="img" aria-label="${esc(t('pp.layout.aria'))}" class="pp-svg">
                <defs>${['cold', 'hot'].map(w => `<marker id="ppArrow-${w}" viewBox="0 0 10 10" refX="22" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M0,0 L10,5 L0,10 z" fill="${colour[w]}"/></marker>`).join('')}</defs>
                ${lines}${dots}</svg></div>
            <ul class="pp-svg-legend">
                <li><span style="background:${colour.cold}"></span>${esc(t('pp.water.cold'))}</li>
                <li><span style="background:${colour.hot}"></span>${esc(t('pp.water.hot'))}</li>
                <li><span class="pp-solid"></span>${esc(t('pp.status.observed'))}</li>
                <li><span class="pp-dashed"></span>${esc(t('pp.status.proposed'))}</li>
                <li><span class="pp-dotted"></span>${esc(t('pp.status.unverified'))}</li>
                <li>≈ ${esc(t('pp.layout.estimated'))}</li>
            </ul>${options}`;
    }

    function quoteFormHtml(a) {
        const c = CFG.customer || {};
        const rows = a.items.map(it => `<li class="pp-quote-item">
            <label class="pp-check"><input type="checkbox" data-qitem="${it.id}" checked>
                ${esc(t('pp.cat.' + it.category))} ${it.diameter_mm ? esc(it.diameter_mm + (it.secondary_diameter_mm ? '×' + it.secondary_diameter_mm : '') + ' mm') : ''} — ${esc(it.product?.name || t('pp.bom.requires_verification'))}</label>
            <label class="pp-field pp-narrow"><span class="pp-visually-hidden">${esc(t('pp.bom.quantity'))}</span>
                <input type="number" class="form-control" min="0" step="any" data-qqty="${it.id}" value="${it.quantity ?? ''}" placeholder="${esc(t('pp.quote.qty_ph'))}"></label>
            <span class="pp-muted">${esc(t('pp.unit.' + it.unit))}</span></li>`).join('');
        return `<p class="pp-muted">${esc(t('pp.quote.intro'))}</p>
        <form id="ppQuoteForm" novalidate>
            <ul class="pp-quote-items">${rows}</ul>
            <div class="pp-grid-3">
                <label class="pp-field"><span>${esc(t('pp.quote.name'))} *</span><input class="form-control" name="name" required maxlength="150" value="${esc(c.name || '')}" autocomplete="name"></label>
                <label class="pp-field"><span>${esc(t('pp.quote.email'))} *</span><input class="form-control" name="email" type="email" required maxlength="150" value="${esc(c.email || '')}" autocomplete="email"></label>
                <label class="pp-field"><span>${esc(t('pp.quote.phone'))}</span><input class="form-control" name="phone" type="tel" maxlength="30" value="${esc(c.phone || '')}" autocomplete="tel"></label>
            </div>
            <label class="pp-field"><span>${esc(t('pp.quote.message'))}</span><textarea class="form-control" name="message" rows="3" maxlength="2000"></textarea></label>
            <label class="pp-check pp-field" data-field="approve_assumptions"><input type="checkbox" name="approve_assumptions" value="1"> ${esc(t('pp.quote.approve_assumptions'))}</label>
            <label class="pp-check pp-field" data-field="confirm"><input type="checkbox" name="confirm" value="1"> ${esc(t('pp.quote.confirm'))}</label>
            <div id="ppQuoteAlert"></div>
            <button class="btn btn-primary btn-lg" type="submit"><i class="bi bi-send"></i> ${esc(t('pp.quote.submit'))}</button>
        </form>`;
    }

    function bindResultEvents() {
        $$('#ppResults [data-goto]').forEach(b => b.addEventListener('click', () => goTo(Number(b.dataset.goto))));
        $('#ppRerun')?.addEventListener('click', () => { goTo(4); renderAnalysisIntro(); startAnalysis(); });

        $('#ppApplyWaste')?.addEventListener('click', async (e) => {
            const v = num($('#ppWaste').value);
            if (v !== null && (v < 0 || v > 30)) { alert(t('pp.results.waste_range')); return; }
            setBusy(e.target, true);
            try {
                const res = await api('PATCH', projectUrl(`/analyses/${state.analysis.version}/waste`), { waste_allowance_percent: v });
                state.analysis = res.data;
                state.project.waste_allowance_percent = v;
                renderResults();
            } catch (err) { alert(err.message); } finally { setBusy(e.target, false); }
        });

        $('#ppAnswersForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.submitter;
            setBusy(btn, true);
            try {
                const res = await api('PATCH', projectUrl(), { answers: collectAnswers() });
                setProject(res.data);
                goTo(4);
                renderAnalysisIntro();
                startAnalysis();
            } catch (err) { showAlert($('#ppAnswersAlert'), err.message); } finally { setBusy(btn, false); }
        });

        $('#ppQuoteForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const btn = e.submitter;
            clearFieldErrors(form);
            showAlert($('#ppQuoteAlert'), '');
            const items = $$('[data-qitem]', form).filter(c => c.checked).map(c => ({ id: Number(c.dataset.qitem), quantity: num($(`[data-qqty="${c.dataset.qitem}"]`, form).value) }));
            const errors = {};
            if (!form.name.value.trim()) errors.name = [t('pp.validation.required')];
            if (!/^\S+@\S+\.\S+$/.test(form.email.value.trim())) errors.email = [t('pp.validation.email')];
            if (!form.approve_assumptions.checked) errors.approve_assumptions = [t('pp.validation.accepted')];
            if (!form.confirm.checked) errors.confirm = [t('pp.validation.accepted')];
            if (!items.length) { showAlert($('#ppQuoteAlert'), t('pp.quote.no_items')); return; }
            if (Object.keys(errors).length) { showFieldErrors(form, errors); return; }
            setBusy(btn, true);
            try {
                const res = await api('POST', projectUrl(`/analyses/${state.analysis.version}/quote`), {
                    name: form.name.value.trim(), email: form.email.value.trim(), phone: form.phone.value.trim() || null,
                    message: form.message.value.trim() || null, items, approve_assumptions: true, confirm: true,
                });
                state.analysis.quote_submitted = true;
                $('.pp-quote').innerHTML = `<h3>${esc(t('pp.quote.title'))}</h3><div class="pp-alert pp-alert-ok" role="status">${esc(t('pp.quote.sent', { ref: res.data.reference }))}</div>`;
            } catch (err) {
                if (err.errors) showFieldErrors(form, err.errors);
                showAlert($('#ppQuoteAlert'), err.message);
            } finally { setBusy(btn, false); }
        });
    }

    // ------------------------------------------------------------------
    // Saved projects + resume
    // ------------------------------------------------------------------
    async function loadProject(reference) {
        try {
            const res = await api('GET', `${CFG.base}/projects/${encodeURIComponent(reference)}`);
            setProject(res.data);
            fillDetails(res.data);
            fillMeasurements(res.data);
            if (res.data.latest_analysis_version) {
                const a = await api('GET', projectUrl(`/analyses/${res.data.latest_analysis_version}`));
                state.analysis = a.data;
                if (a.data.status === 'completed') { renderResults(); goTo(5); return; }
                if (a.data.status === 'queued' || a.data.status === 'running') { goTo(4); renderAnalysisIntro(); handleAnalysisState(); return; }
            }
            goTo(res.data.images.length ? 3 : 2);
        } catch (err) {
            try { localStorage.removeItem(STORE_KEY); } catch (e) { /* ignore */ }
            goTo(1);
        }
    }

    async function loadSavedProjects() {
        try {
            const res = await api('GET', `${CFG.base}/projects`);
            const box = $('#ppSaved');
            if (!res.data.length) { box.hidden = true; return; }
            box.hidden = false;
            $('#ppSavedList').innerHTML = res.data.map(p => `<li><button type="button" class="pp-link" data-open="${esc(p.reference)}">
                <code>${esc(p.reference)}</code> · ${esc(t('pp.type.' + p.project_type))} · ${esc(t('pp.astatus.' + p.analysis_status))}
                <small class="pp-muted">${esc(new Date(p.updated_at).toLocaleDateString(RTL ? 'ar-SA' : 'en-GB'))}</small></button></li>`).join('');
        } catch (e) { /* non-critical */ }
    }

    function newProject() {
        stopPolling();
        state.project = null;
        state.analysis = null;
        try { localStorage.removeItem(STORE_KEY); } catch (e) { /* ignore */ }
        const url = new URL(location.href);
        url.searchParams.delete('project');
        history.replaceState(null, '', url);
        $('#ppDetailsForm').reset();
        $('#ppMeasurementsForm').reset();
        $('#ppDistances').innerHTML = '';
        $$('.pp-reference').forEach(el => { el.textContent = ''; });
        $('#ppRefWrap').hidden = true;
        refreshRelevantFields();
        goTo(1);
    }

    // ------------------------------------------------------------------
    // Init
    // ------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        if (!$('#ppWizard')) return;
        $('#ppDetailsForm').addEventListener('submit', saveDetails);
        $('#ppDetailsForm').addEventListener('change', refreshRelevantFields);
        bindPhotoEvents();
        bindMeasurementEvents();
        $('#ppStartAnalysis').addEventListener('click', startAnalysis);
        $$('[data-back]').forEach(b => b.addEventListener('click', () => goTo(Number(b.dataset.back))));
        $('#ppPhotosNext').addEventListener('click', () => goTo(3));
        $('#ppNewProject').addEventListener('click', newProject);
        $('#ppSavedList').addEventListener('click', (e) => {
            const b = e.target.closest('[data-open]');
            if (b) loadProject(b.dataset.open);
        });
        $$('.pp-progress button').forEach(b => b.addEventListener('click', () => {
            const n = Number(b.closest('li').dataset.step);
            if (n === 5 && state.analysis?.status !== 'completed') return;
            goTo(n);
            if (n === 4) renderAnalysisIntro();
        }));
        refreshRelevantFields();

        let ref = CFG.initialReference;
        if (!ref) { try { ref = localStorage.getItem(STORE_KEY); } catch (e) { /* ignore */ } }
        if (ref) loadProject(ref); else goTo(1, false);
        loadSavedProjects();
    });
})();
