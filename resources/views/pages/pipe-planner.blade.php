@extends('layouts.app', ['seoTitle' => __('pp.meta.title'), 'seoDescription' => __('pp.meta.description')])

@section('styles')
<link rel="stylesheet" href="{{ asset('css/pipe-planner.css') }}?v={{ filemtime(public_path('css/pipe-planner.css')) }}">
@endsection

@section('content')
@include('partials.page-hero', ['title' => __('pp.title'), 'crumbs' => [__('pp.title') => null]])

<section class="section pp-section">
    <div class="container" id="ppWizard">

        <div class="pp-disclaimer" role="note">
            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
            <div><strong>{{ __('pp.disclaimer.title') }}</strong> {{ __('pp.disclaimer.body') }}</div>
        </div>

        @unless ($available)
            <div class="pp-alert pp-alert-info" role="status">{{ __('pp.unavailable') }} <a href="{{ route('contact') }}">{{ __('pp.next.contact') }}</a></div>
        @endunless

        <div class="pp-topbar">
            <p class="pp-intro">{{ __('pp.intro') }}</p>
            <div class="pp-topbar-actions">
                <span id="ppRefWrap" hidden class="pp-muted">{{ __('pp.results.reference') }}: <code class="pp-reference"></code></span>
                <button type="button" class="btn btn-sm btn-outline" id="ppNewProject"><i class="bi bi-plus-lg"></i> {{ __('pp.new_project') }}</button>
            </div>
        </div>

        <details class="pp-saved-box" id="ppSaved" hidden>
            <summary>{{ __('pp.saved_projects') }}</summary>
            <ul id="ppSavedList" class="pp-list"></ul>
        </details>

        <ol class="pp-progress" aria-label="{{ __('pp.progress') }}">
            @foreach ([1 => 'pp.step.details', 2 => 'pp.step.photos', 3 => 'pp.step.measurements', 4 => 'pp.step.analysis', 5 => 'pp.step.results'] as $n => $key)
                <li data-step="{{ $n }}"><button type="button"><span class="pp-step-num">{{ $n }}</span><span class="pp-step-label">{{ __($key) }}</span></button></li>
            @endforeach
        </ol>

        {{-- STEP 1: PROJECT DETAILS --}}
        <div class="pp-step pp-card" data-step="1">
            <h2>{{ __('pp.step.details') }}</h2>
            <form id="ppDetailsForm" novalidate>
                <fieldset class="pp-field" data-field="project_type">
                    <legend>{{ __('pp.details.project_type') }} *</legend>
                    <div class="pp-choices">
                        @foreach (['apartment' => 'bi-building', 'villa' => 'bi-house-door', 'bathroom' => 'bi-droplet', 'kitchen' => 'bi-cup-hot', 'commercial' => 'bi-shop', 'other' => 'bi-three-dots'] as $value => $icon)
                            <label class="pp-choice"><input type="radio" name="project_type" value="{{ $value }}"><span><i class="bi {{ $icon }}" aria-hidden="true"></i> {{ __('pp.type.'.$value) }}</span></label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="pp-field" data-field="project_status">
                    <legend>{{ __('pp.details.project_status') }} *</legend>
                    <div class="pp-choices">
                        @foreach (['new_construction', 'renovation', 'existing_installation'] as $value)
                            <label class="pp-choice"><input type="radio" name="project_status" value="{{ $value }}"><span>{{ __('pp.pstatus.'.$value) }}</span></label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="pp-field" data-field="system_type">
                    <legend>{{ __('pp.details.system_type') }} *</legend>
                    <div class="pp-choices">
                        @foreach (['cold', 'hot', 'combined', 'other'] as $value)
                            <label class="pp-choice"><input type="radio" name="system_type" value="{{ $value }}"><span>{{ __('pp.system.'.$value) }}</span></label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="pp-field">
                    <legend>{{ __('pp.details.scope') }}</legend>
                    <div class="pp-choices">
                        @foreach (['complete', 'partial'] as $value)
                            <label class="pp-choice"><input type="radio" name="scope" value="{{ $value }}" @checked($value === 'complete')><span>{{ __('pp.scope.'.$value) }}</span></label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="pp-field">
                    <legend>{{ __('pp.details.fixtures') }}</legend>
                    <div class="pp-grid-3">
                        @foreach (['bathrooms', 'kitchens', 'water_heaters', 'washing_machines', 'outdoor_taps'] as $f)
                            <label class="pp-field" data-fixture="{{ $f }}"><span>{{ __('pp.fixture.'.$f) }}</span>
                                <input type="number" class="form-control" name="fixtures[{{ $f }}]" min="0" max="200" step="1" inputmode="numeric"></label>
                        @endforeach
                        <label class="pp-field"><span>{{ __('pp.fixture.other') }}</span>
                            <input type="text" class="form-control" name="fixtures[other]" maxlength="300" placeholder="{{ __('pp.fixture.other_ph') }}"></label>
                    </div>
                </fieldset>

                <div class="pp-grid-2">
                    <label class="pp-field"><span>{{ __('pp.details.location') }}</span>
                        <input type="text" class="form-control" name="location" maxlength="150" placeholder="{{ __('pp.details.location_ph') }}"></label>
                    <label class="pp-field"><span>{{ __('pp.details.notes') }}</span>
                        <textarea class="form-control" name="notes" rows="2" maxlength="2000" placeholder="{{ __('pp.details.notes_ph') }}"></textarea></label>
                </div>

                <div id="ppDetailsAlert"></div>
                <div class="pp-nav"><span></span><button class="btn btn-primary" type="submit">{{ __('pp.save_continue') }} <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></button></div>
            </form>
        </div>

        {{-- STEP 2: PHOTOS --}}
        <div class="pp-step pp-card" data-step="2" hidden>
            <h2>{{ __('pp.step.photos') }}</h2>
            <div class="pp-grid-2 pp-guidance">
                <div>
                    <h4>{{ __('pp.photos.what_title') }}</h4>
                    <ul class="pp-list">
                        @foreach (['walls', 'supply', 'fixtures', 'pipes', 'access', 'scale'] as $g)
                            <li>{{ __('pp.photos.tip_'.$g) }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="pp-alert pp-alert-info">
                    <p><strong>{{ __('pp.photos.scale_title') }}</strong> {{ __('pp.photos.scale_body') }}</p>
                    <p><strong>{{ __('pp.photos.privacy_title') }}</strong> {{ __('pp.photos.privacy_body') }}</p>
                </div>
            </div>

            <div id="ppDropzone" class="pp-dropzone">
                <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
                <p>{{ __('pp.photos.drop') }}</p>
                <div class="pp-drop-actions">
                    <label class="btn btn-primary pp-file-btn">{{ __('pp.photos.choose') }}
                        <input type="file" id="ppFileInput" class="pp-visually-hidden" multiple accept="{{ implode(',', $limits['mimes']) }}"></label>
                    <label class="btn btn-outline pp-file-btn pp-camera">{{ __('pp.photos.camera') }}
                        <input type="file" id="ppCameraInput" class="pp-visually-hidden" accept="image/*" capture="environment"></label>
                </div>
                <small class="pp-muted">{{ __('pp.photos.limits', ['max' => $limits['max_images'], 'size' => round($limits['max_image_kb'] / 1024)]) }}</small>
            </div>

            <div id="ppPhotosAlert" aria-live="polite"></div>
            <p class="pp-muted" id="ppPhotoCount" aria-live="polite"></p>
            <p class="pp-muted" id="ppPhotosEmpty">{{ __('pp.photos.empty') }}</p>
            <ul class="pp-photos" id="ppPhotoList"></ul>

            <div class="pp-nav">
                <button type="button" class="btn btn-outline" data-back="1">{{ __('pp.back') }}</button>
                <button type="button" class="btn btn-primary" id="ppPhotosNext">{{ __('pp.continue') }}</button>
            </div>
        </div>

        {{-- STEP 3: MEASUREMENTS --}}
        <div class="pp-step pp-card" data-step="3" hidden>
            <h2>{{ __('pp.step.measurements') }}</h2>
            <p class="pp-muted">{{ __('pp.measure.intro') }}</p>
            <form id="ppMeasurementsForm" novalidate>
                <label class="pp-field pp-narrow"><span>{{ __('pp.measure.unit') }}</span>
                    <select class="form-control" name="unit">
                        @foreach (['mm', 'cm', 'm', 'in', 'ft'] as $u)
                            <option value="{{ $u }}" @selected($u === 'm')>{{ __('pp.unit.'.$u) }}</option>
                        @endforeach
                    </select></label>

                <fieldset class="pp-field">
                    <legend>{{ __('pp.measure.room') }}</legend>
                    <div class="pp-grid-3">
                        @foreach (['length', 'width', 'height'] as $k)
                            <div class="pp-field" data-field="measurements.room.{{ $k }}">
                                <label for="ppRoom{{ $k }}">{{ __('pp.measure.room_'.$k) }}</label>
                                <input id="ppRoom{{ $k }}" type="number" class="form-control" name="room_{{ $k }}" min="0" step="any" inputmode="decimal">
                                <label class="pp-check"><input type="checkbox" data-unknown="room_{{ $k }}"> {{ __('pp.measure.dont_know') }}</label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="pp-field">
                    <legend>{{ __('pp.measure.distances') }}</legend>
                    <p class="pp-muted">{{ __('pp.measure.distances_help') }}</p>
                    <ul id="ppDistances" class="pp-distances"></ul>
                    <button type="button" class="btn btn-sm btn-outline" id="ppAddDistance"><i class="bi bi-plus"></i> {{ __('pp.measure.add_distance') }}</button>
                </fieldset>

                <div class="pp-grid-2">
                    @foreach (['water_source', 'heater_location', 'known_diameters', 'elevations'] as $k)
                        <label class="pp-field" data-field="measurements.{{ $k }}"><span>{{ __('pp.measure.'.$k) }}</span>
                            <input type="text" class="form-control" name="{{ $k }}" maxlength="300" placeholder="{{ __('pp.measure.'.$k.'_ph') }}"></label>
                    @endforeach
                </div>
                <label class="pp-field"><span>{{ __('pp.measure.details') }}</span>
                    <textarea class="form-control" name="details" rows="2" maxlength="1000"></textarea></label>

                <div id="ppOpenQuestions" class="pp-card pp-inset" hidden>
                    <h4>{{ __('pp.results.questions') }}</h4>
                    <div id="ppOpenQuestionsList"></div>
                </div>

                <div id="ppMeasureAlert"></div>
                <div class="pp-nav">
                    <button type="button" class="btn btn-outline" data-back="2">{{ __('pp.back') }}</button>
                    <button class="btn btn-primary" type="submit">{{ __('pp.save_continue') }}</button>
                </div>
            </form>
        </div>

        {{-- STEP 4: ANALYSIS --}}
        <div class="pp-step pp-card" data-step="4" hidden>
            <h2>{{ __('pp.step.analysis') }}</h2>
            <p>{{ __('pp.analyze.intro') }}</p>
            <ul class="pp-list" id="ppAnalyzeSummary"></ul>
            <p class="pp-alert pp-alert-info" id="ppAnalyzeNoPhotos" hidden>{{ __('pp.analyze.need_photo') }}</p>
            <div id="ppStagesWrap" hidden>
                <ol class="pp-stages" id="ppStages" aria-live="polite"></ol>
                <p class="pp-muted">{{ __('pp.analyze.wait') }}</p>
            </div>
            <div id="ppAnalyzeAlert" aria-live="assertive"></div>
            <div class="pp-nav">
                <button type="button" class="btn btn-outline" data-back="3">{{ __('pp.back') }}</button>
                <button type="button" class="btn btn-primary" id="ppStartAnalysis"><i class="bi bi-cpu"></i> {{ __('pp.analyze.start') }}</button>
            </div>
        </div>

        {{-- STEP 5: RESULTS --}}
        <div class="pp-step" data-step="5" hidden>
            <h2 class="pp-results-title">{{ __('pp.step.results') }}</h2>
            <div class="pp-disclaimer pp-disclaimer-sm" role="note"><i class="bi bi-info-circle" aria-hidden="true"></i><div>{{ __('pp.disclaimer.body') }}</div></div>
            <div id="ppResults" aria-live="polite"></div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
window.PIPE_PLANNER = @json($plannerConfig);
</script>
<script src="{{ asset('js/pipe-planner.js') }}?v={{ filemtime(public_path('js/pipe-planner.js')) }}"></script>
@endsection
