@extends('admin.layouts.app')

@section('title', 'General Settings')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings._tabs')

<form method="POST" action="{{ route('admin.settings.general.update') }}">
    @csrf

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Site Identity</div>
        <div class="card-body row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="form-control" value="{{ old('site_name', $values['site_name']) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Site Name (Arabic)</label>
                <input type="text" name="site_name_ar" class="form-control" value="{{ old('site_name_ar', $values['site_name_ar']) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Logo URL</label>
                <input type="text" name="logo" class="form-control" value="{{ old('logo', $values['logo']) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Favicon URL</label>
                <input type="text" name="favicon" class="form-control" value="{{ old('favicon', $values['favicon']) }}">
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Default Language</label>
                <select name="default_language" class="form-select">
                    <option value="ar" {{ $values['default_language'] == 'ar' ? 'selected' : '' }}>Arabic</option>
                    <option value="en" {{ $values['default_language'] == 'en' ? 'selected' : '' }}>English</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Default Currency</label>
                <input type="text" name="default_currency" class="form-control" value="{{ old('default_currency', $values['default_currency']) }}">
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Founded Year</label>
                <input type="text" name="founded_year" class="form-control" value="{{ old('founded_year', $values['founded_year']) }}">
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Contact Information</div>
        <div class="card-body row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Contact Email</label>
                <input type="text" name="contact_email" class="form-control" value="{{ old('contact_email', $values['contact_email']) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Contact Phone</label>
                <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $values['contact_phone']) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Secondary Phone</label>
                <input type="text" name="contact_phone_secondary" class="form-control" value="{{ old('contact_phone_secondary', $values['contact_phone_secondary']) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">WhatsApp Number</label>
                <input type="text" name="contact_whatsapp" class="form-control" value="{{ old('contact_whatsapp', $values['contact_whatsapp']) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Working Hours</label>
                <input type="text" name="working_hours" class="form-control" value="{{ old('working_hours', $values['working_hours']) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Working Hours (Arabic)</label>
                <input type="text" name="working_hours_ar" class="form-control" value="{{ old('working_hours_ar', $values['working_hours_ar']) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Address</label>
                <textarea name="contact_address" class="form-control" rows="2">{{ old('contact_address', $values['contact_address']) }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Address (Arabic)</label>
                <textarea name="contact_address_ar" class="form-control" rows="2">{{ old('contact_address_ar', $values['contact_address_ar']) }}</textarea>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label">Google Maps Embed URL</label>
                <input type="text" name="google_maps_embed" class="form-control" value="{{ old('google_maps_embed', $values['google_maps_embed']) }}" placeholder="Leave empty to use the main branch coordinates">
                <small class="text-muted">Paste the full iframe <code>src</code> from Google Maps (Share → Embed). Truncated <code>?pb=</code> links are ignored and the map falls back to branch GPS.</small>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Footer</div>
        <div class="card-body row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Footer Description</label>
                <textarea name="footer_description" class="form-control" rows="2">{{ old('footer_description', $values['footer_description']) }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Footer Description (Arabic)</label>
                <textarea name="footer_description_ar" class="form-control" rows="2">{{ old('footer_description_ar', $values['footer_description_ar']) }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Copyright Text</label>
                <input type="text" name="copyright_text" class="form-control" value="{{ old('copyright_text', $values['copyright_text']) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Copyright Text (Arabic)</label>
                <input type="text" name="copyright_text_ar" class="form-control" value="{{ old('copyright_text_ar', $values['copyright_text_ar']) }}">
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Commerce</div>
        <div class="card-body row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Tax Rate (%)</label>
                <input type="number" step="0.01" name="tax_rate" class="form-control" value="{{ old('tax_rate', $values['tax_rate']) }}">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Free Shipping Threshold</label>
                <input type="number" step="0.01" name="free_shipping_threshold" class="form-control" value="{{ old('free_shipping_threshold', $values['free_shipping_threshold']) }}">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Settings</button>
</form>
@endsection
