<?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4 col-xxl-3 mb-4">
    <div class="card shadow-none border rounded-3">
        <div class="card-body px-3">
            <div class="d-flex flex-grow-1 align-items-top gap-8">
                
                <div class="text-gray-600 size-40 min-w-40 d-flex align-items-center justify-content-between position-relative">
                    <a href="<?php echo e(url('app/teams/'.$team->id_secure)); ?>" class="text-gray-900 text-hover-primary">
                        <img  src="<?php echo e(Media::url($team->ownerUser->avatar)); ?>" class="b-r-100 w-full h-full border-1">
                    </a>
                </div>
                
                <div class="flex-grow-1 fs-14 fw-5 text-truncate">
                    <div class="text-truncate">
                        <?php echo e($team->name ?? $team->ownerUser->fullname ?? __('No Name')); ?>

                    </div>
                    <div class="fs-12 text-gray-600 text-truncate">
                        <?php echo e($team->description ?? __('Joined Team')); ?>

                    </div>
                </div>
                <div class="d-flex fs-14">
                    <input class="form-check-input checkbox-item"
                           type="checkbox"
                           name="id[]"
                           value="<?php echo e($team->id_secure); ?>"
                           id="team_<?php echo e($team->id_secure); ?>">
                </div>
            </div>
        </div>
        <div class="card-footer fs-12 d-flex justify-content-center gap-8">
            <a href="<?php echo e(route("app.teams.joined.open_team")); ?>" data-id="<?php echo e($team->id_secure); ?>" class="d-flex flex-fill gap-8 align-items-center justify-content-center text-gray-900 text-hover-primary fw-5 actionItem">
                <i class="fa-light fa-eye text-success"></i>
                <span><?php echo e(__("Open Team")); ?></span>
            </a>
            <div class="text-gray-400 h-20 w-1 bg-gray-200"></div>
            <a href="<?php echo e(url('app/teams/joined/leave')); ?>" class="d-flex flex-fill gap-8 align-items-center justify-content-center text-gray-900 text-hover-primary fw-5 actionItem" data-confirm="<?php echo e(__("Are you sure you want to leave the team? You will lose access to all resources shared by this team.")); ?>" data-id="<?php echo e($team->id_secure); ?>" data-id="<?php echo e($team->id_secure); ?>" data-call-success="Main.ajaxScroll(true);">
                <i class="fa-light fa-sign-out text-danger"></i>
                <span><?php echo e(__("Leave Team")); ?></span>
            </a>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if( $teams->Total() == 0 && $teams->currentPage() == 1): ?>
<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
    <span class="fs-1 mb-3 text-primary">
        <i class="fa-light fa-people-group"></i>
    </span>
    <div class="fw-semibold fs-5 mb-2 text-gray-900">
        <?php echo e(__('No Joined Teams Yet')); ?>

    </div>
    <div class="text-body-secondary mb-4 text-center max-w-500">
        <?php echo e(__('Access teams you’re a member of here. Join a team to start collaborating and sharing resources.')); ?>

    </div>
    <a href="<?php echo e(url_app("teams")); ?>" class="btn btn-dark">
        <i class="fa-light fa-users-medical me-1"></i> <?php echo e(__('Go to you Team')); ?>

    </a>
</div>
<?php endif; ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppTeamJoined/resources/views/list.blade.php ENDPATH**/ ?>