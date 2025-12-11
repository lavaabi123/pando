@if(Access::permission('apppublishing'))

@php
    $channels = Channels::channels();
    $teamId = request()->team_id;
    $now = \Carbon\Carbon::now();
    
    $daterange = request()->daterange;
    
    if ($daterange && strpos($daterange, ',') !== false) {
        list($startDateStr, $endDateStr) = explode(',', $daterange);
        $startDate = \Carbon\Carbon::parse($startDateStr)->startOfDay();
        $endDate = \Carbon\Carbon::parse($endDateStr)->endOfDay();
    } else {
        $startDate = request()->has('start_date') 
            ? \Carbon\Carbon::parse(request()->start_date)->startOfDay()
            : $now->copy()->subDays(27)->startOfDay();
            
        $endDate = request()->has('end_date')
            ? \Carbon\Carbon::parse(request()->end_date)->endOfDay()
            : $now->copy()->endOfDay();
    }

    $report = PublishingReport::postInfo($startDate, $endDate, $teamId ?? null);
    $reportStat = PublishingReport::postStatsGrowthInfo($startDate, $endDate, $teamId ?? null);
    $errorSuccessChart = PublishingReport::postStatsByDay($startDate, $endDate, $teamId ?? null);
    $errorSuccessSummary = $errorSuccessChart['summary'];
    $recentPosts = PublishingReport::recentPostsStatus(10, $teamId ?? null);
	$socialMediaStats = PublishingReport::postsBySocialMedia($startDate, $endDate, $teamId ?? null);

    $statusMap    = $reportStat['status_map'];
    $statusCounts = $reportStat['status_counts'];
    $statusGrowth = $reportStat['status_growth'];
    $totalPosts   = $reportStat['total_posts'];
    $totalGrowth  = $reportStat['total_growth'];
    $successTotal = $statusCounts[4] ?? 0;
    $failedTotal  = $statusCounts[5] ?? 0;

    $processingTotal = $report['status_counts'][3] ?? 0;
	$processingGrowth = $report['status_growth'][3] ?? 0;
	$processingLabel = $report['status_map'][3]['label'] ?? 'Processing';

    $successRate = ($successTotal + $failedTotal) > 0
        ? round($successTotal * 100 / ($successTotal + $failedTotal), 1)
        : 0;
    $quota = Publishing::checkQuota($teamId ?? null);
@endphp


