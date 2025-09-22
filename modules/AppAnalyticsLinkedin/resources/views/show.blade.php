@extends('layouts.app')

@section('content')

@php
    $module = Module::find($account->module);
    $moduleInfo = $module->get('menu');
@endphp
<div class="border-bottom mb-5 pt-5 pb-4 bg-polygon">
    <div class="container">
        <div class="d-flex justify-content-center text-center">
            <div class="mb-0">
                <div class="size-80 size-chill position-relative mx-auto mb-3">
                    <img class="rounded-circle border mb-3 wp-100 hp-100" src="{{ Media::url($account->avatar) }}">
                    <div class="fs-12 position-absolute b-0 r-0 size-22 bg-white border b-r-100"><i class="{{ $moduleInfo['icon'] }}" style="color: {{ $moduleInfo['color'] }};"></i></div>
                </div>
                <div class="flex-fill mb-3">
                    <h4 class="mb-1 fs-20 fw-bold">{{ $account->name }}</h4>
                    <a href="{{ $account->url }}" class="small text-gray-600" target="_blank">{{ $account->url }}</a>
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
                'followers' => 'fa-light fa-users',
                'reach' => 'fa-light fa-eye',
                'impressions' => 'fa-light fa-repeat',
                'engagement' => 'fa-light fa-comment',
                'page_views' => 'fa-light fa-binoculars',
                'published_posts' => 'fa-light fa-paper-plane',
            ];
            $colors = [
                'likes' => 'primary',
                'followers' => 'info',
                'reach' => 'success',
                'impressions' => 'warning',
                'engagement' => 'danger',
                'page_views' => 'dark',
                'published_posts' => 'pink',
            ];
        @endphp

        @foreach ($overview as $key => $item)
            @php
                $icon = $icons[$key] ?? 'fa-light fa-chart-line';
                $color = $colors[$key] ?? 'secondary';
                $value = $item['value'] ?? 0;
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
                            <div class="fw-bold fs-30 mb-3">{{ number_format($value) }}</div>
                            <div class="small {{ $changeClass }}">
                                <i class="{{ $change >= 0 ? 'fa-light fa-arrow-trend-up' : 'fa-light fa-arrow-trend-down' }}"></i>
                                {{ $changeLabel }} {{ __('vs last period') }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach

        @php $summary = $analytics['dailyAllPageViewsChart']['summary']; @endphp
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Page Views') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="page-views-chart" class="export-chart" style="height: 300px;"></div>
                </div>
                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total page views') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['sectionPageViewsChartData']['summary']; @endphp
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Page Views by Section') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="linkedinSectionViewsChart" class="export-chart" style="height: 300px;"></div>
                </div>

                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Overview') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['overview']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Jobs') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['jobs']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Products') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['products']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('About') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['about']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['devicePageViewsChartData']['summary']; @endphp
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Page Views by Device') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="device-view-pie-chart" class="export-chart" style="height: 300px;"></div>
                </div>

                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Desktop') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['desktop']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Mobile') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['mobile']) }}</div>
                    </div>
                </div>
            </div>
        </div>

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

        @php $summary = $analytics['postCountChart']['summary']; @endphp
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Post Count by Day') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="linkedin-post-count-chart" class="export-chart" style="height: 300px;"></div>
                </div>

                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Post') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['total']) }}</div>
                    </div>
                   
                </div>
            </div>
        </div>

        @php $summary = $analytics['reachChartData']['summary']; @endphp
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Post Reach') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="reachChart" class="export-chart" style="height: 300px;"></div>
                </div>

                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Reach') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['postImpressionAndEngagementChartData']['summary']; @endphp
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Post Impressions, Engagement & Rate') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="postImpressionAndEngagementChart" class="export-chart" style="height: 300px;"></div>
                </div>

                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Impressions') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['total_impressions']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Engagement') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['total_engagement']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Engagement Rate') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['engagement_rate']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['interactionBreakdownChartData']['summary']; @endphp
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Interactions by Type') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="interactionBreakdownChart" class="export-chart" style="height: 300px;"></div>
                </div>

                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Like') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['like_count']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Comment') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['comment_count']) }}</div>
                    </div>
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Share') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['share_count']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $summary = $analytics['clickCountChartData']['summary']; @endphp
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="fs-5 fs-16">{{ __('Post Click Count by Day') }}</h5>
                </div>
                <div class="card-body border-bottom">
                    <div id="clickCountChart" class="export-chart" style="height: 300px;"></div>
                </div>

                <div class="d-flex card-body p-0">
                    <div class="flex-fill px-4 py-3 border-end">
                        <div class="text-gray-500 fs-14 mb-2">{{ __('Total Click') }}</div>
                        <div class="text-gray-800 fs-25 fw-bold">{{ number_format($summary['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
var reachChartData = {!! json_encode($analytics['reachChartData']) !!};
Main.Chart('column', reachChartData.series, 'reachChart', {
    title: { text: '{{ __("Post Reach") }}' },
    xAxis: {
        categories: reachChartData.categories,
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
        valueSuffix: '{{ __("Reach") }}'
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

var postImpressionAndEngagementChartData = {!! json_encode($analytics['postImpressionAndEngagementChartData']) !!};
postImpressionAndEngagementChartData.series[0].color = '#675dff';
postImpressionAndEngagementChartData.series[1].color = '#ffa940';
Main.Chart("mix", postImpressionAndEngagementChartData.series, 'postImpressionAndEngagementChart', {
    chart: { zoomType: 'xy' },
    title: { text: '{{ __("Post Impressions, Engagement & Rate") }}' },
    xAxis: {
        categories: postImpressionAndEngagementChartData.categories,
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
        line: {
            lineWidth: 2,
            marker: { enabled: true }
        }
    }
});

Main.Chart('line', {!! json_encode($analytics['postCountChart']['series']) !!}, 'linkedin-post-count-chart', {
    xAxis: {
        categories: {!! json_encode($analytics['postCountChart']['categories']) !!},
        crosshair: true,
        title: { text: null },
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
        allowDecimals: false
    },
    title: { text: '{{ __("Post Count by Day") }}' },
    tooltip: {
        shared: true,
        valueSuffix: ' posts'
    }
});

Main.Chart('pie', {!! json_encode($analytics['devicePageViewsChartData']['data']) !!}, 'device-view-pie-chart', {
    title: { text: '{{ __("Page Views by Device") }}' },
    legend: { enabled: true },
    tooltip: {
        pointFormat: '<b>{point.y} {{ __("views") }}</b> ({point.percentage:.1f}%)'
    },
    plotOptions: {
        pie: {
            showInLegend: true,
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.y} ({point.percentage:.1f}%)'
            }
        }
    }
});

var sectionPageViewsChartData = {!! json_encode($analytics['sectionPageViewsChartData']) !!};
sectionPageViewsChartData.series[0].color = '#675dff';
sectionPageViewsChartData.series[1].color = '#13c2c2';
sectionPageViewsChartData.series[2].color = '#ffa940';
sectionPageViewsChartData.series[3].color = '#f5222d';
Main.Chart('areaspline', sectionPageViewsChartData.series, 'linkedinSectionViewsChart', {
    xAxis: {
        categories: sectionPageViewsChartData.categories,
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


var dailyAllPageViewsChart = {!! json_encode($analytics['dailyAllPageViewsChart']) !!};
Main.Chart('areaspline', dailyAllPageViewsChart.series, 'page-views-chart', {
    xAxis: {
        categories: dailyAllPageViewsChart.categories,
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

var interactionBreakdownChartData = {!! json_encode($analytics['interactionBreakdownChartData']) !!};
interactionBreakdownChartData.series[0].color = '#675dff';
interactionBreakdownChartData.series[1].color = '#13c2c2';
interactionBreakdownChartData.series[2].color = '#ffa940';
Main.Chart('areaspline', interactionBreakdownChartData.series, 'interactionBreakdownChart', {
    xAxis: {
        categories: interactionBreakdownChartData.categories,
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

var clickCountChartData = {!! json_encode($analytics['clickCountChartData']) !!};
Main.Chart('column', clickCountChartData.series, 'clickCountChart', {
    title: { text: '{{ __("Post Click Count by Day") }}' },
    xAxis: {
        categories: clickCountChartData.categories,
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
        valueSuffix: '{{ __("Click") }}'
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