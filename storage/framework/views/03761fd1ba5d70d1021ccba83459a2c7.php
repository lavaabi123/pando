<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('Crons')).'','description' => ''.e(__('Automates scheduled tasks for efficient time-based execution')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
    
    <div class="card">
        <div class="card-header">
            <div class="fw-5"><?php echo e(__('Secure Cron Key')); ?></div>
        </div>
        <div class="card-body">
            <form class="actionForm" method="POST" action="<?php echo e(module_url("change")); ?>" data-confirm="<?php echo e(__('Changing the key requires updating all cronjobs in your system for continued operation. Are you sure you want to continue?')); ?>">
                <div class="input-group mb-0">
                    <span class="btn btn-input">
                        <i class="fa-light fa-key"></i>
                    </span>
                    <input class="form-control disabled" type="text" value="<?php echo e(get_option("cron_key", rand_string())); ?>">
                    <button type="submit" class="btn btn-dark">
                        <i class="fa-light fa-arrows-rotate"></i> <?php echo e(__('Change Key')); ?>

                    </button>
                </div>
                <span class="fs-12 text-gray-500"><?php echo e(__('Use this secret key for secure URL-based cron execution')); ?></span>
            </form>
        </div>
    </div>

    <?php if($crons): ?>
    <div class="fw-5 pt-5 pb-3"><?php echo e(__('List Cron')); ?></div>

        <?php $__currentLoopData = $crons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        <div class="card mb-4">
            <div class="card-header px-3">
                <div class="fw-5"> <i class="<?php echo e($value['icon']); ?> me-2" style="color: <?php echo e($value['color']); ?>"></i> <?php echo e(__($value['module_name'])??__($value['command_name'])); ?></div>
            </div>
            <?php if($value['url']??false): ?>
            <div class="card-body bg-gray-100 fw-5 fs-12 py-2 px-3">
                <?php echo e(__('Use cron with URL')); ?>

            </div>
            <div class="card-body text-success bg-dark">
                <pre class="mb-0"><?php echo e($value['expression']); ?> <?php echo e($value['url'] ?? $value['command']); ?></pre>
            </div>
            <?php endif; ?>
            <div class="card-body bg-gray-100 fw-5 fs-12 py-2 px-3">
                <?php echo e(__('Use cron with Artisan Command')); ?>

            </div>
            <div class="card-body text-success bg-dark bbr-r-10 bbl-r-10">
                <pre class="mb-0"><?php echo e($value['expression']); ?> <?php echo e($value['full_command']); ?></pre>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AdminCrons\resources/views/index.blade.php ENDPATH**/ ?>