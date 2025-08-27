<?php $__env->startSection('header_center'); ?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <button class="size-40 px-2 navbar-toggler border shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-light fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav py-2">
                <?php if (\Illuminate\Support\Facades\Blade::check('canany', 'apppublishingcampaigns', 'apppublishinglabels')): ?>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-6 text-uppercase fs-12  <?php echo e(Request::segment(3)==""?"text-primary":""); ?>" aria-current="page" href="<?php echo e(url_app("publishing")); ?>">
                        <?php echo e(__("Schedules")); ?>

                    </a>
                </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check("apppublishingcampaigns")): ?>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-6 text-uppercase fs-12 <?php echo e(Request::segment(3)=="campaigns"?"text-primary":""); ?>" aria-current="page" href="<?php echo e(url_app("publishing/campaigns")); ?>">
                        <?php echo e(__("Campaigns")); ?>

                    </a>
                </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check("apppublishinglabels")): ?>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-6 text-uppercase fs-12 <?php echo e(Request::segment(3)=="labels"?"text-primary":""); ?>" aria-current="page" href="<?php echo e(url_app("publishing/labels")); ?>">
                        <?php echo e(__("Labels")); ?>

                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link px-3 py-2 fw-6 text-uppercase fs-12 <?php echo e(Request::segment(3)=="draft"?"text-primary":""); ?>" aria-current="page" href="<?php echo e(url_app("publishing/draft")); ?>">
                        <?php echo e(__("Draft")); ?>

                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php $__env->stopSection(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppPublishing/resources/views/header_center.blade.php ENDPATH**/ ?>