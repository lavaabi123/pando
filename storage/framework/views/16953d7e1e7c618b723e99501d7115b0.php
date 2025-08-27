<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('Pinterest API')).'','description' => ''.e(__('Essential Guide to Configure Pinterest API Easily')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
<div class="container max-w-800 pb-5">
    <form class="actionForm" action="<?php echo e(url_admin("settings/save")); ?>">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Status')); ?></label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="pinterest_status" value="1" id="pinterest_status_1" <?php echo e(get_option("pinterest_status", 0)==1?"checked":""); ?>>
                                    <label class="form-check-label mt-1" for="pinterest_status_1">
                                        <?php echo e(__('Enable')); ?>

                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="pinterest_status" value="0" id="pinterest_status_0"<?php echo e(get_option("pinterest_status", 0)==0?"checked":""); ?>>
                                    <label class="form-check-label mt-1" for="pinterest_status_0">
                                        <?php echo e(__('Disable')); ?>

                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Mode')); ?></label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="pinterest_mode" value="1" id="pinterest_mode_1" <?php echo e(get_option("pinterest_mode", 1)==1?"checked":""); ?>>
                                    <label class="form-check-label mt-1" for="pinterest_mode_1">
                                        <?php echo e(__('Live')); ?>

                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="pinterest_mode" value="0" id="pinterest_mode_0"<?php echo e(get_option("pinterest_mode", 0)==0?"checked":""); ?>>
                                    <label class="form-check-label mt-1" for="pinterest_mode_0">
                                        <?php echo e(__('Sandbox')); ?>

                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label"><?php echo e(__('App ID')); ?></label>
                            <input class="form-control" name="pinterest_client_id" id="pinterest_client_id" type="text" value="<?php echo e(get_option("pinterest_client_id", "")); ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label"><?php echo e(__('App Secret')); ?></label>
                            <input class="form-control" name="pinterest_client_secret" id="pinterest_client_secret" type="text" value="<?php echo e(get_option("pinterest_client_secret", "")); ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label"><?php echo e(__('Scopes')); ?></label>
                            <input class="form-control" name="pinterest_scopes" id="pinterest_scopes" type="text" value="<?php echo e(get_option("pinterest_scopes", "user_accounts:read,pins:read,pins:read_secret,pins:write,pins:write_secret,boards:read,boards:read_secret,boards:write")); ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            <div>
                                <span class="fw-6"><?php echo e(__("Callback URL: ")); ?> </span>
                                <a href="<?php echo e(url_app("pinterest/board")); ?>" target="_blank"><?php echo e(url_app("pinterest/board")); ?></a>
                            </div>

                            <div>
                                <span class="fw-6"><?php echo e(__("Create Pinterest app: ")); ?></span> 
                                <a href="https://developers.pinterest.com/apps/connect/" target="_blank">https://developers.pinterest.com/apps/connect/</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-dark b-r-10 w-100">
                <?php echo e(__('Save changes')); ?>

            </button>
        </div>

    </form>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppChannelPinterestBoards/resources/views/settings.blade.php ENDPATH**/ ?>