<div class="gradient-bg main-services text-center my-4 p-4 b-r-20 justify-content-xl-evenly">
	<div class="p-4 d-flex gap-25 justify-content-xl-evenly">
		<a class="icons" href="{{ url_app('publishing') }}">
			<div class="mb-3" style="fill:var(--d-primary);">
				{!! file_get_contents(public_path('img/post.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Create Post
			</div>
		</a>
		<a class="icons" href="{{ url_app('channels') }}">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 75%,var(--d-secondary) 25%)">
				{!! file_get_contents(public_path('img/account.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Manage Accounts
			</div>
		</a>
		<a class="icons" href="{{ url_app('publishing') }}">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 60%,var(--d-secondary) 40%)">
				{!! file_get_contents(public_path('img/calender.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Calender
			</div>
		</a>
		<a class="icons" href="{{ url_app('analytics') }}">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 50%,var(--d-secondary) 50%)">
				{!! file_get_contents(public_path('img/Reports.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Reports
			</div>
		</a>
		<a class="icons" href="{{ url_app('inbox') }}">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 20%,var(--d-secondary) 80%)">
				{!! file_get_contents(public_path('img/inbox.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Inbox
			</div>
		</a>
		<a class="icons" href="">
			<div class="mb-3" style="fill:var(--d-secondary);">
				{!! file_get_contents(public_path('img/note.svg')) !!}
			</div>
			<div class="fw-6 text-black">
				Notes
			</div>
		</a>
	</div>
</div>

<div class="card d-alert position-relative overflow-hidden hp-100 mb-4">
    <div class="card-body py-4 px-4">
		<div class="text-black mb-3">
			<h5 class="fw-6">Daily Alerts!</h5>
			<p class="mb-0">Stay up to date on all your accounts!</p>
		</div>
		<div class="row row-gap-4">
			<div class="col-md-4">
				<a href="#">
				<div class="card p-3 d-flex flex-row align-items-center">
					<h3 class="count text-primary mb-0">0</h3>
					<h5 class="mb-0 fw-bold">Accounts with no scheduled posts today</h5>
				</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="#">
				<div class="card p-3 d-flex flex-row align-items-center">
					<h3 class="count text-primary mb-0">1</h3>
					<h5 class="mb-0 fw-bold">Accounts with inbox not cleared for more than 24 hours</h5>
				</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="#">
				<div class="card p-3 d-flex flex-row align-items-center">
					<h3 class="count text-primary mb-0">7</h3>
					<h5 class="mb-0 fw-bold">Posts pending approval</h5>
				</div>
				</a>
			</div>
		</div>
	</div>
</div>

<div class="card d-alert position-relative overflow-hidden hp-100 mb-4">
    <div class="card-body py-4 px-4">
		<div class="text-black mb-3">
			<h5 class="fw-6">Today! {{ date("F d, Y") }}</h5>
			<p class="mb-0">What’s happening on your accounts today!</p>
		</div>
		<div class="row row-gap-4">
			<div class="col-md-4">
				<a href="#">
				<div class="card p-3 d-flex flex-row align-items-center">
					<h3 class="count text-primary mb-0">{{ number_format($processingTotal) }}</h3>
					<h5 class="mb-0 fw-bold">Amount of total scheduled posts</h5>
				</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="#">
				<div class="card p-3 d-flex flex-row align-items-center">
					<h3 class="count text-primary mb-0">0</h3>
					<h5 class="mb-0 fw-bold">Inbox Messages</h5>
				</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="#">
				<div class="card p-3 d-flex flex-row align-items-center">
					<h3 class="count text-primary mb-0">0</h3>
					<h5 class="mb-0 fw-bold">Total Reviews</h5>
				</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="#">
				<div class="card p-3 d-flex flex-row align-items-center">
					<h3 class="count text-primary mb-0">4</h3>
					<h5 class="mb-0 fw-bold">Number of New People added</h5>
				</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="#">
				<div class="card p-3 d-flex flex-row align-items-center">
					<h3 class="count text-primary mb-0">{{number_format($failedTotal)}}</h3>
					<h5 class="mb-0 fw-bold">Total failed posts</h5>
				</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="#">
				<div class="card p-3 d-flex flex-row align-items-center">
					<h3 class="count text-primary mb-0">0</h3>
					<h5 class="mb-0 fw-bold">Holidays</h5>
				</div>
				</a>
			</div>
		</div>
	</div>
</div>

<div class="row row-cols-1 row-cols-md-4 g-4 mb-4">

    {{-- Quota --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 hp-100 min-h-350">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4 h-100">
                <div class="d-flex flex-column mb-3 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center b-r-12 size-50 bg-primary-100 border border-primary-200">
                        <i class="fa-light fa-gauge text-primary fs-22"></i>
                    </span>
                    <span class="fw-6 fs-20">{{ __('Post Quota') }}</span>
                </div>
                @if($quota['limit'] == -1)
                    <div class="fw-bold fs-2 mb-1 text-primary">{{ __('Unlimited') }}</div>
                    <div class="fs-15 text-muted">{{ $quota['message'] }}</div>
                @else
                    <div class="fw-bold fs-2 mb-1 text-dark">
                        {{ $quota['used'] }}/{{ $quota['limit'] }}
                        <span class="fs-12 text-muted">
                            ({{ __('left:') }} {{ $quota['left'] }})
                        </span>
                    </div>
                    <div class="w-100 mb-2">
                        <div class="progress h-7 bg-gray-200">
                            <div class="progress-bar
                                {{ $quota['left'] == 0 ? 'bg-danger' : ($quota['left'] <= 5 ? 'bg-warning' : 'bg-primary') }}"
                                role="progressbar"
                                style="width:{{ $quota['limit'] > 0 ? round($quota['used']/$quota['limit']*100) : 0 }}%;">
                            </div>
                        </div>
                    </div>
                    <div class="fs-15 {{ $quota['left'] == 0 ? 'text-danger' : ($quota['left'] < 5 ? 'text-warning' : 'text-muted') }}">
                        {{ $quota['message'] }}
                    </div>
                @endif
                <div class="mt-3 fw-6 text-dark fs-16">
                    {{ __('Total Posted:') }} <span class="fw-bold">{{ number_format($totalPosts) }}</span>
                </div>
                <div class="fs-14 text-muted">
                    {{ __('Compared to last :days days', ['days' => $startDate->diffInDays($endDate)]) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Add Channels --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <div class="fw-5">{{ __("Add Channels") }}</div>
            </div>
            <div class="card-body max-h-300 overflow-y-scroll">
                <div class="row">
                    @if( !empty( $channels ) )
                        @foreach( $channels as $channel )
                            <div class="col-md-6 mb-4">
                                <div class="card border-gray-300">
                                    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center gap-10">
                                        <div class="d-flex align-items-center justify-content-center size-50 text-white border-1 b-r-100 fs-16" style="background-color: {{ $channel['color'] }};">
                                            <i class="{{ $channel['icon'] }}"></i>
                                        </div>
                                        <div class="fs-14 fw-5">{{ __($channel['name']) }}</div>
                                        <div>
                                            @if( !empty( $channel ) && isset( $channel['items']  ) )
                                                @foreach( $channel['items'] as $item )
                                                    <a href="{{ url($item["uri"]) }}" class="btn btn-outline btn-sm btn-light mb-1"><i class="fa-light fa-plus"></i> {{ __( ucfirst( str_replace("_", " ", $item["category"]) ) ) }}</a>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>
        </div>
    </div>


	<div class="col-md-12 card position-relative overflow-hidden hp-100 mb-4">
		<div class="card-body d-flex align-items-center justify-content-between py-4 px-4">
			<div class="text-black">
				<h5 class="fw-6 d-flex icon-primary"><span>{!! file_get_contents(public_path('img/Reports.svg')) !!}</span> Post Analytics</h5>
			</div>
			<div class="align-self-end">
				<select class="form-control">
					<option>All Platform</option>
					<option>Facebook</option>
				</select>
			</div>
			<div class="">
                <div></div>
                <div class="d-flex align-items-center justify-content-between gap-8">
                    <div>
                        <div class="daterange d-none bg-white b-r-4 fs-12 border-gray-300 border" data-open="left"></div>
                    </div>
                </div>
            </div>
		</div>
	</div>
    {{-- Success --}}
    <div class="col">
        <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-success-100 border border-success-200">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
                <div class="d-flex align-items-center mb-2 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-success-500">
                        <i class="fa-light fa-circle-check text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted">{{ $statusMap[4]['label'] ?? __('Success') }}</span>
                </div>
                <div class="fw-bold fs-2 mb-1 text-dark">{{ number_format($successTotal) }}</div>
                <div class="fs-14 text-muted">
                    {{ ($statusGrowth[4] ?? 0) == 0 ? '0%' : (($statusGrowth[4] ?? 0) > 0 ? '+' : '-') . abs($statusGrowth[4] ?? 0) . '%' }}
                </div>
            </div>
        </div>
    </div>
    {{-- Failed --}}
    <div class="col">
        <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-danger-100 border border-danger-200">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
                <div class="d-flex align-items-center mb-2 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-danger-500">
                        <i class="fa-light fa-circle-xmark text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted">{{ $statusMap[5]['label'] ?? __('Failed') }}</span>
                </div>
                <div class="fw-bold fs-2 mb-1 text-dark">{{ number_format($failedTotal) }}</div>
                <div class="fs-14 text-muted">
                    {{ ($statusGrowth[5] ?? 0) == 0 ? '0%' : (($statusGrowth[5] ?? 0) > 0 ? '+' : '-') . abs($statusGrowth[5] ?? 0) . '%' }}
                </div>
            </div>
        </div>
    </div>
    {{-- Success Rate --}}
    <div class="col">
        <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-primary-100 border border-primary-200">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
                <div class="d-flex align-items-center mb-2 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-primary-500">
                        <i class="fa-light fa-badge-check text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted">{{ __('Success Rate') }}</span>
                </div>
                <div class="fw-bold fs-2 mb-1 text-primary">{{ $successRate }}%</div>
                <div class="fs-14 text-muted">{{ __('of processed posts') }}</div>
            </div>
        </div>
    </div>

    {{-- Processing --}}
	<div class="col">
	    <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-teal-100 border border-teal-200">
	        <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
	            <div class="d-flex align-items-center mb-2 gap-12">
	                <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-teal-500">
	                    <i class="fa-light fa-arrows-rotate text-white fs-22"></i>
	                </span>
	                <span class="fw-6 fs-14 text-muted">{{ $processingLabel }}</span>
	            </div>
	            <div class="fw-bold fs-2 mb-1 text-dark">{{ number_format($processingTotal) }}</div>
	            <div class="fs-14 text-muted">
	                {{ $processingGrowth == 0 ? '0%' : ($processingGrowth > 0 ? '+' : '-') . abs($processingGrowth) . '%' }}
	            </div>
	        </div>
	    </div>
	</div>
{{-- Three Mini Charts Section --}}
<div class="col-md-12 mb-4">
    <div class="row g-3">
        {{-- Success Chart --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-success-100" style="width: 48px; height: 48px;">
                            <i class="fa-light fa-circle-check text-success fs-20"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h2 class="fw-bold text-success mb-0">{{ number_format($successTotal) }}</h2>
                            <div class="text-muted fs-14">Succeed</div>
                            <div class="text-muted fs-12">{{ $startDate->format('m/d/Y') }} - {{ $endDate->format('m/d/Y') }}</div>
                        </div>
                    </div>
                    <div id="success-mini-chart" style="height: 120px;"></div>
                </div>
            </div>
        </div>

        {{-- Failed Chart --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-danger-100" style="width: 48px; height: 48px;">
                            <i class="fa-light fa-circle-xmark text-danger fs-20"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h2 class="fw-bold text-danger mb-0">{{ number_format($failedTotal) }}</h2>
                            <div class="text-muted fs-14">Failed</div>
                            <div class="text-muted fs-12">{{ $startDate->format('m/d/Y') }} - {{ $endDate->format('m/d/Y') }}</div>
                        </div>
                    </div>
                    <div id="failed-mini-chart" style="height: 120px;"></div>
                </div>
            </div>
        </div>

        {{-- Total Chart --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-success-100" style="width: 48px; height: 48px;">
                            <i class="fa-light fa-calendar-check text-success fs-20"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h2 class="fw-bold text-success mb-0">{{ number_format($totalPosts) }}</h2>
                            <div class="text-muted fs-14">Total</div>
                            <div class="text-muted fs-12">{{ $startDate->format('m/d/Y') }} - {{ $endDate->format('m/d/Y') }}</div>
                        </div>
                    </div>
                    <div id="total-mini-chart" style="height: 120px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Chart Section (Report post by status) --}}
<div class="col-md-12">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="fs-5 fs-16 mb-0">{{ __('Report post by status') }}</h5>
        </div>
        <div class="card-body">
            <div id="posts-error-success-chart" style="height: 400px;"></div>
        </div>
        <div class="d-flex card-body p-0 border-top">
            <div class="flex-fill px-4 py-3 border-end">
                <div class="text-gray-500 fs-14 mb-2">{{ __('Success') }}</div>
                <div class="text-gray-800 fs-25 fw-bold">{{ number_format($errorSuccessSummary['success_total']) }}</div>
            </div>
            <div class="flex-fill px-4 py-3 border-end">
                <div class="text-gray-500 fs-14 mb-2">{{ __('Error') }}</div>
                <div class="text-gray-800 fs-25 fw-bold">{{ number_format($errorSuccessSummary['fail_total']) }}</div>
            </div>
            <div class="flex-fill px-4 py-3">
                <div class="text-gray-500 fs-14 mb-2">{{ __('Success Rate') }}</div>
                <div class="text-gray-800 fs-25 fw-bold">
                    {{ $errorSuccessSummary['success_rate'] }}%
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Two Column Section: Pie Chart + Recent Publications --}}
<div class="col-md-12 mb-4">
    <div class="row g-4">
        {{-- Left Column: Pie Chart (Report post by type) --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="fs-5 fs-16 mb-0">{{ __('Report post by social media') }}</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div id="social-media-pie-chart" style="height: 400px; width: 100%;"></div>
                </div>
                <div class="card-footer bg-white border-top">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th class="text-muted fs-13">{{ __('Social Media') }}</th>
                                    <th class="text-muted fs-13 text-end">{{ __('Total Posts') }}</th>
                                </tr>
                            </thead>
                            <tbody id="social-media-stats">
                                @foreach($socialMediaStats as $stat)
                                <tr>
                                    <td>
                                        <span class="d-inline-block rounded-circle me-2" style="width: 10px; height: 10px; background-color: {{ $stat['color'] }};"></span>
                                        {{ $stat['name'] }}
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($stat['y']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Recent Publications --}}
<div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-0">
            <h5 class="fs-5 fs-16 mb-0">{{ __('Recent publications') }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="schedules-main overflow-auto" style="max-height: 600px;">
                <div class="schedule-list p-3">
                    @forelse($recentPosts as $post)
                        @php
                            $data = json_decode($post->data);
                            $result = json_decode($post->result);
                        @endphp
                        
                        <div class="card border mb-3 position-relative overflow-hidden">
                            {{-- Status Ribbon --}}
                            @if($post->status == 4)
                                <div class="position-absolute top-0 end-0 m-2" style="z-index: 10;">
                                    <span class="badge bg-success">
                                        <i class="fa-solid fa-check-double"></i>
                                    </span>
                                </div>
                            @elseif($post->status == 5)
                                <div class="position-absolute top-0 end-0 m-2" style="z-index: 10;">
                                    <span class="badge bg-danger">
                                        <i class="fa-solid fa-exclamation-circle"></i>
                                    </span>
                                </div>
                            @endif

                            {{-- Card Header: Multiple Accounts --}}
                            <div class="card-header border-0 bg-white p-3">
                                @if(count($post->accounts) > 0)
                                    {{-- Show first 2-3 accounts with avatars --}}
                                    <div class="d-flex align-items-center">
                                        {{-- Account Avatars Stack --}}
                                        <div class="d-flex me-2" style="margin-right: -8px;">
                                            @foreach(array_slice($post->accounts, 0, 4) as $index => $account)
                                                <div class="position-relative">
                                                    <img src="{{ Media::url($account['avatar']) }}" 
                                                         class="rounded-circle border border-2 border-white bg-white" 
                                                         style="width: 32px; height: 32px; object-fit: cover;" 
                                                         alt="{{ $account['name'] }}"
                                                         title="{{ $account['name'] }}">
                                                    {{-- Social Media Icon Badge --}}
                                                    <span class="position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center" 
                                                          style="width: 14px; height: 14px; background-color: {{ $account['color'] }}; border: 2px solid white;">
                                                        {{ get_social_media_image($account['social_network']) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                            
                                            {{-- Show +N indicator if more than 4 accounts --}}
                                            @if(count($post->accounts) > 4)
                                                <div class="rounded-circle border border-2 border-white bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 32px; height: 32px; margin-left: -12px; z-index: 1;">
                                                    <span class="fs-10 fw-bold text-muted">+{{ count($post->accounts) - 4 }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Account Names and Time --}}
                                        <div class="flex-grow-1 ms-2">
                                            <!--<div class="fw-bold fs-13">
                                                @foreach(array_slice($post->accounts, 0, 2) as $index => $account)
                                                    @if($index > 0), @endif
                                                    {{ get_social_media_image($account['social_network']) }}
                                                    {{ $account['name'] }}
                                                @endforeach
                                                @if(count($post->accounts) > 2)
                                                    <span class="text-muted">+{{ count($post->accounts) - 2 }} more</span>
                                                @endif
                                            </div>-->
                                            <div class="text-muted fs-11">
                                                <i class="fa-light fa-calendar"></i>
                                                {{ \Carbon\Carbon::createFromTimestamp($post->time_post)->format('m/d/Y g:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Card Body: Content --}}
                            <div class="card-body p-3 pt-0">
                                <div class="d-flex gap-3">
                                    {{-- Media Thumbnail --}}
                                    <div class="flex-shrink-0">
                                        @if($post->type == 'media' && !empty($post->medias))
                                            @php 
                                                $firstMedia = is_array($post->medias) ? ($post->medias[0] ?? null) : $post->medias;
                                            @endphp
                                            @if($firstMedia)
                                                <div class="position-relative">
                                                    @if(str_contains($firstMedia, '.mp4') || str_contains($firstMedia, '.mov'))
                                                        <video class="rounded border" style="width: 80px; height: 80px; object-fit: cover;" muted>
                                                            <source src="{{ Media::url($firstMedia) }}" type="video/mp4">
                                                        </video>
                                                    @else
                                                        <img src="{{ Media::url($firstMedia) }}" 
                                                             class="rounded border" 
                                                             style="width: 80px; height: 80px; object-fit: cover;" 
                                                             alt="">
                                                    @endif
                                                    
                                                    @if(is_array($post->medias) && count($post->medias) > 1)
                                                        <div class="position-absolute top-0 end-0 badge bg-dark m-1" style="font-size: 9px;">
                                                            +{{ count($post->medias) - 1 }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @elseif($post->type == 'link')
                                            <div class="d-flex align-items-center justify-content-center rounded border bg-light" style="width: 80px; height: 80px;">
                                                <i class="fa-light fa-link text-primary fs-24"></i>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center justify-content-center rounded border bg-light" style="width: 80px; height: 80px;">
                                                <i class="fa-light fa-align-center text-primary fs-24"></i>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Caption --}}
                                    <div class="flex-grow-1">
                                        <div class="text-muted fs-13" style="max-height: 80px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical;">
                                            {{ $post->caption ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Card Footer: Status --}}
                            @if($post->status == 4)
                                <div class="card-footer bg-success bg-opacity-10 text-white border-0 py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-13">{{ __('Post Published') }}</span>
                                        @if(!empty($result->url))
                                            <a href="{{ $result->url }}" target="_blank" class="btn btn-sm btn-success">
                                                <i class="fa-light fa-eye"></i> {{ __('View post') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @elseif($post->status == 5)
                                <div class="card-footer bg-danger bg-opacity-10 text-white border-0 py-2 px-3">
                                    <div class="fs-13">
                                        <i class="fa-light fa-exclamation-triangle me-1"></i>
                                        {{ $result->message ?? __('Post failed to publish') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fa-light fa-inbox fs-48 text-muted opacity-50"></i>
                            </div>
                            <div class="text-muted mb-3">{{ __('No recent publications') }}</div>
                            <a href="{{ url_app('publishing') }}" class="btn btn-primary btn-sm">
                                <i class="fa-light fa-plus"></i> {{ __('Create post') }}
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
	</div>
</div>
</div>
<style>
/* Stacked avatars hover effect */
.schedule-list .position-relative:hover {
    z-index: 20 !important;
    transform: translateY(-2px);
    transition: all 0.2s ease;
}

/* Scrollbar styling */
.schedules-main::-webkit-scrollbar {
    width: 6px;
}

.schedules-main::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.schedules-main::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 10px;
}

.schedules-main::-webkit-scrollbar-thumb:hover {
    background: #999;
}

/* Card hover effect */
.schedule-list .card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

/* Badge sizing */
.fs-10 {
    font-size: 10px;
}

.fs-11 {
    font-size: 11px;
}
</style>
<script>
    // Social Media Pie Chart
    var socialMediaData = {!! json_encode($socialMediaStats) !!};
    
    Highcharts.chart('social-media-pie-chart', {
        chart: {
            type: 'pie',
            backgroundColor: 'transparent'
        },
        title: {
            text: null
        },
        tooltip: {
            pointFormat: '<b>{point.y}</b> posts ({point.percentage:.1f}%)',
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            borderColor: '#e8e8e8',
            borderRadius: 8,
            style: {
                fontSize: '12px'
            }
        },
        accessibility: {
            point: {
                valueSuffix: ' posts'
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b><br>{point.percentage:.1f}%',
                    distance: 15,
                    style: {
                        fontSize: '12px',
                        textOutline: 'none'
                    }
                },
                showInLegend: false,
                innerSize: '50%', // Makes it a donut chart
                borderWidth: 0,
                states: {
                    hover: {
                        brightness: 0.1
                    }
                }
            }
        },
        series: [{
            name: 'Posts',
            colorByPoint: true,
            data: socialMediaData
        }],
        credits: {
            enabled: false
        }
    });
</script>
<script>
    var errorSuccessChart = {!! json_encode($errorSuccessChart) !!};
    
    // Configure series for stacked column chart
    errorSuccessChart.series[0].color = '#52c41a'; // Green for success
    errorSuccessChart.series[0].name = 'Post succeed';
    errorSuccessChart.series[1].color = '#ff4d4f'; // Red for failed
    errorSuccessChart.series[1].name = 'Post failed';
    
    Main.Chart('column', errorSuccessChart.series, 'posts-error-success-chart', {
        chart: {
            type: 'column'
        },
        xAxis: {
            categories: errorSuccessChart.categories,
            title: { text: '' },
            crosshair: false,
            labels: {
                rotation: -45,
                style: {
                    fontSize: '11px'
                }
            }
        },
        yAxis: {
            min: 0,
            title: { text: 'Number of Posts' },
            stackLabels: {
                enabled: false
            },
            gridLineColor: '#f0f0f0',
            gridLineDashStyle: 'Dash'
        },
        legend: {
            enabled: true,
            align: 'center',
            verticalAlign: 'bottom',
            backgroundColor: 'white',
            borderColor: '#CCC',
            borderWidth: 0,
            shadow: false
        },
        tooltip: {
            headerFormat: '<b>{point.x}</b><br/>',
            pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: false
                },
                borderWidth: 0,
                pointPadding: 0.1,
                groupPadding: 0.1
            }
        }
    });
	// Simplified Mini Charts
    function createSparkline(containerId, data, color) {
        Highcharts.chart(containerId, {
            chart: {
                type: 'areaspline',
                backgroundColor: 'transparent',
                height: 80,
                margin: [5, 0, 5, 0],
                spacing: [0, 0, 0, 0]
            },
            title: { text: null },
            xAxis: { visible: false },
            yAxis: { 
                visible: false,
                min: 0,
                endOnTick: false,
                startOnTick: false
            },
            legend: { enabled: false },
            tooltip: { enabled: false },
            plotOptions: {
                areaspline: {
                    fillColor: {
                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                        stops: [
                            [0, color + '40'],
                            [1, color + '00']
                        ]
                    },
                    lineWidth: 2,
                    lineColor: color,
                    marker: { enabled: false },
                    states: { hover: { enabled: false } },
                    enableMouseTracking: false
                }
            },
            series: [{
                data: data
            }],
            credits: { enabled: false }
        });
    }

    // Create sparklines
    createSparkline('success-mini-chart', errorSuccessChart.series[0].data, '#52c41a');
    createSparkline('failed-mini-chart', errorSuccessChart.series[1].data, '#ff4d4f');
    
    var totalData = errorSuccessChart.series[0].data.map(function(val, idx) {
        return val + errorSuccessChart.series[1].data[idx];
    });
    createSparkline('total-mini-chart', totalData, '#1890ff');
</script>
<script>
    var errorSuccessChart = {!! json_encode($errorSuccessChart) !!};
    
    // Format dates for display
    var formattedCategories = errorSuccessChart.categories.map(function(dateStr) {
        var date = new Date(dateStr);
        var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                         'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var month = monthNames[date.getMonth()];
        var day = date.getDate();
        return month + ' ' + day;
    });
    
    // Configure main chart colors
    errorSuccessChart.series[0].color = '#52c41a';
    errorSuccessChart.series[0].name = 'Post succeed';
    errorSuccessChart.series[1].color = '#ff4d4f';
    errorSuccessChart.series[1].name = 'Post failed';
    
    // Main Chart (Report post by status)
    Main.Chart('column', errorSuccessChart.series, 'posts-error-success-chart', {
        chart: {
            type: 'column',
            backgroundColor: 'transparent'
        },
        title: {
            text: null
        },
        xAxis: {
            categories: formattedCategories,
            crosshair: false,
            labels: {
                rotation: -45,
                align: 'right',
                style: {
                    fontSize: '11px',
                    color: '#666'
                }
            },
            lineColor: '#e8e8e8'
        },
        yAxis: {
            min: 0,
            title: {
                text: null
            },
            stackLabels: {
                enabled: false
            },
            gridLineColor: '#f5f5f5',
            gridLineDashStyle: 'Dash',
            labels: {
                style: {
                    color: '#666'
                }
            }
        },
        legend: {
            enabled: true,
            align: 'center',
            verticalAlign: 'bottom',
            layout: 'horizontal',
            itemStyle: {
                fontSize: '13px',
                fontWeight: 'normal',
                color: '#666'
            },
            symbolHeight: 12,
            symbolWidth: 12,
            symbolRadius: 2,
            itemMarginBottom: 5
        },
        tooltip: {
            headerFormat: '<b>{point.x}</b><br/>',
            pointFormat: '<span style="color:{series.color}">\u25CF</span> {series.name}: <b>{point.y}</b><br/>',
            shared: true,
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            borderColor: '#e8e8e8',
            borderRadius: 8,
            shadow: true
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                borderWidth: 0,
                pointPadding: 0.1,
                groupPadding: 0.1,
                borderRadius: 4,
                dataLabels: {
                    enabled: false
                },
                states: {
                    hover: {
                        brightness: 0.1
                    }
                }
            }
        },
        credits: {
            enabled: false
        }
    });

    // Mini Chart with Axes
    function createMiniChartWithAxes(containerId, data, color, categories) {
        // Calculate max value for better y-axis scaling
        var maxValue = Math.max.apply(null, data);
        var yAxisMax = maxValue > 0 ? Math.ceil(maxValue * 1.2) : 10;
        
        Main.Chart('areaspline', [{
            name: 'Posts',
            data: data,
            color: color
        }], containerId, {
            chart: {
                backgroundColor: 'transparent',
                height: 120,
                spacingTop: 10,
                spacingRight: 10,
                spacingBottom: 5,
                spacingLeft: 5
            },
            title: {
                text: null
            },
            xAxis: {
                categories: categories,
                labels: {
                    enabled: true,
                    rotation: 0,
                    step: Math.ceil(categories.length / 8), // Show every nth label
                    style: {
                        fontSize: '9px',
                        color: '#999'
                    }
                },
                lineColor: '#e8e8e8',
                tickLength: 0
            },
            yAxis: {
                min: 0,
                max: yAxisMax,
                title: {
                    text: null
                },
                labels: {
                    enabled: true,
                    style: {
                        fontSize: '9px',
                        color: '#999'
                    }
                },
                gridLineColor: '#f5f5f5',
                gridLineDashStyle: 'Dash'
            },
            legend: {
                enabled: false
            },
            tooltip: {
                enabled: true,
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: 'Posts: <b>{point.y}</b>',
                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                borderColor: color,
                borderRadius: 4,
                style: {
                    fontSize: '11px'
                }
            },
            plotOptions: {
                areaspline: {
                    fillOpacity: 0.2,
                    lineWidth: 2,
                    marker: {
                        enabled: false,
                        states: {
                            hover: {
                                enabled: true,
                                radius: 4
                            }
                        }
                    }
                },
                series: {
                    fillColor: {
                        linearGradient: [0, 0, 0, 120],
                        stops: [
                            [0, color + '40'],
                            [1, color + '00']
                        ]
                    }
                }
            },
            credits: {
                enabled: false
            }
        });
    }

    // Create Mini Charts with axes
    createMiniChartWithAxes('success-mini-chart', errorSuccessChart.series[0].data, '#52c41a', formattedCategories);
    createMiniChartWithAxes('failed-mini-chart', errorSuccessChart.series[1].data, '#ff4d4f', formattedCategories);
    
    // Total chart (sum of success + failed)
    var totalData = errorSuccessChart.series[0].data.map(function(val, idx) {
        return val + errorSuccessChart.series[1].data[idx];
    });
    createMiniChartWithAxes('total-mini-chart', totalData, '#52c41a', formattedCategories);
</script>
{{-- Initialize daterange after content is loaded --}}
<script>
(function() {
    console.log('Dashboard item loaded, initializing daterange...');
    
    // Wait for DOM to be ready
    setTimeout(function() {
        if (typeof Main !== 'undefined' && typeof Main.dateRange === 'function') {
            console.log('Calling Main.dateRange()');
            Main.dateRange();
            
            // Setup date change handler
            setupDateChangeHandler();
        } else {
            console.error('Main.dateRange() not available');
        }
    }, 100);
})();
</script>
@endif