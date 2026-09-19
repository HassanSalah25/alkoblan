@extends('layouts.app')

@php
    $seoTitle = app()->getLocale() === 'ar' ? ($page->seo_title_ar ?: $page->title_ar) : ($page->seo_title ?: $page->title);
    $seoDescription = app()->getLocale() === 'ar' ? $page->seo_description_ar : $page->seo_description;
@endphp

@section('content')
@include('partials.page-hero', [
    'title' => trans_field($page, 'title'),
    'crumbs' => [trans_field($page, 'title') => null],
    'image' => $page->featuredImage?->url,
])

<section class="section">
    <div class="container" style="max-width:1040px;">
        <div class="page-content">
            {!! trans_field($page, 'content') !!}
        </div>
    </div>
</section>
@endsection
