@props(['field', 'value' => null, 'url' => null, 'label' => 'Image', 'type' => null, 'title' => null])

<div class="mb-3">
    <label class="form-label">{{ $label }}</label>
    <div class="d-flex align-items-center gap-3">
        <img id="preview-{{ $field }}" src="{{ $url }}" class="media-pick-thumb {{ $url ? '' : 'd-none' }}" alt="">
        <div>
            <input type="hidden" name="{{ $field }}" id="input-{{ $field }}" value="{{ $value }}">
            <div class="small text-muted mb-1" id="label-{{ $field }}">{{ $title ?: ($url ? 'Current file' : 'No file selected') }}</div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openMediaPicker('{{ $field }}', {{ $type ? "'".$type."'" : 'null' }})">
                <i class="bi bi-images"></i> Browse / Upload
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearMediaPicker('{{ $field }}')">
                <i class="bi bi-x"></i> Remove
            </button>
        </div>
    </div>
</div>
