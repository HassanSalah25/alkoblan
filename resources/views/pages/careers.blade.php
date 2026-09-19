@extends('layouts.app')

@section('content')
@include('partials.page-hero', ['title' => 'الوظائف الشاغرة', 'crumbs' => ['الوظائف' => null]])

<section class="section">
    <div class="container">
        @forelse($jobs as $job)
            <div class="branch-card reveal" style="background:#fff;padding:25px;border-radius:10px;margin-bottom:18px;box-shadow:0 4px 15px rgba(0,0,0,0.05);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;">
                <div>
                    <h3 style="margin-bottom:8px;">{{ trans_field($job, 'title') }}</h3>
                    <div style="color:var(--text-light);font-size:0.9rem;display:flex;gap:15px;flex-wrap:wrap;">
                        <span><i class="bi bi-building"></i> {{ $job->department }}</span>
                        <span><i class="bi bi-geo-alt-fill"></i> {{ $job->location }}</span>
                        <span><i class="bi bi-clock"></i> {{ $job->employment_type }}</span>
                    </div>
                </div>
                <a href="{{ route('careers.show', $job->slug) }}" class="btn btn-primary">{{ __('View Details') }}</a>
            </div>
        @empty
            <p style="text-align:center;">لا توجد وظائف شاغرة حالياً.</p>
        @endforelse
    </div>
</section>
@endsection
