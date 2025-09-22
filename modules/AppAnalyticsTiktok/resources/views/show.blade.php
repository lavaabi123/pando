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
                    <div class="text-muted small mb-1">{{ $analytics['account']['username'] ?? 'Unknown Category' }}</div>
                    <a href="{{ $account->url }}" class="small text-gray-600" target="_blank">{{ $account->url }}</a>
                </div>
                <div class="d-flex justify-content-center align-items-center gap-16">
                    <div class="fw-bold fs-16">{{ number_format($analytics['account']['followers_count'] ?? 0) }} {{ __('Followers') }}</div>
                    <div class="px-1 text-gray-500">|</div>
                    <div class="fw-bold fs-16">{{ number_format($analytics['account']['video_count'] ?? 0) }} {{ __('Videos') }}</div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="container py-4">

    <form class="auto-submit" action="{{ url()->current(); }}" method="GET">
        <div class="d-flex justify-content-end gap-8">
            <a  href="{{ route('analytics.export.pdf', [ 'social' => request()->segment(3), 'id_secure' => request()->segment(4) ]) }}" class="btn btn-dark exportPDF">{{ __("Export PDF") }}</a>
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
                'likes' => 'fa-light fa-thumbs-up', 
                'engagement_rate' => 'fa-light fa-handshake',
                'shares' => 'fa-light fa-repeat', 
                'comments' => 'fa-light fa-comment',
                'views' => 'fa-light fa-binoculars', 
                'published_videos' => 'fa-light fa-paper-plane',
            ];
            $colors = [
                'likes' => 'primary', 
                'engagement_rate' => 'success',
                'comments' => 'warning', 
                'shares' => 'danger',
                'views' => 'dark', 
                'published_videos' => 'pink',
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
                            <div class="fw-bold fs-30 mb-3">{{ $key == 'engagement_rate'? number_format($item['value'], 2) : number_format($item['value']) }}</div>
                            <div class="small {{ $changeClass }}">
                                <i class="{{ $change >= 0 ? 'fa-light fa-arrow-trend-up' : 'fa-light fa-arrow-trend-down' }}"></i> {{ $changeLabel }} {{ __('vs last period') }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach

        @php $summary = $analytics['followerCountTrend']['summary']; @endphp
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Views') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="followerCountTrend" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-13 mb-2">{{ __('Total Followers') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">
                            {{ number_format($summary['end']) }}
                        </div>
                    </div>
                    <div class="flex-fill px-4 py-3">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Net Followers') }}</div>
                        <div class="fs-25 fw-bold {{ $summary['change'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $summary['change'] >= 0 ? '+' : '' }}{{ number_format($summary['change']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['trendChartData']['summary']; @endphp
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Engagement') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="trendChart" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-13 mb-2">{{ __('Total Likes') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">
                            {{ number_format($summary['total_likes']) }}
                        </div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-13 mb-2">{{ __('Total Comments') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">
                            {{ number_format($summary['total_comments']) }}
                        </div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-13 mb-2">{{ __('Total Shares') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">
                            {{ number_format($summary['total_shares']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>


        @php $summary = $analytics['viewTrendChartData']['summary']; @endphp
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Views') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="viewTrendChart" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-13 mb-2">{{ __('Total Views') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">
                            {{ number_format($summary['total_views']) }}
                        </div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-13 mb-2">{{ __('Avg. Views per Day') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">
                            {{ number_format($summary['avg_views_per_day']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['engagementRateTrend']['summary']; @endphp
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Engagement Rate') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="engagementRateTrend" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-13 mb-2">{{ __('Avg. Engagement Rate') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">
                            {{ number_format($summary['avg_engagement_rate'], 2) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['averageViewsPerVideoTrend']['summary']; @endphp
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Average Views per Video') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="averageViewsPerVideoTrend" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-13 mb-2">{{ __('Total Videos') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">
                            {{ number_format($summary['total_videos']) }}
                        </div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-13 mb-2">{{ __('Total Views') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">
                            {{ number_format($summary['total_views']) }}
                        </div>
                    </div>
                    <div class="flex-fill px-4 py-3">
                        <div class="text-gray-500 fs-13 mb-2">{{ __('Avg. Views per Day') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">
                            {{ number_format($summary['avg_views_per_video'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-12">
            @php $posts = $analytics['postHistoryList']; @endphp
            <div class="card px-0">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Post History') }}</h5>
                    <div class="card-tooltip">
                        <div class="d-flex flex-wrap gap-8">
                            <div class="d-flex">
                                <div class="form-control form-control-sm">
                                    <button class="btn btn-icon">
                                        <i class="fa-duotone fa-magnifying-glass"></i>
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
                                    <th style="width: 60px;">{{ __('Thumbnail') }}</th>
                                    <th>{{ __('Caption') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Views') }}</th>
                                    <th>{{ __('Total Interactions') }}</th>
                                    <th>{{ __('Likes') }}</th>
                                    <th>{{ __('Comments') }}</th>
                                    <th>{{ __('Shares') }}</th>
                                    <th>{{ __('ER%') }}</th>
                                    <th>{{ __('View') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($posts as $post)
                                    <tr>
                                        {{-- Thumbnail --}}
                                        <td class="text-center">
                                            @if (!empty($post['media_url']))
                                                <img src="{{ Media::url($post['media_url']) }}" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                            @else
                                                <div class="d-flex align-items-center justify-content-center bg-light border rounded" style="width: 48px; height: 48px;">
                                                    <i class="fa-light fa-video text-gray-600 fs-4"></i>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Caption --}}
                                        <td>{{ \Str::limit($post['message'] ?? '-', 80) }}</td>

                                        {{-- Date --}}
                                        <td class="text-nowrap text-gray-700 fs-14">
                                            {{ \Carbon\Carbon::parse($post['created_time'])->format('M d, Y') }}
                                        </td>

                                        {{-- Metrics --}}
                                        <td class="text-center text-info">{{ number_format($post['views'] ?? 0) }}</td>
                                        <td class="text-center text-primary">
                                            {{ number_format(($post['likes'] ?? 0) + ($post['comments'] ?? 0) + ($post['shares'] ?? 0)) }}
                                        </td>
                                        <td class="text-center text-success">{{ number_format($post['likes'] ?? 0) }}</td>
                                        <td class="text-center text-danger">{{ number_format($post['comments'] ?? 0) }}</td>
                                        <td class="text-center text-warning">{{ number_format($post['shares'] ?? 0) }}</td>
                                        <td class="text-center text-dark">{{ number_format($post['engagement_rate'] ?? 0, 2) }}%</td>

                                        {{-- View TikTok link --}}
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

    </div>

</div>
@endsection

@section('script')
<script type="text/javascript">
var trendChartData = {!! json_encode($analytics['trendChartData']) !!};
trendChartData.series[0].color = '#675dff';
trendChartData.series[1].color = '#13c2c2';
trendChartData.series[2].color = '#ffa940';
Main.Chart("mix", trendChartData.series, 'trendChart', {
    chart: { zoomType: 'xy' },
    title: { text: '{{ __("Interactions") }}' },
    xAxis: {
        categories: trendChartData.categories,
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
    yAxis: [
        {
            title: { text: '' },
            labels: { format: '{value}' }
        },
        {
            title: { text: '' },
            labels: { format: '{value}%' },
            opposite: true
        }
    ],
    tooltip: {
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: { dataLabels: { enabled: false } },
        spline: {
            lineWidth: 2,
            marker: { enabled: false }
        }
    }
});

var viewTrendChartData = {!! json_encode($analytics['viewTrendChartData']) !!};
Main.Chart('areaspline', viewTrendChartData.series, 'viewTrendChart', {
    title: { text: '{{ __("Views") }}' },
    xAxis: {
        categories: viewTrendChartData.categories,
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
    title: { text: '{{ __("view") }}' },
    legend: { enabled: false },
    plotOptions: {
        areaspline: {
            fillOpacity: 0.1,
            lineWidth: 3,
            marker: { enabled: false }
        },
        series: {
            color: '#675dff',
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

var engagementRateTrend = {!! json_encode($analytics['engagementRateTrend']) !!};
Main.Chart('areaspline', engagementRateTrend.series, 'engagementRateTrend', {
    title: { text: '{{ __("Engagement Rate") }}' },
    xAxis: {
        categories: engagementRateTrend.categories,
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
    title: { text: '{{ __("view") }}' },
    legend: { enabled: false },
    plotOptions: {
        areaspline: {
            fillOpacity: 0.1,
            lineWidth: 3,
            marker: { enabled: false }
        },
        series: {
            color: '#675dff',
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

var followerCountTrend = {!! json_encode($analytics['followerCountTrend']) !!};
Main.Chart('column', followerCountTrend.series, 'followerCountTrend', {
    title: { text: '{{ __("Followers") }}' },
    xAxis: {
        categories: followerCountTrend.categories,
        lineColor: '#ddd',
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
        gridLineDashStyle: 'Dash'
    },
    legend: { enabled: false },
    tooltip: {
        shared: true,
        valueSuffix: ' {{ __('view') }}'
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
    }
});

var averageViewsPerVideoTrend = {!! json_encode($analytics['averageViewsPerVideoTrend']) !!};
Main.Chart('column', averageViewsPerVideoTrend.series, 'averageViewsPerVideoTrend', {
    title: { text: '{{ __("Average Views Per Video") }}' },
    xAxis: {
        categories: averageViewsPerVideoTrend.categories,
        lineColor: '#ddd',
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
        gridLineDashStyle: 'Dash'
    },
    legend: { enabled: false },
    tooltip: {
        shared: true,
        valueSuffix: ' {{ __('view') }}'
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
    }
});
</script>
@endsection