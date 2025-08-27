<?php $__env->startSection('form', json_encode([
    'method' => 'POST'
])); ?>

<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('Manage groups')).'','description' => ''.e(__('Effortlessly Organize and Manage All Your Groups')).'','count' => $total] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sub-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\SubHeader::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div class="d-flex">
            <div class="form-control form-control-sm">
                <span class="btn btn-icon">
                    <i class="fa-duotone fa-solid fa-magnifying-glass"></i>
                </span>
                <input class="ajax-scroll-filter" name="keyword" placeholder="<?php echo e(__('Search')); ?>" type="text">
                <button class="btn btn-icon">
                    <div class="form-check form-check-sm mb-0">
                        <input class="form-check-input checkbox-all" id="select_all" type="checkbox">
                    </div>
                </button>
            </div>
        </div>
        <div class="d-flex">
            <div class="btn-group position-static">
                <button class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
                    <i class="fa-light fa-grid-2"></i> <?php echo e(__("Actions")); ?>

                </button>
                <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125" data-popper-placement="bottom-end">
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="<?php echo e(module_url("destroy")); ?>" data-call-success="Main.ajaxScroll(true);">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                            <span><?php echo e(__("Delete")); ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="d-flex gap-8">
            <a class="btn btn-dark btn-sm actionItem" href="<?php echo e(module_url("update")); ?>" data-popup="groupModal" data-call-success="Main.ajaxScroll(true);">
                <span><i class="fa-light fa-plus"></i></span>
                <span><?php echo e(__('Create new')); ?></span>
            </a>
        </div>
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
    <div class="container px-4">

        <div class="ajax-scroll" data-url="<?php echo e(module_url("list")); ?>" data-resp=".channel-list" data-scroll="document">

            <div class="row channel-list">
                
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppGroups/resources/views/index.blade.php ENDPATH**/ ?>