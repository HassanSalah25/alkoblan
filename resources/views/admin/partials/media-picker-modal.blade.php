{{-- Single reusable Media Picker modal + upload tab, used by every module via the
     <x-admin.media-picker /> component and the openMediaPicker() JS helper below. --}}
<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-images me-1"></i> Select Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#mp-browse-tab" type="button">Browse Library</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#mp-upload-tab" type="button">Upload New</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="mp-browse-tab">
                        <input type="text" id="mpSearch" class="form-control form-control-sm mb-2" placeholder="Search by title or filename...">
                        <div id="mpGrid" class="row g-2" style="max-height: 50vh; overflow-y:auto;"></div>
                        <div id="mpEmpty" class="text-muted small text-center py-4 d-none">No media found.</div>
                    </div>
                    <div class="tab-pane fade" id="mp-upload-tab">
                        <form id="mpUploadForm">
                            <div class="mb-2">
                                <label class="form-label small">File</label>
                                <input type="file" name="file" id="mpUploadFile" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Title (optional)</label>
                                <input type="text" name="title" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-upload"></i> Upload &amp; Use
                            </button>
                            <div id="mpUploadStatus" class="small text-muted mt-2"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let mpCurrentField = null;
    let mpCurrentType = null;
    let mpAllItems = [];

    function mpIconFor(type) {
        switch (type) {
            case 'pdf': return 'bi-file-earmark-pdf';
            case 'document': return 'bi-file-earmark-text';
            case 'video': return 'bi-file-earmark-play';
            default: return 'bi-file-earmark';
        }
    }

    function mpRenderGrid(items) {
        const grid = document.getElementById('mpGrid');
        const empty = document.getElementById('mpEmpty');
        grid.innerHTML = '';
        if (!items.length) {
            empty.classList.remove('d-none');
            return;
        }
        empty.classList.add('d-none');
        items.forEach(function (item) {
            const col = document.createElement('div');
            col.className = 'col-4 col-md-3';
            const inner = document.createElement('div');
            inner.className = 'picker-grid-item text-center';
            inner.onclick = function () { mpSelect(item); };
            if (item.type === 'image') {
                inner.innerHTML = '<img src="' + item.url + '" alt="">';
            } else {
                inner.innerHTML = '<div class="file-icon"><i class="bi ' + mpIconFor(item.type) + '"></i></div>';
            }
            inner.innerHTML += '<div class="small text-truncate mt-1">' + (item.title || item.original_name) + '</div>';
            col.appendChild(inner);
            grid.appendChild(col);
        });
    }

    function mpApplyFilter() {
        const q = (document.getElementById('mpSearch').value || '').toLowerCase();
        let items = mpAllItems;
        if (mpCurrentType) {
            items = items.filter(i => i.type === mpCurrentType);
        }
        if (q) {
            items = items.filter(i => (i.title || '').toLowerCase().includes(q) || (i.original_name || '').toLowerCase().includes(q));
        }
        mpRenderGrid(items);
    }

    function mpSelect(item) {
        if (!mpCurrentField) return;
        const input = document.getElementById('input-' + mpCurrentField);
        const preview = document.getElementById('preview-' + mpCurrentField);
        const label = document.getElementById('label-' + mpCurrentField);
        if (input) input.value = item.id;
        if (preview) {
            if (item.type === 'image') {
                preview.src = item.url;
                preview.classList.remove('d-none');
            } else {
                preview.classList.add('d-none');
            }
        }
        if (label) label.textContent = item.title || item.original_name;
        const modalEl = document.getElementById('mediaPickerModal');
        bootstrap.Modal.getInstance(modalEl)?.hide();
    }

    function clearMediaPicker(field) {
        const input = document.getElementById('input-' + field);
        const preview = document.getElementById('preview-' + field);
        const label = document.getElementById('label-' + field);
        if (input) input.value = '';
        if (preview) { preview.src = ''; preview.classList.add('d-none'); }
        if (label) label.textContent = 'No file selected';
    }

    function openMediaPicker(field, type) {
        mpCurrentField = field;
        mpCurrentType = type || null;
        document.getElementById('mpSearch').value = '';
        fetch('{{ route('admin.media.picker-data') }}')
            .then(r => r.json())
            .then(data => {
                mpAllItems = data.items || [];
                mpApplyFilter();
            });
        const modalEl = document.getElementById('mediaPickerModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const search = document.getElementById('mpSearch');
        if (search) search.addEventListener('input', mpApplyFilter);

        const uploadForm = document.getElementById('mpUploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const status = document.getElementById('mpUploadStatus');
                status.textContent = 'Uploading...';
                const fd = new FormData(uploadForm);
                fetch('{{ route('admin.media.quick-upload') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': window.AK_CSRF, 'Accept': 'application/json' },
                    body: fd,
                }).then(r => r.json()).then(data => {
                    if (data.item) {
                        status.textContent = 'Uploaded.';
                        mpSelect(data.item);
                        uploadForm.reset();
                    } else {
                        status.textContent = data.message || 'Upload failed.';
                    }
                }).catch(() => { status.textContent = 'Upload failed.'; });
            });
        }
    });
</script>
