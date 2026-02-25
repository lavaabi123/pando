@extends('layouts.app')
@include('apppublishing::header_center', [])

@section('sub_header')
    <x-sub-header 
        title="{{ __('Approval') }}" 
        description="{{ __('See all your approvals in one place for easy access and management.') }}" 
    >
    </x-sub-header>
@endsection

@section('header_end')
    <div class="compose_header position-absolute w-100 t-0 l-0 d-flex justify-content-between align-items-center zIndex-9 bg-white h-70 border-bottom px-4 d-none">
        <div class="fw-6 fs-18">{{ __("New Post") }}</div>
        <div class="fw-6 fs-18">
            <div class="btn btn-icon btn-light btn-hover-danger b-r-50 a-rotate closeCompose">
                <i class="fa-light fa-xmark"></i>
            </div>
        </div>
    </div>
    <a class="btn btn-dark btn-sm actionItem b-r-50 text-nowrap" href="{{ url_app("publishing/composer") }}" data-append-content="composer-scheduling" data-call-success="AppPubishing.openCompose();"><i class="fa-light fa-calendar-lines-pen"></i> {{ __("Compose") }}</a>
@endsection

@section('content')
    <div class="composer-scheduling position-absolute zIndex-9 wp-100 hp-100 top-0 d-none"></div>
    <div class="container">

        <div class="ajax-scroll" data-url="{{ module_url("list") }}" data-resp=".approval-list" data-scroll="document" data-call-success="initHorizontalScroll();">

            <div class="row approval-list">
                <div class="mb-50"></div>
            </div>

            <div class="pb-30 ajax-scroll-loading d-none">
                <div class="app-loading mx-auto mt-10 pl-0 pr-0">
                    <div></div>   
                    <div></div>    
                    <div></div>    
                    <div></div>    
                </div>
            </div>
        </div>

    </div>
@endsection