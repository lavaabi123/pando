

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make("appprofile::partials.profile-header", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="container max-w-800 pb-5 pt-5">
        <div class="mb-5">
            <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('My Billings')).'','description' => ''.e(__('View your payment history and download invoices.')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

        <div class="card mb-4">
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 fs-13">
                        <thead class="table-light">
                            <tr>
                                <th class="btl-r-15"><?php echo e(__('Invoice #')); ?></th>
                                <th><?php echo e(__('Date')); ?></th>
                                <th><?php echo e(__('Amount')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th class="text-end btr-r-15"><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $billings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="fw-bold text-uppercase"><?php echo e($bill->id_secure); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($bill->created)->format('d/m/Y')); ?></td>
                                    <td>
                                        <span class="fw-bold">
                                            <?php echo e(number_format($bill->amount)); ?>

                                            <span class="text-uppercase"><?php echo e($bill->currency); ?></span>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($bill->status == 1): ?>
                                            <span class="badge badge-outline badge-pill badge-sm badge-success"><?php echo e(__('Paid')); ?></span>
                                        <?php elseif($bill->status == 2): ?>
                                            <span class="badge badge-outline badge-pill badge-sm badge-primary"><?php echo e(__('Pending')); ?></span>
                                        <?php elseif($bill->status == 0): ?>
                                            <span class="badge badge-outline badge-pill badge-sm badge-danger"><?php echo e(__('Rejected')); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo e(ucfirst($bill->status)); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?php echo e(route('app.profile.billing.download_invoice', $bill->id_secure)); ?>" class="btn btn-sm b-r-50 btn-outline btn-primary me-2">
                                            <i class="fa-light fa-file-arrow-down"></i> <?php echo e(__('Invoice')); ?>

                                        </a>
                                        <a href="<?php echo e(route('app.profile.billing.show_invoice', $bill->id_secure)); ?>" class="btn btn-sm b-r-50 btn-light">
                                            <i class="fa-light fa-eye"></i> <?php echo e(__('Detail')); ?>

                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="fa-light fa-file-invoice-dollar fa-2x mb-2"></i>
                                        <div><?php echo e(__('No billing history yet.')); ?></div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
   

                <?php if($billings->hasPages()): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <?php echo e($billings->links('components.pagination')); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .card {border-radius: 1.5rem;}
        .table thead th {border-top: none;}
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppProfile/resources/views/billing.blade.php ENDPATH**/ ?>