<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('Social Analytics')).'','description' => ''.e(__('Track and compare performance across social media platforms.')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sub-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\SubHeader::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a)): ?>
<?php $attributes = $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a; ?>
<?php unset($__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a)): ?>
<?php $component = $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a; ?>
<?php unset($__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container pb-5">

        <?php $__empty_1 = true; $__currentLoopData = $analytics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $network => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="mb-5">
                <h4 class="fw-6 fs-18 mb-4"><?php echo e($network); ?></h4>
                <div class="row">
                    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-grow-1 align-items-top gap-8">
                                    
                                    <div class="text-gray-600 size-40 min-w-40 d-flex align-items-center justify-content-between position-relative">
                                        <a href="<?php echo e($value->url); ?>" target="_blank" class="text-gray-900 text-hover-primary">
                                            <img data-src="<?php echo e(Media::url($value->avatar)); ?>" src="<?php echo e(theme_public_asset('img/default.png')); ?>" class="b-r-100 w-full h-full border-1 lazyload" onerror="this.src='<?php echo e(theme_public_asset('img/default.png')); ?>'">
                                        </a>
                                        <span class="size-17 border-1 b-r-100 position-absolute fs-9 d-flex align-items-center justify-content-between text-center text-white b-0 r-0" style="background-color: <?php echo e($value->module_color); ?>;">
                                            <div class="w-100"><i class="<?php echo e($value->module_icon); ?>"></i></div>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 fs-14 fw-5 text-truncate">
                                        <div class="text-truncate">
                                            <a href="<?php echo e($value->url); ?>" target="_blank" class="text-gray-900 text-hover-primary">
                                                <?php echo e($value->name); ?>

                                            </a>
                                        </div>
                                        <div class="fs-12 text-gray-600 text-truncate">
                                            <?php echo e(__( ucfirst( $value->social_network." ".$value->category ) )); ?>

                                        </div>
                                    </div>

                                </div>
                                    
                            </div>
                            <div class="card-footer fs-12 d-flex justify-content-center gap-8">
                            <a href="<?php echo e(module_url($value->social_network."/".$value->id_secure)); ?>" class="d-flex flex-fill gap-8 align-items-center justify-content-center text-gray-900 text-hover-primary fw-5">
                                <i class="fa-light fa-chart-mixed"></i>
                                <span><?php echo e(__("View")); ?></span>
                            </a>
                        </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>



                    <?php if($data->isEmpty()): ?>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
                                    <span class="fs-70 mb-3 text-primary">
                                        <i class="fa-light fa-chart-mixed"></i>
                                    </span>
                                    <div class="fw-semibold fs-5 mb-2 text-gray-800">
                                        <?php echo e(__('No accounts yet')); ?>

                                    </div>
                                    <div class="text-body-secondary mb-4">
                                        <?php echo e(__('Connect your social accounts to start tracking analytics and gain insights into your performance.')); ?>

                                    </div>
                                    <a href="<?php echo e(route('app.channels.index')); ?>" class="btn btn-dark">
                                        <i class="fa-light fa-plus me-1"></i> <?php echo e(__('Add channel')); ?>

                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
                <span class="fs-70 mb-3 text-primary">
                    <i class="fa-light fa-chart-mixed"></i>
                </span>
                <div class="fw-semibold fs-5 mb-2 text-gray-800">
                    <?php echo e(__('No analytics data available.')); ?>

                </div>
                <div class="text-body-secondary mb-4">
                    <?php echo e(__('Analytics data is not yet available for this section. Please check back later.')); ?>

                </div>
                <a href="<?php echo e(route('app.dashboard.index')); ?>" class="btn btn-dark">
                    <i class="fa-light fa-house"></i> <?php echo e(__('Dashboard')); ?>

                </a>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppAnalytics/resources/views/index.blade.php ENDPATH**/ ?>