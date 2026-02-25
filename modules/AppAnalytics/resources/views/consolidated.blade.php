@extends('layouts.app')

@section('content')

@section('sub_header')
    <div class="d-flex justify-content-between align-items-center">
        <div style="flex: 1;">
            <x-sub-header 
                title="{{ __('Consolidated Analytics Overview') }}" 
                description="{{ __('Track and compare performance across social media platforms.') }}"
            />
        </div>
        <div>
            <a href="{{ route('app.analytics.export-all-pdf', ['brand_id' => session('brand_id')]) }}" class="btn btn-primary exportAllPDF" data-brand-name="{{$brand_name}}">
                <i class="fa fa-file-pdf"></i> {{ __('Export Report (PDF)') }}
            </a>
        </div>
		<div class="">
			<form class="auto-submit" action="{{ url()->current() }}" method="GET">
                <input type="hidden" name="brand_id" value="{{ session('brand_id') }}">
                <div class="d-flex align-items-center justify-content-between gap-8">
                    <div>
                        <div class="daterange d-none bg-white b-r-4 fs-12 border-gray-300 border" data-open="left"></div>
                    </div>
                </div>
            </form>
		</div>
    </div>
@endsection

<div class="container-fluid">

    {{-- Social Media Grid --}}
    <div class="row">
        @foreach($analytics as $data)
			@php
                $uniqueId = $data['unique_id'];
                $account = $data['analytics']['account'] ?? [];
                
                // Get followers/subscribers count based on platform
                $followersLabel = 'Followers';
                $followersCount = 0;
                
                if ($data['social'] === 'youtube') {
                    $followersLabel = 'Subscribers';
                    $followersCount = $account['subscribers'] ?? 0;
                }else if ($data['social'] === 'facebook') {
                    $followersLabel = 'Followers';
					$followersCount = $account['followers_count'] ?? 0;
                }else if ($data['social'] === 'linkedin') {
                    $followersLabel = 'Followers';
					$followersCount = $data['analytics']['overview']['followers']['value'] ?? 0;
                } else {
                    $followersCount = $account['followers_count'] ?? 0;
                }
            @endphp
            <div class="col-lg-6 mb-4">
                <div class="card social-analytics-card" data-account-name="{{$account['name']}}" data-social="{{ $data['social'] }}" data-account-id="{{ $uniqueId }}">
                    <div class="card-body">
                        {{-- Platform Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0 platform-title">
                                @if($data['social'] === 'facebook')
                                    <i class="fab fa-facebook text-primary"></i> Facebook
                                @elseif($data['social'] === 'instagram')
                                    <i class="fab fa-instagram text-danger"></i> Instagram
                                @elseif($data['social'] === 'linkedin')
                                    <i class="fab fa-linkedin text-info"></i> LinkedIn
                                @elseif($data['social'] === 'tiktok')
                                    <i class="fab fa-tiktok text-dark"></i> TikTok
                                @elseif($data['social'] === 'youtube')
                                    <i class="fab fa-youtube text-danger"></i> YouTube
                                @endif
                            </h4>
							<small> 
							<img data-src="{{ Media::url($data['account']->avatar) }}" src="{{ theme_public_asset('img/default.png') }}" class="b-r-100 w-25 mr-5 border-1 lazyload" onerror="this.src='{{ theme_public_asset('img/default.png') }}'"><small class="text-muted">{{ $account['name'] ?? $account['username'] ?? '' }} - {{ __($followersLabel) }} : {{ number_format($followersCount) }} </small> </small> 
							
                            <a href="{{ route('app.analytics.show', ['social' => $data['social'], 'id_secure' => $data['account']->id_secure, 'brand_id' => session('brand_id')]) }}" 
                               class="">
                                <small> <i class="fa fa-arrow-right mr-5"></i>{{ __('View Details') }}</small> 
                            </a>
                        </div>

                        {{-- Main Chart --}}
                        @if(!empty($data['mainChart']))
                            <div class="mb-3">
                                <div class="export-chart" 
                                     id="chart-{{ $uniqueId }}" 
                                     data-social="{{ $data['social'] }}"
                                     data-account-id="{{ $uniqueId }}"
									 data-account-name="{{$account['name']}}"
                                     style="height: 250px;"></div>
                            </div>
                        @endif

                        {{-- Metrics Grid --}}
                        <div class="row g-2">
                            @php
                                $overview = $data['analytics']['overview'] ?? [];
                                $displayMetrics = array_slice($overview, 0, 6); // Show first 6 metrics
                            @endphp
                            
                            @foreach($displayMetrics as $key => $metric)
                                <div class="col-4">
                                    <div class="metric-card p-2">
                                        <div class="metric-icon mb-1">
                                            @if($data['social'] === 'facebook')
                                                <i class="fab fa-facebook"></i>
                                            @elseif($data['social'] === 'instagram')
                                                <i class="fab fa-instagram"></i>
                                            @elseif($data['social'] === 'linkedin')
                                                <i class="fab fa-linkedin"></i>
                                            @elseif($data['social'] === 'tiktok')
                                                <i class="fab fa-tiktok"></i>
                                            @elseif($data['social'] === 'youtube')
                                                <i class="fab fa-youtube"></i>
                                            @endif
                                            {{ $metric['label'] ?? __(ucwords(str_replace('_', ' ', $key))) }}
                                        </div>
                                        <div class="metric-value">
                                            {{ number_format($metric['value'] ?? 0) }}
                                        </div>
                                        @if(isset($metric['change']))
    @php
        $change = $metric['change'];
        $absChange = abs($change);
    @endphp
    <div class="metric-change {{ $change >= 0 ? 'text-success' : 'text-danger' }}">
        @if($absChange > 100)
            <i class="fa fa-arrow-{{ $change >= 0 ? 'up' : 'down' }}"></i>100%
        @else
            <i class="fa fa-arrow-{{ $change >= 0 ? 'up' : 'down' }}"></i>
            {{ number_format($absChange, 0) }}%
        @endif
    </div>
@endif
                                        
                                        {{-- Mini sparkline chart --}}
                                        @if(!empty($metric['trend']))
                                            <div class="export-chart mini-chart" 
                                                 id="chart-{{ $uniqueId }}-{{ $key }}" 
                                                 data-social="{{ $data['social'] }}"
                                                 data-account-id="{{ $uniqueId }}"
                                                 style="height: 40px;"></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('styles')
<style>
    .social-analytics-card {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.2s;
    }
    
    .social-analytics-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    
    .platform-title {
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .metric-card {
        background: #f8f9fa;
        border-radius: 8px;
        text-align: center;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .metric-icon {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 500;
    }
    
    .metric-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #212529;
        margin: 4px 0;
    }
    
    .metric-change {
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .mini-chart {
        margin-top: 8px;
    }
</style>
@endpush
@endsection
@section('script')
<script>
    
    $(document).ready(function() {
        
        // Check if ApexCharts is loaded
        if (typeof ApexCharts === 'undefined') {
            //console.error('ApexCharts is not loaded! Please add it to your layout.');
            return;
        }
        
        // Initialize charts for each account
        @foreach($analytics as $data)
            @php 
                $social = $data['social'];
                $uniqueId = $data['unique_id'];
            @endphp
                        
            @if(!empty($data['mainChart']))
                initMainChart('chart-{{ $uniqueId }}', @json($data['mainChart']), '{{ $social }}');
            @else
            @endif
            
            // Mini sparkline charts
            @foreach($data['analytics']['overview'] ?? [] as $key => $metric)
                @if(!empty($metric['trend']))
                    initMiniChart('chart-{{ $uniqueId }}-{{ $key }}', @json($metric['trend']));
                @endif
            @endforeach
        @endforeach
        
        // Initialize export functionality
        if (typeof Main !== 'undefined' && typeof Main.exportAllCharts === 'function') {
            Main.exportAllCharts();
        }
    });
    
    function initMainChart(chartId, data, social) {
        
        if (!data || !data.series || !data.categories) {
            //console.error('Invalid chart data for', chartId);
            return;
        }
        
        // Check if we have valid data
        if (data.series.length === 0 || data.categories.length === 0) {
            console.warn('Empty chart data for', chartId);
            const chartElement = document.querySelector(`#${chartId}`);
            if (chartElement) {
                chartElement.closest('.mb-3').style.display = 'none';
            }
            return;
        }
        
        const chartElement = document.querySelector(`#${chartId}`);
        if (!chartElement) {
            console.error('Chart element not found:', `#${chartId}`);
            return;
        }
        
        const options = {
            series: data.series,
            chart: {
                type: 'area',
                height: 250,
                toolbar: { show: false },
                animations: { 
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                },
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: data.categories,
                tickAmount: 8,
                labels: { 
                    style: { 
                        fontSize: '10px',
                        colors: '#888'
                    },
                    rotate: -45,
                    rotateAlways: false,
                    hideOverlappingLabels: true,
                    trim: true
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: { 
                    style: { 
                        fontSize: '10px',
                        colors: '#888'
                    },
                    formatter: function(val) {
                        if (val >= 1000000) {
                            return (val / 1000000).toFixed(1) + 'M';
                        } else if (val >= 1000) {
                            return (val / 1000).toFixed(1) + 'K';
                        }
                        return Math.round(val);
                    }
                }
            },
            colors: getColorForSocial(social),
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'left',
                fontSize: '12px',
                fontWeight: 600,
                markers: {
                    width: 12,
                    height: 12,
                    radius: 3
                },
                itemMargin: {
                    horizontal: 10,
                    vertical: 5
                }
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: false
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function(val) {
                        return val !== null ? val.toLocaleString() : '0';
                    }
                }
            }
        };
        
        try {
            const chart = new ApexCharts(chartElement, options);
            chart.render();
            
            // Store chart instance with unique ID
            if (!window.ChartInstances) window.ChartInstances = {};
            window.ChartInstances[chartId] = chart;
            
        } catch (error) {
            //console.error('Error rendering chart for', chartId, error);
        }
    }
    
    function initMiniChart(chartId, trendData) {
        const options = {
            series: [{
                name: 'Trend',
                data: trendData
            }],
            chart: {
                type: 'area',
                height: 40,
                sparkline: { enabled: true }
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                opacity: 0.3
            },
            colors: ['#6c757d']
        };
        
        const chart = new ApexCharts(
            document.querySelector(`#${chartId}`), 
            options
        );
        chart.render();
        
        if (!window.ChartInstances) window.ChartInstances = {};
        window.ChartInstances[chartId] = chart;
    }
    
    function getColorForSocial(social) {
        const colors = {
            facebook: ['#1877F2', '#45dc1a', '#dc1abf'],
            instagram: ['#F77737', '#E4405F', '#45dc1a'],
            linkedin: ['#0A66C2', '#dc1abf', '#25F4EE'],
            tiktok: ['#6908f4', '#FE2C55', '#25F4EE'],
            youtube: ['#FF0000', '#ab0fd6', '#E62117']
        };
        
        return colors[social] || ['#8B5CF6', '#EC4899', '#F59E0B'];
    }
</script>
@endsection