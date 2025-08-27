<?php
    $languages = Language::getLanguages();
?>



<?php $__env->startSection('content'); ?>

    <?php echo $__env->make("appprofile::partials.profile-header", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="container max-w-700 pb-5 pt-5">
        <div class="mb-5">
            <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('Your Profile')).'','description' => ''.e(__('Update your personal information and password. Your information is safe with us.')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
        </div>

        <form class="actionForm" action="<?php echo e(route('app.profile.update_profile')); ?>" enctype="multipart/form-data" data-redirect="">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="fw-5"><?php echo e(__("Update profile")); ?></div>
                </div>
                <div class="card-body py-5 px-4">
                    <div class="d-flex flex-column flex-lg-row gap-20 align-items-start">
                        
                        <div class="mb-4 mb-lg-0 text-center w-100" >
                            <?php echo $__env->make('appfiles::block_upload', [
                                "large" => true,
                                "id" => "avatar",
                                "name" => __("Upload Avatar"),
                                "value" => Media::url($user->avatar)
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>

                        <div class="flex-fill">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="fullname" class="form-label"><?php echo e(__('Fullname')); ?></label>
                                    <div class="input-group" >
                                        <span class="btn btn-input"><i class="fa-light fa-user"></i></span>
                                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo e($user->fullname); ?>" placeholder="<?php echo e(__('Fullname')); ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="username" class="form-label"><?php echo e(__('Username')); ?></label>
                                    <div class="input-group">
                                        <span class="btn btn-input"><i class="fa-light fa-at"></i></span>
                                        <input type="text" class="form-control" id="username" name="username"
                                               value="<?php echo e($user->username); ?>"
                                               placeholder="<?php echo e(__('Username')); ?>"
                                               <?php if(!get_option("auth_user_change_username_status", 0)): ?> disabled <?php endif; ?>>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="email" class="form-label"><?php echo e(__('Email')); ?></label>
                                    <div class="input-group">
                                        <span class="btn btn-input"><i class="fa-light fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="<?php echo e($user->email); ?>"
                                               placeholder="<?php echo e(__('Email')); ?>"
                                               <?php if(!get_option("auth_user_change_email_status", 0)): ?> disabled <?php endif; ?>>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="language" class="form-label"><?php echo e(__('Language')); ?></label>
                                    <div class="input-group" >
                                        <span class="btn btn-input"><i class="fa-light fa-globe"></i></span>
                                        <select class="form-select" name="language" id="language">
                                            <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($value->code); ?>" <?php echo e($user->language == $value->code ? 'selected' : ''); ?>>
                                                    <?php echo e($value->name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="timezone" class="form-label"><?php echo e(__('Timezone')); ?></label>
                                    <div class="input-group" >
                                        <span class="btn btn-input"><i class="fa-light fa-clock"></i></span>
                                        <select class="form-select" name="timezone" id="timezone">
                                            <?php $__currentLoopData = tz_list(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($key); ?>" <?php echo e($user->timezone==$key?"selected":""); ?> ><?php echo e($value); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark px-4 mt-4">
                                <?php echo e(__('Save changes')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        
        <form class="actionForm" action="<?php echo e(route('app.profile.change_password')); ?>" data-redirect="">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="fw-5"><?php echo e(__("Change password")); ?></div>
                </div>
                <div class="card-body py-5 px-5">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="current_password" class="form-label"><?php echo e(__('Current Password')); ?></label>
                            <div class="input-group">
                                <span class="btn btn-input"><i class="fa-light fa-lock"></i></span>
                                <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" placeholder="<?php echo e(__('Enter current password')); ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="password" class="form-label"><?php echo e(__('New Password')); ?></label>
                            <div class="input-group">
                                <span class="btn btn-input"><i class="fa-light fa-key"></i></span>
                                <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" placeholder="<?php echo e(__('New password')); ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="password_confirmation" class="form-label"><?php echo e(__('Confirm Password')); ?></label>
                            <div class="input-group">
                                <span class="btn btn-input"><i class="fa-light fa-key"></i></span>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password" placeholder="<?php echo e(__('Confirm new password')); ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-dark mt-1">
                                <?php echo e(__('Save password')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        
        <div class="text-center d-none">
            <button type="button" class="btn btn-outline btn-danger btn-lg w-100" onclick="confirmDeleteAccount()">
                <i class="fa-light fa-trash"></i> <?php echo e(__('Delete Account')); ?>

            </button>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Optional: Live preview avatar
    document.getElementById('file-upload')?.addEventListener('change', function(e){
        if(e.target.files && e.target.files[0]){
            const reader = new FileReader();
            reader.onload = function(ev){
                document.querySelector('img[alt="Avatar"]').src = ev.target.result;
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    function confirmDeleteAccount() {
        if (confirm("<?php echo e(__('Are you sure you want to delete your account? This action cannot be undone.')); ?>")) {
            // Make ajax request or redirect to delete route
            // window.location.href = "<?php echo e(url('profile/delete')); ?>";
        }
    }
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppProfile/resources/views/index.blade.php ENDPATH**/ ?>