<?php if(Access::permission('apppublishing')): ?>

<?php
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
	
	// Get daily alerts (independent of date range)
    $dailyAlerts = PublishingReport::getDailyAlerts($teamId ?? null);
    
    // Get today counts (independent of date range)
    $todayCounts = PublishingReport::getTodayCounts('daily', $teamId ?? null);

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
?>


<div class="gradient-bg main-services text-center my-4 p-4 b-r-20 justify-content-xl-evenly">
	<div class="p-4 d-flex gap-20 justify-content-xl-evenly">
		<a class="icons" href="<?php echo e(url_app('publishing/composer')); ?>">
			<div class="mb-3" style="fill:var(--d-primary);">
				<?php echo file_get_contents(public_path('img/post.svg')); ?>

			</div>
			<div class="fw-6 text-black">
				Create Post
			</div>
		</a>
		<a class="icons" href="<?php echo e(url_app('channels')); ?>">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 75%,var(--d-secondary) 25%)">
				<?php echo file_get_contents(public_path('img/account.svg')); ?>

			</div>
			<div class="fw-6 text-black">
				Manage Accounts
			</div>
		</a>
		<a class="icons" href="<?php echo e(url_app('publishing')); ?>">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 60%,var(--d-secondary) 40%)">
				<?php echo file_get_contents(public_path('img/calender.svg')); ?>

			</div>
			<div class="fw-6 text-black">
				Calender
			</div>
		</a>
		<a class="icons" href="<?php echo e(url_app('analytics')); ?>">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 50%,var(--d-secondary) 50%)">
				<?php echo file_get_contents(public_path('img/Reports.svg')); ?>

			</div>
			<div class="fw-6 text-black">
				Reports
			</div>
		</a>
		<a class="icons" href="<?php echo e(url_app('inbox')); ?>">
			<div class="mb-3" style="fill:color-mix(in srgb,var(--d-primary) 20%,var(--d-secondary) 80%)">
				<?php echo file_get_contents(public_path('img/inbox.svg')); ?>

			</div>
			<div class="fw-6 text-black">
				Inbox
			</div>
		</a>
		<a class="icons" href="">
			<div class="mb-3" style="fill:var(--d-secondary);">
				<?php echo file_get_contents(public_path('img/note.svg')); ?>

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
            <h5 class="fw-6"><?php echo e(__('Daily Alerts!')); ?></h5>
            <p class="mb-0"><?php echo e(__('Stay up to date on all your accounts!')); ?></p>
        </div>
        <div class="row row-gap-4">
            
            <div class="col-md-4">
                <a href="<?php echo e(url_app('publishing')); ?>">
                    <div class="card p-3 d-flex flex-row align-items-center">
                        <h3 class="count text-primary mb-0"><?php echo e($dailyAlerts['no_scheduled_posts_count']); ?></h3>
                        <h5 class="mb-0 fw-bold"><?php echo e(__('Accounts with no scheduled posts today')); ?></h5>
                    </div>
                </a>
            </div>
            
            
            <div class="col-md-4">
                <a href="<?php echo e(url_app('inbox')); ?>">
                    <div class="card p-3 d-flex flex-row align-items-center">
                        <h3 class="count text-primary mb-0"><?php echo e($dailyAlerts['inbox_not_cleared_count']); ?></h3>
                        <h5 class="mb-0 fw-bold"><?php echo e(__('Accounts with inbox not cleared for more than 24 hours')); ?></h5>
                    </div>
                </a>
            </div>
            
            
            <div class="col-md-4">
                <a href="<?php echo e(url_app('publishing/approvals')); ?>">
                    <div class="card p-3 d-flex flex-row align-items-center">
                        <h3 class="count text-primary mb-0"><?php echo e($dailyAlerts['pending_approval_count']); ?></h3>
                        <h5 class="mb-0 fw-bold"><?php echo e(__('Posts pending approval')); ?></h5>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>


