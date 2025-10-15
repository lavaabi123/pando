<?php if( $result->Total() > 0 ): ?>

    <?php $__currentLoopData = $result; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="mb-4">
        <div class="card border-gray-300">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row gap-16">
                    <div class="size-80 border b-r-10 fs-40 d-flex align-items-center justify-content-center bg-primary-100 fw-bold text-primary">
                        <?php echo e(__('AI')); ?>

                    </div>
                    <div class="flex-grow-1 d-flex flex-column">
                        <div class="mb-2">
                            <div class="fw-5 fs-14 text-truncate-1"><?php echo e($value->name); ?></div>
                            <div class="fw-5 fs-12 text-gray-600 text-truncate-1">
                                <div class="d-flex gap-8 fs-11 text-gray-500 align-items-center">
                                    <div class=""><?php echo e(__("End date:")); ?> <?php echo e(date_show( $value->end_date )); ?></div>
                                    <div class="size-3 bg-gray-200 b-r-100"></div>
                                    <div><?php echo e(__("Created at:")); ?> <?php echo e(datetime_show( $value->created )); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="fw-5 fs-12 d-flex align-items-center gap-8 mt-auto">
                            <?php if($value->status == 1): ?>
                            <div class="text-primary">
                                <i class="fa-light fa-spin fa-loader"></i>
                                <?php echo e(__("Running")); ?>

                            </div>
                            <?php else: ?>
                            <div class="text-warning">
                                <i class="fa-light fa-pause"></i>
                                <?php echo e(__("Pause/Stop")); ?>

                            </div>
                            <?php endif; ?>

                            <div class="text-gray-200 fs-12">|</div>

                            <div>
                                <i class="fa-light fa-circle-check text-success me-1"></i>
                                <span class="text-gray-700">
                                    <span><?php echo e($value->success); ?></span>
                                    <span><?php echo e(__("Published")); ?></span>
                                </span>
                            </div>

                            <div class="text-gray-200 fs-12">|</div>

                            <div>
                                <i class="fa-light fa-circle-exclamation text-danger me-1"></i>
                                <span class="text-gray-700">
                                    <span><?php echo e($value->failed); ?></span>
                                    <span><?php echo e(__("Failed")); ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-start justify-content-md-center gap-16">

                        <a href="<?php echo e(route("app.ai-publishing.edit", ["id" => $value->id_secure])); ?>" data-id="<?php echo e($value->id_secure); ?>" class="fs-18 text-gray-900">
                            <i class="fa-light fa-gear"></i>
                        </a>

                        <a href="<?php echo e(route("app.publishing.index", ["query" => 1])); ?>" class="fs-18 text-gray-900">
                            <i class="fa-light fa-clock-rotate-left"></i>
                        </a>

                        <div class="btn-group position-static">
                            <a href="javascript:void(0);" class="dropdown-toggle dropdown-arrow-hide text-gray-900 fs-18" data-bs-toggle="dropdown" aria-expanded="true">
                                <i class="fa-light fa-grid-2"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 wp-100 max-w-125">
                                <li>
                                    <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" href="<?php echo e(module_url("status/start")); ?>" data-id="<?php echo e($value->id_secure); ?>" data-call-success="Main.ajaxScroll(true);">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-play"></i></span>
                                        <span ><?php echo e(__('Start')); ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" href="<?php echo e(module_url("status/pause")); ?>" data-id="<?php echo e($value->id_secure); ?>" data-call-success="Main.ajaxScroll(true);">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-pause"></i></span>
                                        <span><?php echo e(__('Pause/Stop')); ?></span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" href="<?php echo e(module_url("destroy")); ?>"  data-id="<?php echo e($value->id_secure); ?>" data-call-success="Main.ajaxScroll(true);">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                        <span><?php echo e(__('Delete')); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
    <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
        <span class="fs-70 mb-3 text-primary">
            <i class="fa-light fa-robot"></i>
        </span>
        <div class="fw-semibold fs-5 mb-2 text-gray-900">
            <?php echo e(__('No AI Publishing Campaigns')); ?>

        </div>
        <div class="text-body-secondary mb-4 text-center max-w-500">
            <?php echo e(__('Easily create your first automated AI publishing campaign to save time and boost your content strategy.')); ?>

        </div>
        <a href="<?php echo e(route('app.ai-publishing.create')); ?>" class="btn btn-dark">
            <i class="fa-light fa-plus me-1"></i> <?php echo e(__('Add new campaign')); ?>

        </a>
    </div>
<?php endif; ?>
<?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppAIPublishing/resources/views/list.blade.php ENDPATH**/ ?>