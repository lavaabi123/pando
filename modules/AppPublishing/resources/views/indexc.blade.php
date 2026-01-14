@extends('layouts.app')
@include('apppublishing::header_center', [])

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ Module::asset('AppPublishing:resources/assets/css/publishing.css'); }}">
@endsection

@section('header_end')
    <div class="compose_header position-absolute w-100 t-0 l-0 d-flex justify-content-between align-items-center zIndex-9 bg-white h-70 border-bottom px-4 d-none ms-4">
        <div class="fw-6 fs-18">{{ __("New Post") }}</div>
        <div class="fw-6 fs-18">
            <div class="btn btn-icon btn-light btn-hover-danger b-r-50 a-rotate closeCompose">
                <i class="fa-light fa-xmark"></i>
            </div>
        </div>
    </div>
	<a class="btn btn-dark btn-sm actionItem b-r-50 text-nowrap vc" href="{{ url_app("publishing/composer") }}" data-append-content="composer-scheduling" data-call-success="AppPubishing.openCompose();"><i class="fa-light fa-calendar-lines-pen"></i> {{ __("Compose") }}</a>
@endsection


@section('content')

    <div class="composer-scheduling position-absolute zIndex-9 wp-100 hp-100 d-none"></div>

@endsection