<div class="card d-alert position-relative overflow-hidden hp-100 mb-4">
    <div class="card-body py-4 px-4">
        <div class="gap-3 mb-3 d-flex justify-content-between align-items-start align-items-md-end flex-column flex-md-row">
            <div class="text-black">
                <h5 class="fw-6" id="date_text"><?php echo e(__('Today!')); ?> <?php echo e(now()->format('F d, Y')); ?></h5>
                <p class="mb-0"><?php echo e(__("What's happening on your accounts")); ?> <span id="load_day_type">today</span>!</p>
            </div>
            <div class="d-flex align-items-center gap-6 renew-plan">
                <button type="button" onclick="changeTodayCounts('daily', this)" class="today_report btn btn-primary btn-sm active">
                    <?php echo e(__('Daily')); ?>

                </button>
                <button type="button" onclick="changeTodayCounts('weekly', this)" class="today_report btn btn-outline-primary btn-sm">
                    <?php echo e(__('Weekly')); ?>

                </button>
                <button type="button" onclick="changeTodayCounts('monthly', this)" class="today_report btn btn-outline-primary btn-sm">
                    <?php echo e(__('Monthly')); ?>

                </button>
            </div>
        </div>
        
        <div class="row row-gap-4">
            
            <div class="col-md-4">
                <a href="<?php echo e(url_app('publishing')); ?>">
                    <div class="card p-3 d-flex flex-row align-items-center">
                        <h3 class="count text-primary mb-0" id="total_scheduled_post"><?php echo e(number_format($todayCounts['total_scheduled_post'])); ?></h3>
                        <h5 class="mb-0 fw-bold"><?php echo e(__('Amount of total scheduled posts')); ?></h5>
                    </div>
                </a>
            </div>
            
            
            <div class="col-md-4">
                <a href="<?php echo e(url_app('inbox')); ?>">
                    <div class="card p-3 d-flex flex-row align-items-center">
                        <h3 class="count text-primary mb-0" id="inbox_messages"><?php echo e(number_format($todayCounts['inbox_messages'])); ?></h3>
                        <h5 class="mb-0 fw-bold"><?php echo e(__('Inbox Messages')); ?></h5>
                    </div>
                </a>
            </div>
            
            
            <div class="col-md-4">
                <a href="<?php echo e(url_app('reviews')); ?>">
                    <div class="card p-3 d-flex flex-row align-items-center">
                        <h3 class="count text-primary mb-0" id="top_performing_post"><?php echo e(number_format($todayCounts['total_reviews'])); ?></h3>
                        <h5 class="mb-0 fw-bold"><?php echo e(__('Total Reviews')); ?></h5>
                    </div>
                </a>
            </div>
            
            
            <div class="col-md-4">
                <a href="<?php echo e(url_app('people')); ?>">
                    <div class="card p-3 d-flex flex-row align-items-center">
                        <h3 class="count text-primary mb-0" id="top_performing_video"><?php echo e(number_format($todayCounts['new_people'])); ?></h3>
                        <h5 class="mb-0 fw-bold"><?php echo e(__('Number of New People added')); ?></h5>
                    </div>
                </a>
            </div>
            
            
            <div class="col-md-4">
                <a href="<?php echo e(url_app('publishing')); ?>">
                    <div class="card p-3 d-flex flex-row align-items-center">
                        <h3 class="count text-primary mb-0" id="total_video_post"><?php echo e(number_format($todayCounts['total_failed_post'])); ?></h3>
                        <h5 class="mb-0 fw-bold"><?php echo e(__('Total failed posts')); ?></h5>
                    </div>
                </a>
            </div>
            
            
            <div class="col-md-4">
                <a href="<?php echo e(url_app('holidays')); ?>">
                    <div class="card p-3 d-flex flex-row align-items-center">
                        <h3 class="count text-primary mb-0" id="total_holidays"><?php echo e(number_format($todayCounts['total_holidays'])); ?></h3>
                        <h5 class="mb-0 fw-bold"><?php echo e(__('Holidays')); ?></h5>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-4 g-4">

    
    <div class="col-md-6">
        <div class="card shadow-sm rounded-4 hp-100 min-h-350">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4 h-100">
                <div class="d-flex flex-column mb-3 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center b-r-12 size-50 bg-primary-100 border border-primary-200">
                        <i class="fa-light fa-gauge text-primary fs-22"></i>
                    </span>
                    <span class="fw-6 fs-20"><?php echo e(__('Post Quota')); ?></span>
                </div>
                <?php if($quota['limit'] == -1): ?>
                    <div class="fw-bold fs-2 mb-1 text-primary"><?php echo e(__('Unlimited')); ?></div>
                    <div class="fs-15 text-muted"><?php echo e($quota['message']); ?></div>
                <?php else: ?>
                    <div class="fw-bold fs-2 mb-1 text-dark">
                        <?php echo e($quota['used']); ?>/<?php echo e($quota['limit']); ?>

                        <span class="fs-12 text-muted">
                            (<?php echo e(__('left:')); ?> <?php echo e($quota['left']); ?>)
                        </span>
                    </div>
                    <div class="w-100 mb-2">
                        <div class="progress h-7 bg-gray-200">
                            <div class="progress-bar
                                <?php echo e($quota['left'] == 0 ? 'bg-danger' : ($quota['left'] <= 5 ? 'bg-warning' : 'bg-primary')); ?>"
                                role="progressbar"
                                style="width:<?php echo e($quota['limit'] > 0 ? round($quota['used']/$quota['limit']*100) : 0); ?>%;">
                            </div>
                        </div>
                    </div>
                    <div class="fs-15 <?php echo e($quota['left'] == 0 ? 'text-danger' : ($quota['left'] < 5 ? 'text-warning' : 'text-muted')); ?>">
                        <?php echo e($quota['message']); ?>

                    </div>
                <?php endif; ?>
                <div class="mt-3 fw-6 text-dark fs-16">
                    <?php echo e(__('Total Posted:')); ?> <span class="fw-bold"><?php echo e(number_format($totalPosts)); ?></span>
                </div>
                <div class="fs-14 text-muted">
                    <?php echo e(__('Compared to last :days days', ['days' => number_format($startDate->diffInDays($endDate),0)])); ?>

                </div>
            </div>
        </div>
    </div>

    
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="fs-20 mb-0"><?php echo e(__("Add Channels")); ?></div>
            </div>
            <div class="card-body">
                <div class="row max-h-300 overflow-y-scroll row-gap-4">
                    <?php if( !empty( $channels ) ): ?>
                        <?php $__currentLoopData = $channels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-6">
                                <div class="card border-gray-300">
                                    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center gap-10">
                                        <div class="d-flex align-items-center justify-content-center size-50 text-white border-1 b-r-100 fs-16" style="background-color: <?php echo e($channel['color']); ?>;">
                                            <i class="<?php echo e($channel['icon']); ?>"></i>
                                        </div>
                                        <div class="fs-14 fw-5"><?php echo e(__($channel['name'])); ?></div>
                                        <div>
                                            <?php if( !empty( $channel ) && isset( $channel['items']  ) ): ?>
                                                <?php $__currentLoopData = $channel['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <a href="<?php echo e(url($item["uri"])); ?>" class="btn btn-outline btn-sm btn-light mb-1"><i class="fa-light fa-plus"></i> <?php echo e(__( ucfirst( str_replace("_", " ", $item["category"]) ) )); ?></a>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>


	<div class="col-md-12">
		<div class="card position-relative overflow-hidden">
			<div class="card-body d-flex align-items-center justify-content-between py-3 px-4">
				<div class="text-black">
					<h5 class="fw-6 d-flex icon-primary"><span><?php echo file_get_contents(public_path('img/Reports.svg')); ?></span> Post Analytics</h5>
				</div>
				
				<div class="d-flex gap-8">
					<div class="align-self-end">
						<select class="form-control">
							<option>All Platform</option>
							<option>Facebook</option>
						</select>
					</div>
					<div class="d-flex align-items-center justify-content-between gap-8">
						<div class="daterange d-none bg-white b-r-20 fs-12 border-gray-300 border" data-open="left"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
    
    <div class="col">
        <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-success-100 border border-success-200">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
                <div class="d-flex align-items-center mb-2 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center b-r-15 size-44 bg-success-500">
                        <i class="fa-light fa-circle-check text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted"><?php echo e($statusMap[4]['label'] ?? __('Success')); ?></span>
                </div>
                <div class="fw-bold fs-2 mb-0 lh-sm text-dark"><?php echo e(number_format($successTotal)); ?></div>
                <div class="fs-14 text-muted">
                    <?php echo e(($statusGrowth[4] ?? 0) == 0 ? '0%' : (($statusGrowth[4] ?? 0) > 0 ? '+' : '-') . abs($statusGrowth[4] ?? 0) . '%'); ?>

                </div>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-danger-100 border border-danger-200">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
                <div class="d-flex align-items-center mb-2 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center b-r-15 size-44 bg-danger-500">
                        <i class="fa-light fa-circle-xmark text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted"><?php echo e($statusMap[5]['label'] ?? __('Failed')); ?></span>
                </div>
                <div class="fw-bold fs-2 mb-0 lh-sm text-dark"><?php echo e(number_format($failedTotal)); ?></div>
                <div class="fs-14 text-muted">
                    <?php echo e(($statusGrowth[5] ?? 0) == 0 ? '0%' : (($statusGrowth[5] ?? 0) > 0 ? '+' : '-') . abs($statusGrowth[5] ?? 0) . '%'); ?>

                </div>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-primary-100 border border-primary-200">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
                <div class="d-flex align-items-center mb-2 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center b-r-15 size-44 bg-primary-500">
                        <i class="fa-light fa-badge-check text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted"><?php echo e(__('Success Rate')); ?></span>
                </div>
                <div class="fw-bold fs-2 mb-0 lh-sm text-primary"><?php echo e($successRate); ?>%</div>
                <div class="fs-14 text-muted"><?php echo e(__('of processed posts')); ?></div>
            </div>
        </div>
    </div>

    
	<div class="col">
	    <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-teal-100 border border-teal-200">
	        <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
	            <div class="d-flex align-items-center mb-2 gap-12">
	                <span class="d-inline-flex align-items-center justify-content-center b-r-15 size-44 bg-teal-500">
	                    <i class="fa-light fa-arrows-rotate text-white fs-22"></i>
	                </span>
	                <span class="fw-6 fs-14 text-muted"><?php echo e($processingLabel); ?></span>
	            </div>
	            <div class="fw-bold fs-2 mb-0 lh-sm text-dark"><?php echo e(number_format($processingTotal)); ?></div>
	            <div class="fs-14 text-muted">
	                <?php echo e($processingGrowth == 0 ? '0%' : ($processingGrowth > 0 ? '+' : '-') . abs($processingGrowth) . '%'); ?>

	            </div>
	        </div>
	    </div>
	</div>

<div class="col-md-12">
    <div class="row g-3">
        
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center b-r-15 mr-10 bg-success-100" style="width: 48px; height: 48px;">
                            <i class="fa-light fa-circle-check text-success fs-20"></i>
                        </div>
                        <div class="flex-grow-1">
							<div class="">
								<span class="fs-28 fw-9 text-success me-1"><?php echo e(number_format($successTotal)); ?></span> 
								<span class="fw-6 text-gray-700">Succeed</span>
							</div>
                            <div class="text-muted fs-12"><?php echo e($startDate->format('m/d/Y')); ?> - <?php echo e($endDate->format('m/d/Y')); ?></div>
                        </div>
                    </div>
                    <div id="success-mini-chart" style="height: 120px;"></div>
                </div>
            </div>
        </div>

        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center b-r-15 mr-10 bg-danger-100" style="width: 48px; height: 48px;">
                            <i class="fa-light fa-circle-xmark text-danger fs-20"></i>
                        </div>
                        <div class="flex-grow-1">
							<div class="">
								<span class="fs-28 fw-9 text-success me-1"><?php echo e(number_format($failedTotal)); ?></span> 
								<span class="fw-6 text-gray-700">Failed</span>
							</div>
                            <div class="text-muted fs-12"><?php echo e($startDate->format('m/d/Y')); ?> - <?php echo e($endDate->format('m/d/Y')); ?></div>
                        </div>
                    </div>
                    <div id="failed-mini-chart" style="height: 120px;"></div>
                </div>
            </div>
        </div>

        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center b-r-15 mr-10 bg-success-100" style="width: 48px; height: 48px;">
                            <i class="fa-light fa-calendar-check text-success fs-20"></i>
                        </div>
                        <div class="flex-grow-1">
							<div class="">
								<span class="fs-28 fw-9 text-success me-1"><?php echo e(number_format($totalPosts)); ?></span> 
								<span class="fw-6 text-gray-700">Total</span>
							</div>
                            <div class="text-muted fs-12"><?php echo e($startDate->format('m/d/Y')); ?> - <?php echo e($endDate->format('m/d/Y')); ?></div>
                        </div>
                    </div>
                    <div id="total-mini-chart" style="height: 120px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h5 class="fs-20 mb-0"><?php echo e(__('Report post by status')); ?></h5>
        </div>
        <div class="card-body">
            <div id="posts-error-success-chart" style="height: 400px;"></div>
        </div>
        <div class="d-flex card-body p-0 border-top">
            <div class="flex-fill px-4 py-3 border-end">
                <div class="text-gray-500 fs-14 mb-0"><?php echo e(__('Success')); ?></div>
                <div class="text-gray-800 fs-25 fw-bold"><?php echo e(number_format($errorSuccessSummary['success_total'])); ?></div>
            </div>
            <div class="flex-fill px-4 py-3 border-end">
                <div class="text-gray-500 fs-14 mb-0"><?php echo e(__('Error')); ?></div>
                <div class="text-gray-800 fs-25 fw-bold"><?php echo e(number_format($errorSuccessSummary['fail_total'])); ?></div>
            </div>
            <div class="flex-fill px-4 py-3">
                <div class="text-gray-500 fs-14 mb-0"><?php echo e(__('Success Rate')); ?></div>
                <div class="text-gray-800 fs-25 fw-bold">
                    <?php echo e($errorSuccessSummary['success_rate']); ?>%
                </div>
            </div>
        </div>
    </div>
</div>


<div class="col-md-12">
    <div class="row g-4">
        
        <div class="col-md-6">
            <div class="card h-100 overflow-hidden">
                <div class="card-header">
                    <h5 class="fs-20 mb-0"><?php echo e(__('Report post by social media')); ?></h5>
                </div>
                <div class="card-body d-flex align-items-start justify-content-center">
                    <div id="social-media-pie-chart" style="height: 400px; width: 100%;"></div>
                </div>
                <div class="card-footer border-top p-0">
                    <div class="table-responsive w-100">
                        <table class="table table-sm table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th class="text-muted fs-13"><?php echo e(__('Social Media')); ?></th>
                                    <th class="text-muted fs-13 text-end"><?php echo e(__('Total Posts')); ?></th>
                                </tr>
                            </thead>
                            <tbody id="social-media-stats">
                                <?php $__currentLoopData = $socialMediaStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <span class="d-inline-block rounded-circle me-2" style="width: 10px; height: 10px; background-color: <?php echo e($stat['color']); ?>;"></span>
                                        <?php echo e($stat['name']); ?>

                                    </td>
                                    <td class="text-end fw-bold"><?php echo e(number_format($stat['y'])); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        
<div class="col-md-6">
    <div class="card h-100 overflow-hidden">
        <div class="card-header">
            <h5 class="fs-20 mb-0"><?php echo e(__('Recent publications')); ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="schedules-main overflow-auto my-3 max-h-600 h-100">
                <div class="schedule-list px-3">
                    <?php $__empty_1 = true; $__currentLoopData = $recentPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $data = json_decode($post->data);
                            $result = json_decode($post->result);
                        ?>
                        
                        <div class="card border mb-3 position-relative overflow-hidden">
                            
                            <?php if($post->status == 4): ?>
                                <div class="position-absolute top-0 end-0 w-56 h-56" style="z-index: 10;">
                                    <span class="badge bg-success b-r-0">
                                        <i class="fs-20 fad fa-check-double text-white"></i>
                                    </span>
                                </div>
                            <?php elseif($post->status == 5): ?>
                                <div class="position-absolute top-0 end-0 w-56 h-56" style="z-index: 10;">
                                    <span class="badge bg-danger b-r-0">
                                        <i class="fs-20 fad fa-exclamation-circle text-white"></i>
                                    </span>
                                </div>
                            <?php endif; ?>

                            
                            <div class="card-header border-0 bg-white p-3">
                                <?php if( !empty($post->accounts) && count($post->accounts) > 0): ?>
                                    
                                    <div class="d-flex align-items-center">
                                        
                                        <div class="d-flex me-2" style="margin-right: -8px;">
                                            <?php $__currentLoopData = array_slice($post->accounts, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="position-relative">
                                                    <img src="<?php echo e(Media::url($account['avatar'])); ?>" 
                                                         class="rounded-circle border border-2 border-white bg-white" 
                                                         style="width: 45px; height: 45px; object-fit: cover;" 
                                                         alt="<?php echo e($account['name']); ?>"
                                                         title="<?php echo e($account['name']); ?>">
                                                    
                                                    <span class="position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center" 
                                                          style="width: 14px; height: 14px; background-color: <?php echo e($account['color']); ?>; border: 2px solid white;">
                                                        <?php echo e(get_social_media_image($account['social_network'])); ?>

                                                    </span>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            
                                            
                                            <?php if(!empty($post->accounts) && count($post->accounts) > 4): ?>
                                                <div class="rounded-circle border border-2 border-white bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 45px; height: 45px; margin-left: -12px; z-index: 1;">
                                                    <span class="fs-10 fw-bold text-muted">+<?php echo e(count($post->accounts) - 4); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        
                                        <div class="flex-grow-1 ms-2">
                                            <!--<div class="fw-bold fs-13">
                                                <?php $__currentLoopData = array_slice($post->accounts, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($index > 0): ?>, <?php endif; ?>
                                                    <?php echo e(get_social_media_image($account['social_network'])); ?>

                                                    <?php echo e($account['name']); ?>

                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php if(count($post->accounts) > 2): ?>
                                                    <span class="text-muted">+<?php echo e(count($post->accounts) - 2); ?> more</span>
                                                <?php endif; ?>
                                            </div>-->
                                            <div class="text-muted fs-11">
                                                <i class="fa-light fa-calendar"></i>
                                                <?php echo e(\Carbon\Carbon::createFromTimestamp($post->time_post)->format('m/d/Y g:i A')); ?>

                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            
                            <div class="card-body p-3 pt-0">
                                <div class="d-flex gap-3">
                                    
                                    <div class="flex-shrink-0 me-3">
                                        <?php if($post->type == 'media' && !empty($post->medias)): ?>
                                            <?php 
                                                $firstMedia = is_array($post->medias) ? ($post->medias[0] ?? null) : $post->medias;
                                            ?>
                                            <?php if($firstMedia): ?>
                                                <div class="position-relative">
                                                    <?php if(str_contains($firstMedia, '.mp4') || str_contains($firstMedia, '.mov')): ?>
                                                        <video class="b-r-15 border" style="width: 80px; height: 80px; object-fit: cover;" muted>
                                                            <source src="<?php echo e(Media::url($firstMedia)); ?>" type="video/mp4">
                                                        </video>
                                                    <?php else: ?>
                                                        <img src="<?php echo e(Media::url($firstMedia)); ?>" 
                                                             class="b-r-15 border" 
                                                             style="width: 80px; height: 80px; object-fit: cover;" 
                                                             alt="">
                                                    <?php endif; ?>
                                                    
                                                    <?php if(is_array($post->medias) && count($post->medias) > 1): ?>
                                                        <div class="position-absolute top-0 end-0 badge bg-dark m-1" style="font-size: 9px;">
                                                            +<?php echo e(count($post->medias) - 1); ?>

                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif($post->type == 'link'): ?>
                                            <div class="d-flex align-items-center justify-content-center rounded border bg-light" style="width: 80px; height: 80px;">
                                                <i class="fa-light fa-link text-primary fs-24"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center rounded border bg-light" style="width: 80px; height: 80px;">
                                                <i class="fa-light fa-align-center text-primary fs-24"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    
                                    <div class="flex-grow-1">
                                        <div class="text-muted fs-13" style="max-height: 80px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical;">
                                            <?php echo e($post->caption ?? ''); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <?php if($post->status == 4): ?>
                                <div class="card-footer bg-success bg-opacity-10 text-white border-0 py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <span class="fs-13"><?php echo e(__('Post Published')); ?></span>
                                        <?php if(!empty($result->url)): ?>
                                            <a href="<?php echo e($result->url); ?>" target="_blank" class="fs-13">
                                                <i class="fa-light fa-eye"></i> <?php echo e(__('View post')); ?>

                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php elseif($post->status == 5): ?>
                                <div class="card-footer bg-danger bg-opacity-10 text-white border-0 py-2 px-3">
                                    <div class="fs-13">
                                        <i class="fa-light fa-exclamation-triangle me-1"></i>
                                        <?php echo e($result->message ?? __('Post failed to publish')); ?>

                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fa-light fa-inbox fs-48 text-muted opacity-50"></i>
                            </div>
                            <div class="text-muted mb-3"><?php echo e(__('No recent publications')); ?></div>
                            <a href="<?php echo e(url_app('publishing')); ?>" class="btn btn-primary btn-sm">
                                <i class="fa-light fa-plus"></i> <?php echo e(__('Create post')); ?>

                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
	</div>
</div>
</div>
<div class="footer pt-50 mt-50">
	<div class="container">
		<div class="row">
			<div class="col-md-2 mb-4">
				<div class="fw-7 fs-18 mb-4">Publishing</div>
				<ul>
					<li class="mb-2"><a class="text-black" href="https://itspando.com/post">Create post</a></li>
					<li class="mb-2"><a class="text-black" href="https://itspando.com/schedules">Calendar</a></li>
					<li class="mb-2"><a class="text-black" href="https://itspando.com/drafts">Drafts</a></li>
					<li class="mb-2"><a class="text-black" href="https://itspando.com/approvals">Approvals</a></li>
				</ul>
			</div>
			<div class="col-md-2 mb-4">
				<div class="fw-7 fs-18 mb-4">Quick Links</div>
				<ul>
					<li class="mb-2"><a class="text-black" href="https://itspando.com/account_manager">Account Manager</a></li>
					<li class="mb-2"><a class="text-black" href="https://itspando.com/reports">Reports</a></li>
					<li class="mb-2"><a class="text-black" href="https://itspando.com/inbox">Inbox</a></li>
					<li class="mb-2"><a class="text-black" href="https://itspando.com/users">Users</a></li>
				</ul>
			</div>
			<div class="col-md-2 mb-4">
				<div class="fw-7 fs-18 mb-4">Connect Profile</div>
				<ul>
					<li class="mb-2"><a class="text-black" target="_blank" href="https://itspando.com/facebook_pages/oauth">Facebook</a></li>
					<li class="mb-2"><a class="text-black" target="_blank" href="https://itspando.com/instagram_profiles/oauth">Instagram</a></li>
					<li class="mb-2"><a class="text-black" target="_blank" href="https://itspando.com/linkedin_pages/oauth">Linkedin</a></li>
					<li class="mb-2"><a class="text-black" target="_blank" href="https://itspando.com/twitter_profiles/oauth">X</a></li>
					<li class="mb-2"><a class="text-black" target="_blank" href="https://itspando.com/pinterest_boards/oauth">Pinterest</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
<style>
/* Stacked avatars hover effect */
.schedule-list .position-relative:hover {
    z-index: 20 !important;
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
.today_report {
    transition: all 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.card a {
    text-decoration: none;
    color: inherit;
}
</style>
<script>
function changeTodayCounts(dayType, button) {
    // Update button states
    $('.today_report').removeClass('active btn-primary').addClass('btn-outline-primary');
    $(button).removeClass('btn-outline-primary').addClass('active btn-primary');
    
    // Show loading
    if (typeof $(".loading").show === 'function') {
        $(".loading").show();
    }
    
    $.ajax({
        url: '<?php echo e(route("dashboard.today-counts")); ?>',
        type: 'GET',
        data: {
            day_type: dayType,
            team_id: '<?php echo e($teamId ?? ""); ?>'
        },
        success: function(response) {
            
            // Update counts
            $('#total_scheduled_post').text(response.total_scheduled_post.toLocaleString());
            $('#inbox_messages').text(response.inbox_messages.toLocaleString());
            $('#top_performing_post').text(response.total_reviews.toLocaleString());
            $('#top_performing_video').text(response.new_people.toLocaleString());
            $('#total_video_post').text(response.total_failed_post.toLocaleString());
            $('#total_holidays').text(response.total_holidays.toLocaleString());
            
            // Update date text
            $('#date_text').html(response.date_text_html);
            $('#load_day_type').text(response.day_type_text);
            
            if (typeof $(".loading").hide === 'function') {
                $(".loading").hide();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading today counts:', error);
            if (typeof $(".loading").hide === 'function') {
                $(".loading").hide();
            }
            alert('Error loading data. Please try again.');
        }
    });
}
</script>
<script>
    // Social Media Pie Chart
    var socialMediaData = <?php echo json_encode($socialMediaStats); ?>;
    
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
			enabled: false,
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
    var errorSuccessChart = <?php echo json_encode($errorSuccessChart); ?>;
    
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
    var errorSuccessChart = <?php echo json_encode($errorSuccessChart); ?>;
    
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

<script>
(function() {
    
    // Wait for DOM to be ready
    setTimeout(function() {
        if (typeof Main !== 'undefined' && typeof Main.dateRange === 'function') {
            
            Main.dateRange();
            
            // Setup date change handler
            //setupDateChangeHandler();
        } else {
            console.error('Main.dateRange() not available');
        }
    }, 100);
})();
</script>
<?php endif; ?><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppPublishing\resources/views/partials/dashboard-item.blade.php ENDPATH**/ ?>