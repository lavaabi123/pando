<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('Users')).'','description' => ''.e(__('Easily Manage and Monitor All Platform Users')).'','count' => $total_user] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sub-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\SubHeader::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <a class="btn btn-primary btn-sm" href="<?php echo e(url_admin("users/export")); ?>">
            <span><i class="fa-light fa-file-export"></i></span>
            <span><?php echo e(__('Export')); ?></span>
        </a>
        <a class="btn btn-dark btn-sm" href="<?php echo e(url_admin("users/create")); ?>">
            <span><i class="fa-light fa-user-plus"></i></span>
            <span><?php echo e(__('Add user')); ?></span>
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
<div class="container">

    <div class="row">
        <div class="col-md-4">
            <div class="card border-gray-200 mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-16">
                        <div class="size-45 fs-20 text-primary d-flex align-items-center justify-content-center bg-primary-100 b-r-10">
                            <i class="fa-light fa-user-check"></i>
                        </div>
                        <div class="text-end">
                            <div class="fs-12 text-gray-600"><?php echo e(__("Active")); ?></div>
                            <div class="fw-7 fs-16"><?php echo e(Number::format($total_active_user)); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-gray-200 mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-16">
                        <div class="size-45 fs-20 text-warning d-flex align-items-center justify-content-center bg-warning-100 b-r-10">
                            <i class="fa-light fa-user-check"></i>
                        </div>
                        <div class="text-end">
                            <div class="fs-12 text-gray-600"><?php echo e(__("Inactive")); ?></div>
                            <div class="fw-7 fs-16"><?php echo e(Number::format($total_inactive_user)); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-gray-200 mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-16">
                        <div class="size-45 fs-20 text-danger d-flex align-items-center justify-content-center bg-danger-100 b-r-10">
                            <i class="fa-light fa-user-check"></i>
                        </div>
                        <div class="text-end">
                            <div class="fs-12 text-gray-600"><?php echo e(__("Banned")); ?></div>
                            <div class="fw-7 fs-16"><?php echo e(Number::format($total_banned_user)); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
<?php $__env->startComponent('components.datatable', [ "Datatable" => $Datatable ]); ?> <?php echo $__env->renderComponent(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?> 
    <?php $__env->startComponent('components.datatable_script', [ "Datatable" => $Datatable, "edit_popup" => "" , "edit_url" => "" , "column_actions" => false, "column_status" => true]); ?> <?php echo $__env->renderComponent(); ?>
    <script type="text/javascript">
        columnDefs = columnDefs.concat([
            {
                targets: 'fullname:name',
                orderable: true,
                render: function (data, type, row) {
                    return `
                        <div class="d-flex gap-8 align-items-center">
                            <a class="size-40 size-child border b-r-6 text-gray-800" href="<?php echo e(module_url("edit")); ?>/${row.id_secure}">
                                <img data-src="${ Main.mediaURL('<?php echo e(Media::url()); ?>', row.avatar) }" src="${ Main.text2img(row.fullname, '000') }" class="b-r-6 lazyload" onerror="this.src='${ Main.text2img(row.fullname, '000') }'">
                            </a>
                            <div class="text-start lh-1 text-truncate">
                                <div class="fw-5 text-gray-900 text-truncate">
                                    <div class="text-truncate">
                                        <a class="text-gray-800 text-hover-primary" href="<?php echo e(module_url("edit")); ?>/${row.id_secure}">
                                            ${row.fullname}
                                        </a>
                                    </div>
                                    <div class="text-truncate text-gray-500 fs-12">
                                        ${row.email}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                }
            },
            {
                targets: 'role:name',
                orderable: true,
                className: 'min-w-80 text-danger text-center',
                render: function (data, type, row){
                
                    if (row.role == 2) {
                        return `<span class="text-warning fs-18" title="<?php echo e(__("Admin")); ?>"><i class="fa-duotone fa-solid fa-crown"></i></span>`;
                    }else{
                        return `<span class="text-gray-500 fs-18" title="<?php echo e(__("User")); ?>"><i class="fa-duotone fa-user"></i></span>`;
                    }
                    
                }
            },
            {
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-end',
            render: function (data, type, row) {
                return `
                    <div class="dropdown">
                        <button class="btn btn-light btn-active-light-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo e(__("Actions")); ?>

                        </button>
                        <ul class="dropdown-menu border-1 border-gray-300 w-auto max-w-180 min-w-150">
                            <li class="mx-2">
                                <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6" href="<?php echo e(module_url("edit")); ?>/${row.id_secure}">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-pen-to-square"></i></span>
                                    <span><?php echo e($edit_text ?? __("Edit")); ?></span>
                                </a>
                            </li>
                            <li class="mx-2">
                                <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6" href="<?php echo e(url("auth/view-as-user")); ?>/${row.id_secure}">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-eye"></i></span>
                                    <span><?php echo e($edit_text ?? __("View As User")); ?></span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if(!isset($delete) || $delete): ?>
                            <li class="mx-2">
                                <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.id_secure}" href="<?php echo e(module_url("destroy")); ?>" data-confirm="<?php echo e(__("Are you sure you want to delete this item?")); ?>" data-call-success="Main.DataTable_Reload('#<?php echo e($Datatable['element']); ?>')">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                    <span><?php echo e(__("Delete")); ?></span>
                                </a>
                            </li>
                            <?php endif; ?>        
                        </ul>
                    </div>
                `;
            },
        },          
        ]);
        var dtConfig = {
            columns: <?php echo json_encode($Datatable['columns'] ?? []); ?>,
            lengthMenu: <?php echo json_encode($Datatable['lengthMenu'] ?? []); ?>,
            order: <?php echo json_encode($Datatable['order'] ?? []); ?>,
            columnDefs: <?php echo json_encode($Datatable['columnDefs'] ?? []); ?>

        };

        dtConfig.columnDefs = dtConfig.columnDefs.concat(columnDefs);
        var DataTable = Main.DataTable("#<?php echo e($Datatable['element']); ?>", dtConfig);
        DataTable.columns(['email:name', 'avatar:name']).visible(false);
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AdminUsers/resources/views/index.blade.php ENDPATH**/ ?>