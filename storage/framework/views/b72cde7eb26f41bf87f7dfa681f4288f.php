

<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('Team Collaboration')).'','description' => ''.e(__('Collaborate seamlessly with teams through shared access.')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
<div class="container px-4">

    <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5 max-w-400 mx-auto">
        <span class="fs-70 mb-3 text-primary">
            <i class="fa-light fa-users"></i>
        </span>
        <div class="fw-semibold fs-5 mb-2 text-gray-800">
            <?php echo e(__('Set your team name')); ?>

        </div>
        <div class="text-body-secondary mb-4 text-center">
            <?php echo e(__('Please choose a clear and unique name for your team. This will make it easier for everyone to collaborate and share resources together.')); ?>

        </div>
        <form action="<?php echo e(route('app.teams.save_team_name')); ?>" method="POST" class="actionForm wp-100">
            <div class="mb-3">
                <input type="text" name="team_name" class="form-control form-control-lg text-center" placeholder="<?php echo e(__('Enter your team name')); ?>" value="<?php echo e(old('team_name', $team->name)); ?>" required>
            </div>
            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-dark btn-lg px-5 wp-100">
                    <?php echo e(__('Save changes')); ?>

                </button>
            </div>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppTeams/resources/views/set_team_name.blade.php ENDPATH**/ ?>