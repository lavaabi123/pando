<?php if($folder): ?>
<input type="radio" class="d-none form-check-input ajax-scroll-filter" name="folder_id" value="<?php echo e($folder->id_secure); ?>" checked>
<nav aria-label="breadcrumb" class="mb-0">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
        <label class="fs-12 text-primary fw-5 text-hover-primary pointer" id="breadcrumb_folder_0">
            <?php echo e(__("Root Folder")); ?>

            <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="0" id="breadcrumb_folder_0">
        </label>
    </li>
    <?php $__currentLoopData = $parent_folders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="breadcrumb-item">
            <label class="fs-12 text-primary fw-5 text-hover-primary-900 pointer" id="breadcrumb_folder_<?php echo e($parent->id_secure); ?>">
                <?php echo e($parent->name); ?>

                <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="<?php echo e($parent->id_secure); ?>" id="breadcrumb_folder_<?php echo e($parent->id_secure); ?>">
            </label>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <li class="breadcrumb-item fs-12 text-gray-400 active" aria-current="page"><?php echo e($folder->name); ?></li>
  </ol>
</nav>
<?php endif; ?>

<?php if($folders): ?>
    <?php $__currentLoopData = $folders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-4 px-2">
        <div class="ratio ratio-1x1 mb-3">
            <label class="d-flex flex-column flex-fill w-100 bg-light border b-r-10 w-100 pointer mb-3" for="folder_<?php echo e($value->id_secure); ?>">
                <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="<?php echo e($value->id_secure); ?>" id="folder_<?php echo e($value->id_secure); ?>">
                <div class="d-flex flex-fill align-items-center justify-content-center p-2 bg-warning-100">
                    <div class="fs-30 text-warning">
                        <i class="fa-light fa-folder-open"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-auto p-1 gap-8 position-relative zIndex-2 border-top">
                    <div class="text-truncate">
                        <div class="fs-9 text-gray-800 fw-5 lh-sm text-truncate"><?php echo e($value->name); ?></div>
                        <div class="fs-8 d-flex align-items-center gap-8 text-gray-600 lh-sm text-truncate">
                            <span><?php echo e(sprintf("%d Files", $value->file_count)); ?> </span>
                            <span class="d-inline-block size-4 b-r-50 bg-gray-400"></span> 
                            <span><?php echo e(sprintf("%d Folder", $value->folder_count)); ?></span>
                        </div>
                    </div>

                    <div>
                        <div class="btn-group position-static">
                            <div class="dropdown-toggle dropdown-arrow-hide text-gray-900 fs-12" data-bs-toggle="dropdown" aria-expanded="true">
                                <i class="fa-light fa-grid-2"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-1 w-100 max-w-180 min-w-120">
                                <li>
                                    <a class="dropdown-item px-2 p-t-2 p-b-2 rounded d-flex gap-8 fw-5 fs-13 actionMultiItem" href="<?php echo e(url_admin("users/status/active")); ?>" data-call-success="">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-edit"></i></span>
                                        <span ><?php echo e(__('Edit Image')); ?></span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item px-2 p-t-2 p-b-2 rounded d-flex gap-8 fw-5 fs-13 actionMultiItem" href="<?php echo e(url_admin("users/destroy")); ?>" data-call-success="">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can"></i></span>
                                        <span><?php echo e(__('Delete')); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </label>
        </div>
        
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<?php if($files->Total() > 0): ?>
    <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

    <?php
    $detectType = Media::detectFileIcon($value->detect);
    ?>

    <div class="col-4 px-2">
        <div class="file-item w-100 ratio ratio-1x1 min-h-80 mb-3 border b-r-6" data-id="file_<?php echo e($value->id_secure); ?>" data-name="medias" data-file="<?php echo e(Media::url($value->file)); ?>" data-type="<?php echo e($value->detect); ?>">
            <label class="d-flex flex-column flex-fill" for="<?php echo e($value->id_secure); ?>">
                <div class="position-absolute r-6 t-6 zIndex-3">
                    <div class="form-check form-check-sm">
                        <input class="form-check-input" name="id[]" type="checkbox" value="<?php echo e($value->file); ?>" id="file_<?php echo e($value->id_secure); ?>">
                    </div>
                </div>

                <div class="d-flex flex-fill align-items-center justify-content-center overflow-y-auto bg-cover position-relative btl-r-6 btr-r-6 file-item-media text-<?php echo e($detectType['color']); ?> bg-<?php echo e($detectType['color']); ?>-100" style="background-image: url( <?php echo e(Media::url($value->file)); ?> );">
                    <?php if($value->detect != "image"): ?>
                    <div class="fs-30">
                        <i class="<?php echo e($detectType['icon']); ?>"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-auto p-1 gap-8 position-relative zIndex-4 file-info border-top">
                    <div class="text-truncate">
                        <div class="fs-9 text-gray-800 fw-5 lh-sm text-truncate"><?php echo e($value->name); ?></div>
                        <div class="fs-8 text-gray-600 lh-sm text-truncate"><?php echo e(Number::fileSize($value->size)); ?></div>
                    </div>

                    <div>
                        <div class="btn-group position-static">
                            <div class="dropdown-toggle dropdown-arrow-hide text-gray-900 fs-12" data-bs-toggle="dropdown" aria-expanded="true">
                                <i class="fa-light fa-grid-2"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-1 w-100 max-w-180 min-w-120">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('appfiles.image_editor')): ?>
                                <li>
                                    <button type="button" class="dropdown-item px-2 p-t-4 p-b-4 rounded d-flex gap-8 fw-5 fs-13 editImage" data-file="<?php echo e(Media::url($value->file)); ?>" data-id="<?php echo e($value->id_secure); ?>">
                                        <span class="size-16 me-0 text-center"><i class="fa-light fa-edit"></i></span>
                                        <span ><?php echo e(__('Edit Image')); ?></span>
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item px-2 p-t-4 p-b-4 rounded d-flex gap-8 fw-5 fs-13 actionItem" href="<?php echo e(url_app("files/destroy")); ?>" data-id="<?php echo e($value->file); ?>" data-call-success="Main.ajaxScroll(true)">
                                        <span class="size-16 me-0 text-center"><i class="fa-light fa-trash-can"></i></span>
                                        <span><?php echo e(__('Delete')); ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </label>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
	<div class="empty mt-100 mb-100"></div>
<?php endif; ?>

<?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppFiles/resources/views/mini_list.blade.php ENDPATH**/ ?>