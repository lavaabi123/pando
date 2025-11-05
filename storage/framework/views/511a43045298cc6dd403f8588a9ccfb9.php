<?php echo $__env->make('apppublishing::header_center', [], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('Approval')).'','description' => ''.e(__('See all your approvals in one place for easy access and management.')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

<?php $__env->startSection('header_end'); ?>
    <div class="compose_header position-absolute w-100 t-0 l-0 d-flex justify-content-between align-items-center zIndex-9 bg-white h-70 border-bottom px-4 d-none">
        <div class="fw-6 fs-18"><?php echo e(__("New Post")); ?></div>
        <div class="fw-6 fs-18">
            <div class="btn btn-icon btn-light btn-hover-danger b-r-50 a-rotate closeCompose">
                <i class="fa-light fa-xmark"></i>
            </div>
        </div>
    </div>
    <a class="btn btn-dark btn-sm actionItem b-r-50 text-nowrap" href="<?php echo e(module_url("composer")); ?>" data-append-content="composer-scheduling" data-call-success="AppPubishing.openCompose();"><i class="fa-light fa-calendar-lines-pen"></i> <?php echo e(__("Compose")); ?></a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="composer-scheduling position-absolute zIndex-9 wp-100 hp-100 top-0 d-none"></div>
    <div class="container">

        <div class="ajax-scroll" data-url="<?php echo e(module_url("list")); ?>" data-resp=".approval-list" data-scroll="document">

            <div class="row approval-list">
                <div class="mb-50"></div>
            </div>

            <div class="pb-30 ajax-scroll-loading d-none">
                <div class="app-loading mx-auto mt-10 pl-0 pr-0">
                    <div></div>   
                    <div></div>    
                    <div></div>    
                    <div></div>    
                </div>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppPublishingApproval\resources/views/index.blade.php ENDPATH**/ ?>