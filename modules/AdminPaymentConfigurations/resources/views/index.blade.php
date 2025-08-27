@php
$payments = app("payments") ?? [];
@endphp

@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Payment Getway Configuration') }}" 
        description="{{ __('Integrate payment gateway for secure and seamless transactions') }}" 
    >
        <a class="btn btn-dark btn-sm" href="{{ module_url("create") }}">
            <span><i class="fa-light fa-plus"></i></span>
            <span>{{ __('Create new') }}</span>
        </a>
    </x-sub-header>
@endsection


@section('content')

    <div class="container pb-5">
        <div class="row">
            @if($payments)
            
                @foreach($payments as $value)

                    <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4 col-xxl-3 mb-4">
                        <label class="card shadow-none border border-gray-300" for="payment_{{ $value['id'] }}">
                            <div class="card-body d-flex justify-content-between align-items-center px-3 gap-16">
                                <div class="d-flex align-items-center gap-8 fs-13 fw-5 text-truncate">
                                    <div class="size-30 d-flex align-items-center justify-content-between fs-20">
                                        <img src="{{ $value['logo'] }}" class="w-100">
                                    </div>
                                    <div>
                                        {{ $value['name'] }}
                                    </div>
                                </div>
                                <div class="d-flex gap-16">
                                    <a class="fw-5 fs-16 text-gray-900 actionItem" href="{{ module_url($value['uri']) }}" data-popup="{{ $value['modal'] }}" data-call-success="">
                                        <i class="fa-light fa-gear"></i>
                                    </a>
                                </div>
                            </div>
                        </label>
                    </div>

                @endforeach

            @endif
            
        </div>
    </div>
@endsection