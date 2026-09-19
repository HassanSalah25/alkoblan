@extends('admin.layouts.app')

@section('title', 'Social Media Settings')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings._tabs')

<form method="POST" action="{{ route('admin.settings.social.update') }}">
    @csrf
    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Social Media Links</div>
        <div class="card-body row">
            @foreach ([
                'social_facebook' => 'Facebook',
                'social_twitter' => 'Twitter / X',
                'social_instagram' => 'Instagram',
                'social_linkedin' => 'LinkedIn',
                'social_youtube' => 'YouTube',
            ] as $key => $label)
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ $label }}</label>
                    <input type="text" name="{{ $key }}" class="form-control" value="{{ old($key, $values[$key]) }}">
                </div>
            @endforeach
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Settings</button>
</form>
@endsection
