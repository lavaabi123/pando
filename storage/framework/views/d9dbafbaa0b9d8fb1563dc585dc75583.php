<div class="card b-r-6 border-gray-300 mb-3">
    <div class="card-header">
        <div class="form-check">
            <input class="form-check-input prevent-toggle" type="checkbox" value="1" id="appanalytics" name="permissions[appanalytics]" <?php if( array_key_exists("appanalytics", $permissions ) ): echo 'checked'; endif; ?>>
            <label class="fw-6 fs-14 text-gray-700 ms-2" for="appanalytics">
                <?php echo e(__("Analytics")); ?>

            </label>
        </div>
        <input class="form-control d-none" name="labels[appanalytics]" type="text" value="Channels">
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 allow_analytics">
                <div class="mb-0">
                    <div class="d-flex gap-8 justify-content-between border-bottom mb-3 pb-2">
                        <div class="fw-5 text-gray-800 fs-14 mb-2"><?php echo e(__('Allow channels')); ?></div>
                        <div class="form-check">
                            <input class="form-check-input checkbox-all" data-checkbox-parent=".allow_analytics" type="checkbox" value="" id="allow_analytics">
                            <label class="form-check-label" for="allow_analytics">
                                <?php echo e(__('Select All')); ?>

                            </label>
                        </div>
                    </div>
                    <div class="row">

                        <?php
                        $analytics = Analytics::getAnalytics();
                        ?>

                        <?php $__currentLoopData = $analytics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $key = 'appanalytics.' . $k;
                                $labelValue = old("labels.$key", $labels[$key] ?? $key);
                            ?>

                            <div class="col-md-4 mb-3">
                                <div class="form-check mb-1">
                                    <input class="form-check-input checkbox-item" 
                                           type="checkbox" 
                                           name="permissions[<?php echo e($key); ?>]" 
                                           value="1" 
                                           id="<?php echo e($key); ?>" 
                                           <?php if(array_key_exists($key, $permissions)): echo 'checked'; endif; ?>>
                                    <label class="form-check-label mt-1 text-truncate" for="<?php echo e($key); ?>">
                                        <?php echo e(str_replace( "_", " ", ucfirst($k))); ?>

                                    </label>
                                </div>
                                <input class="form-control form-control-sm d-none" 
                                       type="text" 
                                       name="labels[<?php echo e($key); ?>]" 
                                       value="<?php echo e($labelValue); ?>" 
                                       placeholder="<?php echo e(__('Custom label')); ?>">
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppAnalytics/resources/views/permissions.blade.php ENDPATH**/ ?>