

<?php $__env->startSection('content'); ?>
    <div class="container px-4 max-w-700">

        <div class="mt-4 mb-5">
            <div class="d-flex flex-column flex-lg-row flex-md-column align-items-md-start align-items-lg-center justify-content-between">
                <div class="my-3 d-flex flex-column gap-8">
                    <h1 class="fs-20 font-medium lh-1 text-gray-900">
                        <?php echo e(__("Add new channels")); ?>

                    </h1>
                    <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                        <div class="d-flex gap-8">
                            <span class="text-gray-600"><span class="text-gray-600"><?php echo e(__('Add and Start Managing Your Social Profile')); ?></span></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-8">
                    <a class="btn btn-light btn-sm" href="<?php echo e($result['reconnect_url']); ?>">
                        <span><i class="fa-light fa-rotate-right"></i></span>
                        <span><?php echo e(__('Reconnect')); ?></span>
                    </a>
                </div>
            </div>
        </div>

        <div>
            <div class="input-group mb-3">
                <input class="form-control bg-white search-input" placeholder="<?php echo e(__("Search ...")); ?>" type="text" value="">
                <span class="btn btn-icon btn-input min-w-55">
                    <input class="form-check-input checkbox-all" type="checkbox" value="">
                </span>
            </div>
        </div>

        <form class="actionForm" action="<?php echo e($result['save_url']); ?>" method="POST">
            <?php if($result['status'] == 1 && isset($result['channels']) && !empty($result['channels']) ): ?>

                <?php $__currentLoopData = $result['channels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-2 search-list">
                        <div class="card shadow-none b-r-6">
                            <div class="card-body px-3">
                                <div class="d-flex flex-grow-1 align-items-center gap-8">
                                    <label for="channel_<?php echo e($key); ?>" class="text-gray-600 size-40 min-w-40 d-flex align-items-center justify-content-between position-relative">
                                        <img src="<?php echo e(__($value['avatar'])); ?>" class="b-r-100 w-full h-full border-1">
                                        <span class="size-16 border-1 b-r-100 position-absolute fs-10 d-flex align-items-center justify-content-between text-center text-white b-0 r-0" style="background-color: <?php echo e($result['module']['color']); ?>;">
                                            <div class="w-100"><i class="<?php echo e($result['module']['icon']); ?>"></i></div>
                                        </span>
                                    </label>
                                    <label for="channel_<?php echo e($key); ?>" class="flex-grow-1 fs-14 fw-5 text-truncate">
                                        <div class="text-truncate"><?php echo e(__($value['name'])); ?></div>
                                        <div class="fs-12 text-gray-600 text-truncate"><?php echo e(__($value['desc'])); ?></div>
                                    </label>
                                    <div class="d-flex fs-14 gap-8">
                                        <input class="form-check-input checkbox-item" type="checkbox" name="channels[]" value="<?php echo e($value['id']); ?>" id="channel_<?php echo e($key); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="mt-4">
                    <button type="submit" class="btn btn-dark w-100" href="<?php echo e(module_url("create")); ?>">
                        <span><i class="fa-light fa-plus"></i></span>
                        <span><?php echo e(__('Add new')); ?></span>
                    </button>
                </div>
            <?php elseif($result['status'] == 0): ?>
                <div class="mt-5">
                    <div class="alert alert-danger d-flex align-items-center gap-16" role="alert">
                        <div class="fs-45"><i class="fa-light fa-triangle-exclamation"></i></div>
                        <div>
                            <div class="fw-6"><?php echo e(__("Error")); ?></div>
                            <div><?php echo e(__($result['message'])); ?></div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty my-5"></div>
            <?php endif; ?>
        </form>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppChannels/resources/views/add.blade.php ENDPATH**/ ?>