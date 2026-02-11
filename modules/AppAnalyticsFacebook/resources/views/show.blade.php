@extends('layouts.app')

@section('content')
@php
    $module = Module::find($account->module);
    $moduleInfo = $module->get('menu');
@endphp
<div class="border-bottom mb-5 pt-5 pb-2 bg-polygon">
    <div class="container">
        <div class="d-flex justify-content-center text-center">
            <div class="mb-5">
                <div class="size-80 size-chill position-relative mx-auto mb-3">
                    <img class="rounded-circle border mb-3 wp-100 hp-100" src="{{ Media::url($account->avatar) }}">
                    <div class="fs-12 position-absolute b-0 r-0 size-22 bg-white border b-r-100"><i class="{{ $moduleInfo['icon'] }}" style="color: {{ $moduleInfo['color'] }};"></i></div>
                </div>
                <div class="flex-fill mb-3">
                    <h4 class="mb-1 fs-20 fw-bold">{{ $account->name }}</h4>
                    <div class="text-muted small mb-1">{{ $analytics['account']['category'] ?? 'Unknown Category' }}</div>
                    <a href="{{ $account->url }}" class="small text-gray-600" target="_blank">{{ $account->url }}</a>
                </div>
                <div class="d-flex justify-content-center align-items-center gap-16">
                    <div class="fw-bold fs-16">{{ number_format($analytics['account']['fan_count'] ?? 0) }} {{ __('Fans') }}</div>
                    <div class="px-1 text-gray-500">|</div>
                    <div class="fw-bold fs-16">{{ number_format($analytics['account']['followers_count'] ?? 0) }} {{ __('Followers') }}</div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="container py-4">

    <form class="auto-submit" action="{{ url()->current(); }}" method="GET">
        <div class="d-flex justify-content-end gap-8">
            <a  href="{{ route('analytics.export.pdf', [ 'social' => request()->segment(3), 'id_secure' => request()->segment(4), 'daterange' => request()->input('daterange') ]) }}" class="btn btn-dark exportPDF"  data-brand-name="{{$brand_name}}" data-account="{{request()->segment(3)}}">{{ __("Export PDF") }}</a>
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div></div>
                <div class="d-flex align-items-center justify-content-between gap-8">
                    <div>
                        <div class="daterange d-none bg-white b-r-4 fs-12 border-gray-300 border" data-open="left"></div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        @php
            $overview = $analytics['overview'];
            $icons = [
                'likes' => 'fa-light fa-thumbs-up', 'follows' => 'fa-light fa-users', 'reach' => 'fa-light fa-eye',
                'impressions' => 'fa-light fa-repeat', 'engagements' => 'fa-light fa-comment',
                'page_views' => 'fa-light fa-binoculars', 'published_posts' => 'fa-light fa-paper-plane',
            ];
            $colors = [
                'likes' => 'primary', 'follows' => 'info', 'reach' => 'success',
                'impressions' => 'warning', 'engagements' => 'danger',
                'page_views' => 'dark', 'published_posts' => 'pink',
            ];
        @endphp

        @foreach ($overview as $key => $item)
            @php
                $icon = $icons[$key] ?? '📊';
                $color = $colors[$key] ?? 'muted';
                $change = $item['change'] ?? 0;
                $changeClass = $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted');
                $changeLabel = $change > 0 ? '+' . $change . '%' : ($change < 0 ? $change . '%' : '0%');
            @endphp
            <div class="col-6 col-md-4 col-lg-4">
                <div class="card hp-100">
                    <div class="card-body">
                        
                        <div class="d-flex align-items-center gap-8">
                            <div class="size-35 d-flex align-items-center justify-content-center b-r-100 bg-{{ $color }}-100 text-{{ $color }}">
                                <span><i class="{{ $icon }}"></i></span>
                            </div>
                            <span class="text-gray-700">{{ __(ucwords(str_replace('_', ' ', $key))) }}</span>
                        </div>

                        <div class="d-flex flex-column justify-content-center pt-3">
                            <div class="text-muted small"></div>
                            <div class="fw-bold fs-30 mb-3">{{ number_format($item['value']) }}</div>
                            <div class="small {{ $changeClass }}">
                                <i class="{{ $change >= 0 ? 'fa-light fa-arrow-trend-up' : 'fa-light fa-arrow-trend-down' }}"></i> {{ $changeLabel }} {{ __('vs last period') }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>


    {{-- Charts Section --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Overview Trends') }}</h5>
                </div>
                <div class="card-body">
                    <div id="overview-chart" class="export-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Fans History') }}</h5>
                </div>
                <div class="card-body">
                    <div id="fan-history-chart" class="export-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['fan_summary']; @endphp
        <div class="col-12">
            <div class="row hp-100 g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="fs-5 fs-16">{{ __('Gained vs Lost Fans') }}</h5>
                        </div>
                        <div class="card-body">
                            <div id="gained-lost-fans-chart" class="export-chart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 hp-100 min-h-398 max-h-398">
                    <div class="d-flex flex-column hp-100 gap-18">
                        <div class="flex-fill text-white">
                            <div class="card hp-100">
                                <div class="d-flex align-items-center justify-content-between card-body b-r-10">
                                    <div class="d-flex gap-15 justify-content-between align-items-center wp-100">
                                        <div class="mt-auto">
                                            <div class="d-flex align-items-end gap-8">
                                                <div class="fw-6 fs-30">{{ number_format($summary['new_fans']) }}</div>
                                            </div>
                                            <div class="fw-4 fs-13 text-gray-600">{{ __('Gained Fans') }}</div>
                                        </div>
                                        <div class="size-40 b-r-10 bg-success-100 text-success fs-20 d-flex align-items-center justify-content-center">
                                            <i class="fa-light fa-user-plus"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex-fill text-white">
                            <div class="card hp-100">
                                <div class="d-flex align-items-center justify-content-between card-body b-r-10">
                                    <div class="d-flex gap-15 justify-content-between align-items-center wp-100">
                                        <div class="mt-auto">
                                            <div class="d-flex align-items-end gap-8">
                                                <div class="fw-6 fs-30">{{ number_format($summary['lost_fans']) }}</div>
                                            </div>
                                            <div class="fw-4 fs-13 text-gray-600">{{ __('Lost Fans') }}</div>
                                        </div>
                                        <div class="size-40 b-r-10 bg-danger-100 text-danger fs-20 d-flex align-items-center justify-content-center">
                                            <i class="fa-light fa-user-minus"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex-fill text-white">
                            <div class="card hp-100">
                                <div class="d-flex align-items-center justify-content-between card-body b-r-10">
                                    <div class="d-flex gap-15 justify-content-between align-items-center wp-100">
                                        <div class="mt-auto">
                                            <div class="d-flex align-items-end gap-8">
                                                <div class="fw-6 fs-30">{{ number_format($summary['net_fans']) }}</div>
                                            </div>
                                            <div class="fw-4 fs-13 text-gray-600">{{ __('Net Fans') }}</div>
                                        </div>
                                        <div class="size-40 b-r-10 bg-primary-100 text-primary fs-20 d-flex align-items-center justify-content-center">
                                            <i class="fa-light fa-user-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['postReachSummaryChart']['summary']; @endphp
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Post Reach') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="post-reach-chart" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Reach') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['total_reach']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Organic Reach') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['organic']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Paid Reach') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['paid']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Avg Daily Reach') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['avg_daily']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['postImpressionSummaryChart']['summary']; @endphp
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Post Impressions') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="post-impression-chart" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Impressions') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['total']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Organic Impressions') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['organic']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Paid Impressions') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['paid']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Avg Daily Impressions') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['avg_daily']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['page_views_chart']['summary']; @endphp
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="fs-5 fs-16 mb-0">{{ __('Page Views') }}</h5>

                        <div class="d-flex align-items-center" style="justify-content:flex-end;">
                            <div role="button" id="data-labels_page_views" class="me-3" data-toggle="tooltip" data-placement="bottom" title="{{ __('Toggle Label') }}"><i class="fal fa-tags"></i></div>
                            <div role="button" id="markers_page_views" class="me-3" data-toggle="tooltip" data-placement="bottom" title="{{ __('Toggle Marker') }}"><i class="fal fa-map-pin"></i></div>
                            <div role="button" id="line_page_views" class="me-3" data-toggle="tooltip" data-placement="bottom" title="{{ __('Line') }}"><i class="fal fa-line-chart"></i></div>
                            <div role="button" id="area_page_views" class="me-3" data-toggle="tooltip" data-placement="bottom" title="{{ __('Area') }}"><i class="fal fa-area-chart"></i></div>
                            <div role="button" id="toggle_page_views" class="me-3" data-toggle="tooltip" data-placement="bottom" title="{{ __('Table') }}"><i class="fal fa-table"></i></div>
                        </div>
                    </div>
                </div>
                <div class="card-body border-bottom">
                    <div id="page-views-chart" class="export-chart" style="height: 300px;"></div>
                
                    <div id="page-views-table-wrapper" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" id="datatable_page_views">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th class="text-end">{{ __('Page Views') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total page views') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['postEngagementRateSummary']['summary']; @endphp
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Post Engagements') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="post-engagement-chart" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Engagement Rate (per Impression)') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['rate'], 2) }}%</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Engagements') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['engagements']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Impressions') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['impressions']) }}</div>
                    </div>
                </div>
            </div>
        </div>
		
		<!--
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Fans Location') }}</h5>
                </div>
                <div class="card-body">
                    <div id="fans-map-chart" class="export-chart" style="height: 510px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Top Countries') }}</h5>
                </div>
                <div class="card-body p-0 min-h-550">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="px-4">{{ __('Country') }}</th>
                                <th class="px-4 text-end">{{ __('Fans') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($analytics['topFansCountries'])
                                @foreach ($analytics['topFansCountries'] as $row)
                                    <tr>
                                        <td class="px-4">
                                            <span class="flag-icon flag-icon-{{ strtolower($row['code']) }} me-2"></span>
                                            {{ $row['country'] }}
                                        </td>
                                        <td class="px-4 text-end fw-bold">{{ number_format($row['fans']) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-4 py-5 border-0" colspan="2">
                                        <div class="empty"></div>
                                    </td>
                                </tr>
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        -->
		
		<div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Video View Completion') }}</h5>
                </div>
                <div class="card-body">
                    <div id="video-view-pie-chart" class="export-chart" style="height: 300px;"></div>
                </div>

            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Video Organic vs Paid Views') }}</h5>
                </div>
                <div class="card-body">
                    <div id="video-views-pie-chart" class="export-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Video Play Methods') }}</h5>
                </div>
                <div class="card-body">
                    <div id="video-play-method-chart" class="export-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    @php $posts = $analytics['postHistoryList']; @endphp
    <div class="card mt-5 px-0">
        <div class="card-header">
            <h5 class="fs-5 fs-16">{{ __('Post History') }}</h5>

            <div class="card-tooltip">
                <div class="d-flex flex-wrap gap-8">
                    <div class="d-flex">
                        <div class="form-control form-control-sm">
                            <button class="btn btn-icon">
                                <i class="fa-duotone fa-solid fa-magnifying-glass"></i>
                            </button>
                            <input name="search" placeholder="{{ __('Search...') }}" type="text"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="DataTable_Static">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 60px;">{{ __('Image') }}</th>
                            <th>{{ __('Message') }}</th>
                            <th>{{ __('Date') }}</th>
                            <!--<th>{{ __('Impressions') }}</th>-->
                            <th>{{ __('Reach') }}</th>
                            <th>{{ __('Likes') }}</th>
                            <th>{{ __('Reactions') }}</th>
                            <th>{{ __('Shares') }}</th>
                            <th>{{ __('Comments') }}</th>
                            <th>{{ __('View') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                            <tr>
                                {{-- Image or icon --}}
                                <td class="text-center">
                                    @if (!empty($post['full_picture']))
                                        <img src="{{ Media::url($post['full_picture']) }}" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-light border rounded" style="width: 48px; height: 48px;">
                                            <i class="fa-light fa-message-lines text-gray-600 fs-4"></i>
                                        </div>
                                    @endif
                                </td>

                                {{-- Message --}}
                                <td>{{ \Str::limit($post['message'], 80) }}</td>

                                {{-- Date --}}
                                <td class="text-nowrap text-gray-700 fs-14">{{ \Carbon\Carbon::parse($post['created_time'])->format('M d, Y') }}</td>

                                {{-- Metrics --}}
                                <!--<td class="text-center text-primary">{{ number_format($post['metrics']['post_impressions'] ?? 0) }}</td>-->
                                <td class="text-center text-info">{{ number_format($post['metrics']['post_impressions_unique'] ?? 0) }}</td>
                                <td class="text-center text-success">{{ number_format($post['metrics']['likes'] ?? 0) }}</td>
                                <td class="text-center text-danger">{{ number_format($post['metrics']['reactions'] ?? 0) }}</td>
                                <td class="text-center text-warning">{{ number_format($post['metrics']['shares'] ?? 0) }}</td>
                                <td class="text-center text-dark">{{ number_format($post['metrics']['comments'] ?? 0) }}</td>

                                {{-- View button --}}
                                <td class="text-center">
                                    @if (!empty($post['permalink_url']))
                                        <a href="{{ $post['permalink_url'] }}" target="_blank">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
var overviewData = {!! json_encode($analytics['overview_chart']) !!};

// Check if series exists before setting colors
if (overviewData && overviewData.series && Array.isArray(overviewData.series)) {
    if (overviewData.series[0]) overviewData.series[0].color = '#675dff';
    if (overviewData.series[1]) overviewData.series[1].color = '#13c2c2';
    if (overviewData.series[2]) overviewData.series[2].color = '#ffa940';
    
    Main.Chart('areaspline', overviewData.series, 'overview-chart', {
    xAxis: {
        categories: overviewData.categories,
        title: { text: '' },
        crosshair: { width: 2, color: '#ddd', dashStyle: 'Solid' },
        labels: {
            rotation: 0,
            useHTML: true,
            formatter: function () {
                const pos = this.pos;
                const total = this.axis.categories.length;
                if (pos === 0)
                    return `<div style="text-align:left;transform:translateX(60px);width:140px;">${this.value}</div>`;
                else if (pos === total - 1)
                    return `<div style="text-align:right;transform:translateX(-55px);width:140px;">${this.value}</div>`;
                return '';
            },
            style: {
                fontSize: '13px',
                whiteSpace: 'nowrap'
            },
            overflow: 'none',
            crop: false
        }
    },
    yAxis: {
        title: { text: '' },
        gridLineColor: '#f3f4f6',
        gridLineDashStyle: 'Dash',
        gridLineWidth: 1
    },
    title: { text: '{{ __("Overview Trends") }}' },
    legend: { enabled: false },
    plotOptions: {
        areaspline: {
            fillOpacity: 0.1,
            lineWidth: 3,
            marker: { enabled: false }
        },
        series: {
            stacking: 'normal',
            marker: {
                enabled: false,
                states: { hover: { enabled: false } }
            },
            color: '#ee1255',
            fillColor: {
                linearGradient: [0, 0, 0, 200],
                stops: [
                    [0, 'rgba(103, 93, 255, 0.4)'],
                    [1, 'rgba(255, 255, 255, 0)']
                ]
            }
        }
    }
});
} else {
    console.error('Invalid overview data structure:', overviewData);
}

var fanHistoryChart = {!! json_encode($analytics['fan_history_chart']) !!};
Main.Chart('column', fanHistoryChart.series, 'fan-history-chart', {
    title: { text: '{{ __("Fan History") }}' },
    xAxis: {
        categories: fanHistoryChart.categories,
        lineColor: '#ddd',
        lineWidth: 1,
        gridLineWidth: 0,
        labels: {
            rotation: 0,
            useHTML: true,
            formatter: function () {
                const pos = this.pos;
                const total = this.axis.categories.length;
                if (pos === 0)
                    return `<div style="text-align:left;transform:translateX(60px);width:140px;">${this.value}</div>`;
                else if (pos === total - 1)
                    return `<div style="text-align:right;transform:translateX(-55px);width:140px;">${this.value}</div>`;
                return '';
            },
            style: {
                fontSize: '13px',
                whiteSpace: 'nowrap'
            },
            overflow: 'none',
            crop: false
        }
    },
    yAxis: {
        title: { text: ' ' },
        gridLineWidth: 1,
        gridLineColor: '#f3f4f6',
        gridLineDashStyle: 'Dash'
    },
    legend: { enabled: false },
    tooltip: {
        shared: true,
        valueSuffix: ' {{ __('fans') }}'
    },
    plotOptions: {
        column: {
            borderRadius: 6,
            colorByPoint: true,
            dataLabels: {
                enabled: false,
                formatter: function () {
                    return this.y.toLocaleString();
                }
            }
        }
    },
	colors: ['#ff7875']
});

var gainedLostFanChartData = {!! json_encode($analytics['gained_lost_fans_chart']) !!};
gainedLostFanChartData.series[0].color = '#675dff';
gainedLostFanChartData.series[1].color = '#ee1255';
Main.Chart('column', gainedLostFanChartData.series, 'gained-lost-fans-chart', {
    xAxis: {
        categories: gainedLostFanChartData.categories,
        title: { text: '' },
        crosshair: { width: 2, color: '#ddd', dashStyle: 'Solid' },
        labels: {
            rotation: 0,
            useHTML: true,
            formatter: function () {
                const pos = this.pos;
                const total = this.axis.categories.length;
                if (pos === 0)
                    return `<div style="text-align:left;transform:translateX(60px);width:140px;">${this.value}</div>`;
                else if (pos === total - 1)
                    return `<div style="text-align:right;transform:translateX(-55px);width:140px;">${this.value}</div>`;
                return '';
            },
            style: {
                fontSize: '13px',
                whiteSpace: 'nowrap'
            },
            overflow: 'none',
            crop: false
        }
    },
    title: { text: '{{ __("Gained vs Lost Fans") }}' },
    yAxis: {
        title: { text: '' },
        gridLineColor: '#f3f4f6',
        gridLineDashStyle: 'Dash',
        gridLineWidth: 1
    },
    plotOptions: {
        column: {
            borderRadius: 4,
            pointPadding: 0.2,
            groupPadding: 0.1
        }
    },
	colors: ['#4096ff', '#ff7875', '#faad14']
});

var pageViewsData = {!! json_encode($analytics['page_views_chart']) !!};
var pageViewsChart = Main.Chart('areaspline', pageViewsData.series, 'page-views-chart', {
    xAxis: {
        categories: pageViewsData.categories,
        title: { text: '' },
        crosshair: { width: 2, color: '#ddd', dashStyle: 'Solid' },
        labels: {
            rotation: 0,
            useHTML: true,
            formatter: function () {
                const pos = this.pos;
                const total = this.axis.categories.length;
                if (pos === 0)
                    return `<div style="text-align:left;transform:translateX(60px);width:140px;">${this.value}</div>`;
                else if (pos === total - 1)
                    return `<div style="text-align:right;transform:translateX(-55px);width:140px;">${this.value}</div>`;
                return '';
            },
            style: {
                fontSize: '13px',
                whiteSpace: 'nowrap'
            },
            overflow: 'none',
            crop: false
        }
    },
    yAxis: {
        title: { text: '' },
        gridLineColor: '#f3f4f6',
        gridLineDashStyle: 'Dash',
        gridLineWidth: 1
    },
    title: { text: '{{ __("Page Views") }}' },
    legend: { enabled: false },
    plotOptions: {
        areaspline: {
            fillOpacity: 0.1,
            lineWidth: 3,
            marker: { enabled: false }
        },
        series: {
            color: '#ee1255',
            fillColor: {
                linearGradient: [0, 0, 0, 200],
                stops: [
                    [0, 'rgba(103, 93, 255, 0.4)'],
                    [1, 'rgba(255, 255, 255, 0)']
                ]
            }
        }
    }
});


// ---- Page Views chart tools (WEB only) ----
(function () {
    if (!window.ChartInstances || !window.ChartInstances['page-views-chart']) return;

    const chart = window.ChartInstances['page-views-chart'];

    // Build table from categories + first series data
    try {
        const categories = (pageViewsData && pageViewsData.categories) ? pageViewsData.categories : [];
        const series0 = chart.series && chart.series[0] ? chart.series[0] : null;
        const tbody = document.querySelector('#datatable_page_views tbody');
        if (tbody && series0) {
            tbody.innerHTML = '';
            const points = series0.data || [];
            categories.forEach((label, i) => {
                const y = points[i] ? points[i].y : (Array.isArray(series0.options.data) ? series0.options.data[i] : 0);
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${label ?? ''}</td><td class="text-end">${Number(y || 0).toLocaleString()}</td>`;
                tbody.appendChild(tr);
            });
        }
    } catch (e) {
        // ignore table build errors
    }

    let enableDataLabels = false;
    let enableMarkers = false;

    const showChartHideTable = () => {
        const chartEl = document.getElementById('page-views-chart');
        const tableWrap = document.getElementById('page-views-table-wrapper');
        if (chartEl) chartEl.style.display = 'block';
        if (tableWrap) tableWrap.classList.add('d-none');
    };

    const showTableHideChart = () => {
        const chartEl = document.getElementById('page-views-chart');
        const tableWrap = document.getElementById('page-views-table-wrapper');
        if (chartEl) chartEl.style.display = 'none';
        if (tableWrap) tableWrap.classList.remove('d-none');
    };

    const btnLabels = document.getElementById('data-labels_page_views');
    const btnMarkers = document.getElementById('markers_page_views');
    const btnLine = document.getElementById('line_page_views');
    const btnArea = document.getElementById('area_page_views');
    const btnToggle = document.getElementById('toggle_page_views');

    if (btnLabels) btnLabels.addEventListener('click', () => {
        showChartHideTable();
        chart.series.forEach(s => s.update({ dataLabels: { enabled: enableDataLabels } }, false));
        chart.redraw();
        enableDataLabels = !enableDataLabels;
    });

    if (btnMarkers) btnMarkers.addEventListener('click', () => {
        showChartHideTable();
        chart.series.forEach(s => s.update({ marker: { enabled: enableMarkers } }, false));
        chart.redraw();
        enableMarkers = !enableMarkers;
    });

    if (btnLine) btnLine.addEventListener('click', () => {
        showChartHideTable();
        chart.series.forEach(s => s.update({ type: 'spline' }, false));
        chart.redraw();
    });

    if (btnArea) btnArea.addEventListener('click', () => {
        showChartHideTable();
        chart.series.forEach(s => s.update({ type: 'areaspline' }, false));
        chart.redraw();
    });

    if (btnToggle) btnToggle.addEventListener('click', () => {
        const chartEl = document.getElementById('page-views-chart');
        if (chartEl && chartEl.style.display === 'none') {
            showChartHideTable();
        } else {
            showTableHideChart();
        }
    });
})();

var postReachChart = {!! json_encode($analytics['postReachSummaryChart']) !!};
Main.Chart('column', postReachChart.series, 'post-reach-chart', {
    title: { text: '{{ __("Post Reach Metrics") }}' },
    xAxis: {
        categories: postReachChart.categories,
        lineColor: '#ddd',
        labels: {
            style: {
                fontSize: '13px',
                color: '#333'
            }
        }
    },
    yAxis: {
        title: { text: '' },
        gridLineColor: '#f3f4f6',
        gridLineDashStyle: 'Dash'
    },
    legend: { enabled: false },
    tooltip: {
        shared: true,
        valueSuffix: ' reach'
    },
    plotOptions: {
        column: {
            borderRadius: 6,
            colorByPoint: true,
			maxPointWidth: 80,
            dataLabels: {
                enabled: true,
                formatter: function () {
                    return this.y.toLocaleString();
                }
            }
        }
    },
    colors: ['#6366f1', '#ec4899', '#ff9800']
});

var impressionChart = {!! json_encode($analytics['postImpressionSummaryChart']) !!};
Main.Chart('column', impressionChart.series, 'post-impression-chart', {
    title: { text: '{{ __("Post Impression Metrics") }}' },
    xAxis: {
        categories: impressionChart.categories,
        lineColor: '#ddd',
        labels: {
            style: {
                fontSize: '13px',
                color: '#333'
            }
        }
    },
    yAxis: {
        title: { text: '' },
        gridLineColor: '#f3f4f6',
        gridLineDashStyle: 'Dash'
    },
    legend: { enabled: false },
    tooltip: {
        shared: true,
        valueSuffix: ' impressions'
    },
    plotOptions: {
        column: {
            borderRadius: 6,
            colorByPoint: true,
			maxPointWidth: 80,
            dataLabels: {
                enabled: true,
                formatter: function () {
                    return this.y.toLocaleString();
                }
            }
        }
    },
	colors: ['#9254de', '#13c2c2', '#ff9800']
});

const postEngagementChartData = {!! json_encode($analytics['postEngagementSummaryChart']) !!};
Main.Chart('column', postEngagementChartData.series, 'post-engagement-chart', {
    title: { text: '{{ __("Post Engagement Metrics") }}' },
    xAxis: {
        categories: postEngagementChartData.categories,
        lineColor: '#ddd',
        labels: {
            style: {
                fontSize: '13px',
                color: '#333'
            }
        }
    },
    yAxis: {
        title: { text: '' },
        gridLineColor: '#f3f4f6',
        gridLineDashStyle: 'Dash'
    },
    legend: { enabled: false },
    tooltip: {
        shared: true,
        valueSuffix: ' {{ __("times") }}'
    },
    plotOptions: {
        column: {
            borderRadius: 6,
            colorByPoint: true,
            dataLabels: {
                enabled: true,
                formatter: function () {
                    return this.y.toLocaleString();
                }
            }
        }
    },
	colors: ['#6366f1', '#ec4899', '#ff9800', '#14b8a6']
});

var videoCompletionData = {!! json_encode($analytics['videoViewCompletionChart']) !!};
var totalViews = 0;
videoCompletionData.forEach(function(item) {
    totalViews += item.y || item[1] || 0;
});

Highcharts.chart('video-view-pie-chart', {
    chart: {
        type: 'pie',
        height: 300,
        spacingBottom: 20,
        backgroundColor: 'transparent'
    },
    title: {
        text: ''  // Empty (it's hidden anyway)
    },
    subtitle: {
        useHTML: true,  // IMPORTANT: Enables HTML formatting
        text: '<div style="text-align:center;">' +
              '<div style="font-size:25px; font-weight:bold; color:#333; line-height:1.2;">' + 
              totalViews.toLocaleString() + 
              '</div>' +
              '<div style="font-size:12px; color:#999; margin-top:5px;">' +
              'Total Views' +
              '</div>' +
              '</div>',
        align: 'center',
        verticalAlign: 'middle',
        y: -25  // Centered vertically
    },
    tooltip: {
        enabled: false
    },
    credits: {
        enabled: false
    },
    plotOptions: {
        pie: {
            innerSize: '70%',
            size: '100%',
            center: ['50%', '45%'],
            dataLabels: {
                enabled: false
            },
            showInLegend: true
        }
    },
    legend: {
        enabled: true,
        align: 'center',
        verticalAlign: 'bottom',
        layout: 'horizontal',
        y: -10,
        itemStyle: {
            fontSize: '12px',
            color: '#666',
            fontWeight: 'normal'
        },
        itemDistance: 0,
		labelFormatter: function() {
            return this.name + ' - <b>' + this.y.toLocaleString() + '</b>';
        }
    },
    series: [{
        name: 'Views',
        colorByPoint: true,
        data: videoCompletionData
    }],
    colors: ['#70ad47', '#ffc000']
});

var videoOrganicPaidData = {!! json_encode($analytics['videoOrganicPaidChart']) !!};

var totalOrganicPaid = 0;
videoOrganicPaidData.forEach(function (item) {
    totalOrganicPaid += item.y || item[1] || 0;
});

Highcharts.chart('video-views-pie-chart', {
    chart: {
        type: 'pie',
        height: 300,
        spacingBottom: 20,
        backgroundColor: 'transparent'
    },

    title: { text: '' },

    subtitle: {
        useHTML: true,
        text:
            '<div style="text-align:center;">' +
                '<div style="font-size:25px; font-weight:bold; color:#333; line-height:1.2;">' +
                    totalOrganicPaid.toLocaleString() +
                '</div>' +
                '<div style="font-size:12px; color:#999; margin-top:5px;">Total Views</div>' +
            '</div>',
        align: 'center',
        verticalAlign: 'middle',
        y: -25
    },

    tooltip: { enabled: false },
    credits: { enabled: false },

    plotOptions: {
        pie: {
            innerSize: '70%',
            size: '100%',
            center: ['50%', '45%'],
            dataLabels: { enabled: false },
            showInLegend: true
        }
    },

    legend: {
		enabled: true,
		layout: 'vertical',
		align: 'center',
		verticalAlign: 'bottom',
		itemMarginTop: 6,
		itemMarginBottom: 6,
		itemStyle: {
			fontSize: '12px',
			color: '#666',
			fontWeight: 'normal'
		},
		labelFormatter: function () {
			return this.name + ' - <b>' + this.y.toLocaleString() + '</b>';
		}
	},

    series: [{
        name: 'Views',
        colorByPoint: true,
        data: videoOrganicPaidData
    }],

    colors: ['#13c2c2', '#ff4d4f'] // Organic / Paid
});

var videoPlayMethodData = {!! json_encode($analytics['videoPlayMethodChart']) !!};

var totalPlayMethods = 0;
videoPlayMethodData.forEach(function (item) {
    totalPlayMethods += item.y || item[1] || 0;
});

Highcharts.chart('video-play-method-chart', {
    chart: {
        type: 'pie',
        height: 300,
        spacingBottom: 20,
        backgroundColor: 'transparent'
    },

    title: { text: '' },

    subtitle: {
        useHTML: true,
        text:
            '<div style="text-align:center;">' +
                '<div style="font-size:25px; font-weight:bold; color:#333; line-height:1.2;">' +
                    totalPlayMethods.toLocaleString() +
                '</div>' +
                '<div style="font-size:12px; color:#999; margin-top:5px;">Total Plays</div>' +
            '</div>',
        align: 'center',
        verticalAlign: 'middle',
        y: -25
    },

    tooltip: { enabled: false },
    credits: { enabled: false },

    plotOptions: {
        pie: {
            innerSize: '70%',
            size: '100%',
            center: ['50%', '45%'],
            dataLabels: { enabled: false },
            showInLegend: true
        }
    },

    legend: {
		enabled: true,
		layout: 'vertical',
		align: 'center',
		verticalAlign: 'bottom',
		itemMarginTop: 6,
		itemMarginBottom: 6,
		itemStyle: {
			fontSize: '12px',
			color: '#666',
			fontWeight: 'normal'
		},
		labelFormatter: function () {
			return this.name + ' - <b>' + this.y.toLocaleString() + '</b>';
		}
	},

    series: [{
        name: 'Plays',
        colorByPoint: true,
        data: videoPlayMethodData
    }],

    colors: ['#4096ff', '#ff7875', '#faad14'] // Click / Auto / Other
});


const fansLocationMapChart = {!! json_encode($analytics['fansLocationMapChart']) !!};
Main.Chart('map', fansLocationMapChart, 'fans-map-chart', {
    chart: { map: 'custom/world' },
    title: { text: '{{ __('Fan Locations') }}' },
    colorAxis: { min: 0,  minColor: '#ede9fe', maxColor: '#675dff' },
    tooltip: {
        formatter: function () {
            const label = this.point.name || this.key || '{{ __('Unknown') }}';
            const value = this.point.value?.toLocaleString?.() ?? '0';

            return `<div class="d-flex gap-8 justify-content-between align-items-center" style="padding: 4px 12px;">
                <span class="fs-12"><span style="color: ${this.point.color};">●</span> ${label}:</span>
                <span class="fs-12 fw-6">${value}</span>
            </div>`;
        }
    }
});
</script>
@endsection