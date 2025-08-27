<?php if(Access::permission('apppublishing')): ?>

<?php
    $channels = Channels::channels();
    $teamId = request()->team_id;
    $now = \Carbon\Carbon::now();
    $startDate = $now->copy()->subDays(30);
    $endDate = $now;

    $report = PublishingReport::postInfo($startDate, $endDate, $teamId ?? null);
    $reportStat = PublishingReport::postStatsGrowthInfo($startDate, $endDate, $teamId ?? null);
    $errorSuccessChart = PublishingReport::postStatsByDay($startDate, $endDate, $teamId ?? null);
    $errorSuccessSummary = $errorSuccessChart['summary'];
    $recentPosts = PublishingReport::recentPostsStatus(10, $teamId ?? null);

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

<div class="row row-cols-1 row-cols-md-4 g-4 mb-4">

    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 hp-100 min-h-350">
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
                    <?php echo e(__('Compared to last :days days', ['days' => $startDate->diffInDays($endDate)])); ?>

                </div>
            </div>
        </div>
    </div>

    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <div class="fw-5"><?php echo e(__("Add Channels")); ?></div>
            </div>
            <div class="card-body max-h-300 overflow-y-scroll">
                <div class="row">
                    <?php if( !empty( $channels ) ): ?>
                        <?php $__currentLoopData = $channels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-6 mb-4">
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

    
    <div class="col">
        <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-success-100 border border-success-200">
            <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
                <div class="d-flex align-items-center mb-2 gap-12">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-success-500">
                        <i class="fa-light fa-circle-check text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted"><?php echo e($statusMap[4]['label'] ?? __('Success')); ?></span>
                </div>
                <div class="fw-bold fs-2 mb-1 text-dark"><?php echo e(number_format($successTotal)); ?></div>
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
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-danger-500">
                        <i class="fa-light fa-circle-xmark text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted"><?php echo e($statusMap[5]['label'] ?? __('Failed')); ?></span>
                </div>
                <div class="fw-bold fs-2 mb-1 text-dark"><?php echo e(number_format($failedTotal)); ?></div>
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
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-primary-500">
                        <i class="fa-light fa-badge-check text-white fs-22"></i>
                    </span>
                    <span class="fw-6 fs-14 text-muted"><?php echo e(__('Success Rate')); ?></span>
                </div>
                <div class="fw-bold fs-2 mb-1 text-primary"><?php echo e($successRate); ?>%</div>
                <div class="fs-14 text-muted"><?php echo e(__('of processed posts')); ?></div>
            </div>
        </div>
    </div>

    
	<div class="col">
	    <div class="card shadow-sm rounded-4 hp-100 min-h-140 bg-teal-100 border border-teal-200">
	        <div class="card-body d-flex flex-column justify-content-center align-items-start p-4">
	            <div class="d-flex align-items-center mb-2 gap-12">
	                <span class="d-inline-flex align-items-center justify-content-center rounded-circle size-44 bg-teal-500">
	                    <i class="fa-light fa-arrows-rotate text-white fs-22"></i>
	                </span>
	                <span class="fw-6 fs-14 text-muted"><?php echo e($processingLabel); ?></span>
	            </div>
	            <div class="fw-bold fs-2 mb-1 text-dark"><?php echo e(number_format($processingTotal)); ?></div>
	            <div class="fs-14 text-muted">
	                <?php echo e($processingGrowth == 0 ? '0%' : ($processingGrowth > 0 ? '+' : '-') . abs($processingGrowth) . '%'); ?>

	            </div>
	        </div>
	    </div>
	</div>

    
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h5 class="fs-5 fs-16"><?php echo e(__('Successful vs Failed Posts Over Time')); ?></h5>
            </div>
            <div class="card-body border-bottom">
                <div id="posts-error-success-chart" style="height: 350px;"></div>
            </div>
            <div class="d-flex card-body p-0">
                <div class="flex-fill px-4 py-3 border-end">
                    <div class="text-gray-500 fs-14 mb-2"><?php echo e(__('Success')); ?></div>
                    <div class="text-gray-800 fs-25 fw-bold"><?php echo e(number_format($errorSuccessSummary['success_total'])); ?></div>
                </div>
                <div class="flex-fill px-4 py-3 border-end">
                    <div class="text-gray-500 fs-14 mb-2"><?php echo e(__('Error')); ?></div>
                    <div class="text-gray-800 fs-25 fw-bold"><?php echo e(number_format($errorSuccessSummary['fail_total'])); ?></div>
                </div>
                <div class="flex-fill px-4 py-3">
                    <div class="text-gray-500 fs-14 mb-2"><?php echo e(__('Success Rate')); ?></div>
                    <div class="text-gray-800 fs-25 fw-bold">
                        <?php echo e($errorSuccessSummary['success_rate']); ?>%
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="col-lg-12 mb-4">
        <div class="card border-0 shadow-sm px-0">
            <div class="card-header">
                <h5 class="fs-5 fs-16"><?php echo e(__('Recently Posted: Success & Failed')); ?></h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="RecentPostsTable">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 60px;"><?php echo e(__('Thumbnail')); ?></th>
                                <th><?php echo e(__('Caption')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th><?php echo e(__('Account')); ?></th>
                                <th><?php echo e(__('Date')); ?></th>
                                <th><?php echo e(__('View')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    
                                    <td class="text-center">
                                        <?php if(!empty($post->media_url)): ?>
                                            <img src="<?php echo e(Media::url($post->media_url)); ?>" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center bg-light border rounded" style="width: 48px; height: 48px;">
                                                <i class="fa-light fa-image text-gray-600 fs-4"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    
                                    <td><?php echo e(\Str::limit($post->title ?? $post->caption ?? '-', 80)); ?></td>

                                    
                                    <td class="text-center">
                                        <span class="badge" style="background: <?php echo e($post->status_color); ?>; color: #fff;">
                                            <?php echo e($post->status_label); ?>

                                        </span>
                                    </td>

                                    
                                    <td class="text-center"><?php echo e($post->account_id); ?></td>

                                    
                                    <td class="text-nowrap text-gray-700 fs-14">
                                        <?php echo e(\Carbon\Carbon::parse($post->created)->format('M d, Y H:i')); ?>

                                    </td>

                                    
                                    <td class="text-center">
                                        <?php if(!empty($post->permalink_url)): ?>
                                            <a href="<?php echo e($post->permalink_url); ?>" target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted"><?php echo e(__('No posts found.')); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    var errorSuccessChart = <?php echo json_encode($errorSuccessChart); ?>;
    errorSuccessChart.series[0].color = '#675dff';
    errorSuccessChart.series[1].color = '#f5222d';
    Main.Chart('areaspline', errorSuccessChart.series, 'posts-error-success-chart', {
        xAxis: {
            categories: errorSuccessChart.categories,
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
        title: { text: '<?php echo e(__("Page Views")); ?>' },
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
</script>
<?php endif; ?><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppPublishing\resources/views/partials/dashboard-item.blade.php ENDPATH**/ ?>