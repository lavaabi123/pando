

<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('Edit User')).'','description' => ''.e(__('Update existing user information and privileges')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sub-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\SubHeader::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <a class="btn btn-primary btn-sm d-flex align-items-center justify-content-between" href="<?php echo e(url_admin("users")); ?>">
            <span><i class="fa-light fa-list"></i></span>
            <span>
                <?php echo e(__('User list')); ?>

            </span>
        </a>
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

    <div class="row">
        <div class="col-md-12">
            <form class="actionForm" action="<?php echo e(url_admin("users/update_info")); ?>" data-redirect="<?php echo e(url_admin("users")); ?>">
                <input name="id_secure" type="hidden" value="<?php echo e($result->id_secure); ?>">
                <div class="card mt-5">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?php echo e(__('User information')); ?>

                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row flex-md-column gap-32">
                            <div class="max-w-220 min-w-220">
                                <div class="p-3 border-1 b-r-6">
                                    <?php echo $__env->make('appfiles::block_upload', [
                                        "large" => true,
                                        "id" => "avatar",
                                        "name" => __("Upload Avatar"),
                                        "value" => Media::url($result->avatar)
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
                            </div>
                            <div class="flex-fill">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label class="form-label"><?php echo e(__('Role')); ?></label>
                                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="radio" name="role" id="role_1" value="1" <?php if($result->role == 1): echo 'checked'; endif; ?>>
                                                    <label class="form-check-label mt-1">
                                                        <?php echo e(__('User')); ?>

                                                    </label>
                                                </div>
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="radio" name="role" id="role_2" value="2" <?php if($result->role == 2): echo 'checked'; endif; ?>>
                                                    <label class="form-check-label mt-1">
                                                        <?php echo e(__('Admin')); ?>

                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label class="form-label"><?php echo e(__('Status')); ?></label>
                                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="radio" name="status" value="2" id="status_2" <?php if($result->status == 2): echo 'checked'; endif; ?>>
                                                    <label class="form-check-label mt-1" for="status_2">
                                                        <?php echo e(__('Active')); ?>

                                                    </label>
                                                </div>
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="radio" name="status" value="1" id="status_1" <?php if($result->status == 1): echo 'checked'; endif; ?>>
                                                    <label class="form-check-label mt-1" for="status_1">
                                                        <?php echo e(__('Inactive')); ?>

                                                    </label>
                                                </div>
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="radio" name="status" value="0" id="status_0" <?php if($result->status == 0): echo 'checked'; endif; ?>>
                                                    <label class="form-check-label mt-1" for="status_0">
                                                        <?php echo e(__('Banned')); ?>

                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="fullname" class="form-label"><?php echo e(__('Fullname')); ?></label>
                                            <div class="form-control">
                                                <i class="fa-light fa-user"></i>
                                                <input placeholder="<?php echo e(__('Fullname')); ?>" name="fullname" id="fullname" type="text" value="<?php echo e($result->fullname); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="username" class="form-label"><?php echo e(__('Username')); ?></label>
                                            <div class="form-control">
                                                <i class="fa-light fa-user"></i>
                                                <input placeholder="<?php echo e(__('Username')); ?>" name="username" id="username" type="text" value="<?php echo e($result->username); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="email" class="form-label"><?php echo e(__('Email')); ?></label>
                                            <div class="form-control">
                                                <i class="fa-light fa-envelope"></i>
                                                <input placeholder="<?php echo e(__('Email')); ?>" name="email" id="email" type="text" value="<?php echo e($result->email); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="timezone" class="form-label"><?php echo e(__('Timezone')); ?></label>
                                            <div class="form-control pe-0">
                                                <i class="fa-light fa-clock"></i>
                                                <select class="form-select" name="timezone" id="timezone">
                                                    <option value=""><?php echo e(__('Select timezone')); ?></option>
                                                    <?php $__currentLoopData = tz_list(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($key); ?>" <?php if( $result->timezone == $key ): echo 'selected'; endif; ?> ><?php echo e($value); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer justify-content-end">
                        <button type="submit" class="btn btn-dark">
                            <?php echo e(__('Save changes')); ?>

                        </button>
                    </div>
                </div>

            </form>
        </div>
        <div class="col-md-6">
            <form class="actionForm hp-100" action="<?php echo e(url_admin("users/change_password")); ?>" data-redirect="<?php echo e(url_admin("users")); ?>">
                <input name="id_secure" type="hidden" value="<?php echo e($result->id_secure); ?>">
                <div class="card mt-4 hp-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?php echo e(__('Change password')); ?>

                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="msg-errors"></div>
                        <div class="d-flex flex-column flex-lg-row flex-md-column gap-32">
                            <div class="flex-fill">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label for="password" class="form-label"><?php echo e(__('Password')); ?></label>
                                            <div class="w-100">
                                                <div class="form-control">
                                                    <i class="fa-light fa-key"></i>
                                                    <input placeholder="<?php echo e(__('Password')); ?>" name="password" id="password" type="password" autocomplete="on" value="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label for="confirm_new_password" class="form-label"><?php echo e(__('Confirm password')); ?></label>
                                            <div class="w-100">
                                                <div class="form-control">
                                                    <i class="fa-light fa-key"></i>
                                                    <input placeholder="<?php echo e(__('Confirm password')); ?>" name="password_confirmation" id="password_confirmation" autocomplete="on" type="password" value="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer justify-content-end">
                        <button type="submit" class="btn btn-dark">
                            <?php echo e(__('Save changes')); ?>

                        </button>
                    </div>
                </div>

            </form>

        </div>
        <div class="col-md-6">

            <form class="actionForm hp-100" action="<?php echo e(url_admin("users/update_plan")); ?>" data-redirect="<?php echo e(url_admin("users")); ?>">
                <input name="id_secure" type="hidden" value="<?php echo e($result->id_secure); ?>">
                <div class="card mt-4 hp-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?php echo e(__('Plan')); ?>

                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="msg-errors"></div>
                        <div class="d-flex flex-column flex-lg-row flex-md-column gap-32">
                            <div class="flex-fill">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label for="plan" class="form-label mb-1"><?php echo e(__('Plan')); ?></label>
                                            <div class="text-gray-600 fs-12 mb-2"><?php echo e(__('All previous permissions will be removed when you switch to a new plan.')); ?></div>
                                            <div class="form-control pe-0">
                                                <i class="fa-light fa-cubes"></i>
                                                <select class="form-select" name="plan">
                                                    <option value="0"><?php echo e(__('Select plan')); ?></option>
                                                    <?php if($plans): ?>
                                                        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                                            <?php

                                                                switch ($plan->type) {
                                                                    case 2:
                                                                        $type = __("Yearly");
                                                                        break;

                                                                    case 3:
                                                                        $type = __("Lifetime");
                                                                        break;
                                                                    
                                                                    default:
                                                                        $type = __("Monthly");
                                                                        break;
                                                                }

                                                            ?>

                                                            <option value="<?php echo e($plan->id); ?>" <?php if($plan->id == $result->plan_id): echo 'selected'; endif; ?> >[<?php echo e($type); ?>] <?php echo e($plan->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label for="expiration_date" class="form-label mb-1"><?php echo e(__('Expiration date')); ?></label>
                                            <div class="text-gray-600 fs-12 mb-2"><?php echo e(__('Set the value to -1 for unlimited')); ?></div>
                                            <div class="input-group">
                                                <div class="form-control">
                                                    <i class="fa-light fa-calendar-clock"></i>
                                                    <input placeholder="<?php echo e(__('Expiration date')); ?>" name="expiration_date" class="dateBtn" id="expiration_date" type="text" value="<?php echo e(date_show($result->expiration_date)); ?>">
                                                    
                                                </div>
                                                <button type="button" class="btn btn-icon btn-light selectDate"><i class="fa-light fa-calendar-days"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer justify-content-end">
                        <button type="submit" class="btn btn-dark">
                            <?php echo e(__('Save changes')); ?>

                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
    
</div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AdminUsers/resources/views/edit.blade.php ENDPATH**/ ?>