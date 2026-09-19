@extends('layouts.app')

@section('content')
@include('partials.page-hero', ['title' => trans_field($job, 'title'), 'crumbs' => ['الوظائف' => route('careers.index'), trans_field($job, 'title') => null]])

<section class="section">
    <div class="container" style="max-width:800px;">
        @if(session('success'))
            <div class="alert" style="background:#d4edda;color:#155724;padding:15px;border-radius:8px;margin-bottom:25px;">{{ session('success') }}</div>
        @endif

        <div style="background:#fff;padding:30px;border-radius:12px;margin-bottom:25px;">
            <div style="display:flex;gap:20px;color:var(--text-light);margin-bottom:20px;flex-wrap:wrap;">
                <span><i class="bi bi-building"></i> {{ $job->department }}</span>
                <span><i class="bi bi-geo-alt-fill"></i> {{ $job->location }}</span>
                <span><i class="bi bi-clock"></i> {{ $job->employment_type }}</span>
                @if($job->deadline)<span><i class="bi bi-calendar3"></i> {{ __('Deadline') }}: {{ $job->deadline->translatedFormat('d M Y') }}</span>@endif
            </div>
            <h3>{{ __('Job Description') }}</h3>
            <p style="line-height:1.8;">{{ trans_field($job, 'description') }}</p>
            @if($job->requirements)<h3>{{ __('Requirements') }}</h3><p style="line-height:1.8;">{{ trans_field($job, 'requirements') }}</p>@endif
            @if($job->benefits)<h3>{{ __('Benefits') }}</h3><p style="line-height:1.8;">{{ trans_field($job, 'benefits') }}</p>@endif
        </div>

        <div class="form-card">
            <h3 style="margin-bottom:20px;">{{ __('Apply for this Job') }}</h3>
            <form method="POST" action="{{ route('careers.apply', $job->slug) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">{{ __('Full Name') }} *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                    <div class="form-group">
                        <label class="form-label">{{ __('Email') }} *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Phone') }}</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('CV / Resume (PDF or Word)') }} *</label>
                    <input type="file" name="cv" accept=".pdf,.doc,.docx" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Cover Letter') }}</label>
                    <textarea name="cover_letter" rows="4" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100" style="justify-content:center">{{ __('Submit Application') }}</button>
            </form>
        </div>
    </div>
</section>
@endsection
