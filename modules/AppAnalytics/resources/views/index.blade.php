@extends('layouts.app')

@section('sub_header')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="flex: 1;">
            <x-sub-header 
                title="{{ __('Social Analytics') }}" 
                description="{{ __('Track and compare performance across social media platforms.') }}"
            />
        </div>
        <div>
            <a href="{{ route('app.analytics.consolidated', ['brand_id' => session('brand_id')]) }}" class="btn btn-primary">
                <i class="fa fa-file-pdf"></i> {{ __('View Consolidated Analytics Overview') }}
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="container pb-5 row row-cols-5">
        @forelse ($analytics as $network => $data)
            <div class="mb-5 col">
                <h4 class="fw-6 fs-18 mb-2">{{ $network }}</h4>
                <div class="row">
                    @foreach ($data as $key => $value)
                    <div class="col-md-12">
                        <div class="card mb-2">
                            <div class="card-body">
                                <div class="d-flex flex-grow-1 align-items-top gap-8">
                                    
                                    <div class="text-gray-600 size-40 min-w-40 d-flex align-items-center justify-content-between position-relative">
                                        <a href="{{ $value->url }}" target="_blank" class="text-gray-900 text-hover-primary">
                                            <img data-src="{{ Media::url($value->avatar) }}" src="{{ theme_public_asset('img/default.png') }}" class="b-r-100 w-full h-full border-1 lazyload" onerror="this.src='{{ theme_public_asset('img/default.png') }}'">
                                        </a>
                                        <span class="size-17 border-1 b-r-100 position-absolute fs-9 d-flex align-items-center justify-content-between text-center text-white b-0 r-0" style="background-color: {{ $value->module_color }};">
                                            <div class="w-100"><i class="{{ $value->module_icon }}"></i></div>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 fs-14 fw-5 text-truncate">
                                        <div class="text-truncate">
                                            <a href="{{ $value->url }}" target="_blank" class="text-gray-900 text-hover-primary">
                                                {{ $value->name }}
                                            </a>
                                        </div>
                                        <div class="fs-12 text-gray-600 text-truncate">
                                            {{ __( ucfirst( $value->social_network." ".$value->category ) ) }}
                                        </div>
                                    </div>

                                </div>
                                    
                            </div>
                            <div class="card-footer fs-12 d-flex justify-content-center gap-8">
                            <a href="{{ module_url($value->social_network."/".$value->id_secure) }}" class="d-flex flex-fill gap-8 align-items-center justify-content-center text-gray-900 text-hover-primary fw-5">
                                <i class="fa-light fa-chart-mixed"></i>
                                <span>{{ __("View") }}</span>
                            </a>
                        </div>
                        </div>
                    </div>
                    @endforeach



                    @if($data->isEmpty())
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-center justify-content-center py-1">
                                    <!--<span class="fs-70 mb-3 text-primary">
                                        <i class="fa-light fa-chart-mixed"></i>
                                    </span>-->
                                    <div class="fw-semibold fs-5 mb-2 text-gray-800">
                                        {{ __('No accounts yet') }}
                                    </div>
                                    <!--<div class="text-body-secondary mb-4">
                                        {{ __('Connect your social accounts to start tracking analytics and gain insights into your performance.') }}
                                    </div>-->
                                    <a href="{{ route('app.channels.index') }}" class="btn btn-dark">
                                        <i class="fa-light fa-plus me-1"></i> {{ __('Add an account') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
                <span class="fs-70 mb-3 text-primary">
                    <i class="fa-light fa-chart-mixed"></i>
                </span>
                <div class="fw-semibold fs-5 mb-2 text-gray-800">
                    {{ __('No analytics data available.') }}
                </div>
                <div class="text-body-secondary mb-4">
                    {{ __('Analytics data is not yet available for this section. Please check back later.') }}
                </div>
                <a href="{{ route('app.dashboard.index') }}" class="btn btn-dark">
                    <i class="fa-light fa-house"></i> {{ __('Dashboard') }}
                </a>
            </div>
        @endforelse
    </div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize the consolidated export
        Main.exportAllCharts();
    });
</script>
@endpush