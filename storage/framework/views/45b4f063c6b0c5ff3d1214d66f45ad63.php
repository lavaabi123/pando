<?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="position-relative bg-light border b-r-10 p-4 w-100">
            <div class="d-flex align-items-top justify-content-between mb-2">
                <div class="fs-28 text-warning">
                    <?php if($member->user->fullname ?? 0): ?>
                        <div class="size-40 size-child">
                            <img src="<?php echo e(Media::url($member->user->avatar)); ?>" class="b-r-50 border">
                        </div>
                    <?php else: ?>
                        <div class="size-40 size-child">
                            <img src="<?php echo e(text2img($member->pending)); ?>" class="b-r-50 border">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="position-relative">
                    <div class="dropdown">
                        <a class="dropdown-toggle dropdown-arrow-hide text-gray-900" data-bs-toggle="dropdown" data-bs-animation="fade" href="javascript:void(0);">
                            <i class="fa-light fa-grid-2"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-2 border-1 border-gray-300 max-w-80 w-100">
                            
                            <li>
                                <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem"
                                    href="<?php echo e(module_url('update')); ?>"
                                    data-id="<?php echo e($member->id_secure); ?>"
                                    data-popup="updateMemberModal">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-pen-to-square"></i></span>
                                    <span class="fw-5"><?php echo e(__("Edit")); ?></span>
                                </a>
                            </li>
                            <?php if($member->status == 0): ?>
                                <li>
                                    <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem"
                                        href="<?php echo e(module_url('resend-invite')); ?>"
                                        data-id="<?php echo e($member->id_secure); ?>">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-envelope"></i></span>
                                        <span class="fw-5"><?php echo e(__("Resend Invite")); ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem"
                                    href="<?php echo e(module_url('destroy')); ?>"
                                    data-id="<?php echo e($member->id_secure); ?>"
                                    data-call-success="Main.ajaxScroll(true)">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can"></i></span>
                                    <span class="fw-5"><?php echo e(__("Delete")); ?></span>
                                </a>
                            </li>
                        </ul>       
                    </div>
                </div>
            </div>
            <div class="fw-5 fs-14 text-gray-800 mb-1 text-truncate">
                <?php echo e($member->user->fullname ?? $member->user->username ?? $member->pending ?? 'N/A'); ?>

                <div class="fs-11">
                    <?php echo e($member->user->email ?? $member->pending ?? ''); ?>

                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between fw-5">
                <div class="fs-12 d-flex align-items-center gap-8">
                    <span>
                        <?php if($member->status == 0): ?>
                            <span class="text-primary"><?php echo e(__("Pending")); ?></span>
                        <?php elseif($member->status == 1): ?>
                            <span class="text-success"><?php echo e(__("Active")); ?></span>
                        <?php else: ?>
                            <span class="text-gray-600"><?php echo e(__("Unknown")); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="d-inline-block size-4 b-r-50 bg-gray-400"></span> 
                    <span class="text-gray-600">
                        <?php echo e(is_array($member->permissions) ? count($member->permissions) : (is_string($member->permissions) ? count(json_decode($member->permissions, true) ?? []) : 0)); ?> <?php echo e(__("Permissions")); ?>

                    </span>
                </div>
                
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if( $members->Total() == 0 && $members->currentPage() == 1): ?>
<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
    <span class="fs-70 mb-3 text-primary">
        <i class="fa-light fa-users"></i>
    </span>
    <div class="fw-semibold fs-5 mb-2 text-gray-900">
        <?php echo e(__('No Team Members Yet')); ?>

    </div>
    <div class="text-body-secondary mb-4 text-center max-w-500">
        <?php echo e(__('Invite teammates to collaborate, assign roles, and work together efficiently. Shared access makes teamwork seamless and organized.')); ?>

    </div>
    <a class="btn btn-dark actionItem" href="<?php echo e(module_url('invite')); ?>" data-popup="inviteModal" >
        <i class="fa-light fa-user-plus me-1"></i> <?php echo e(__('Invite member')); ?>

    </a>
</div>
<?php endif; ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppTeams/resources/views/list.blade.php ENDPATH**/ ?>