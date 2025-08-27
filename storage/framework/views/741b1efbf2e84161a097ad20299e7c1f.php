<?php if($current_page == 1): ?>
    <?php if($folder): ?>
    <input type="radio" class="d-none form-check-input ajax-scroll-filter" name="folder_id" value="<?php echo e($folder->id_secure); ?>" checked>
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <label class="fs-14 text-primary fw-5 text-hover-primary pointer" id="breadcrumb_folder_0">
                <?php echo e(__("Root Folder")); ?>

                <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="0" id="breadcrumb_folder_0">
            </label>
        </li>
        <?php $__currentLoopData = $parent_folders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="breadcrumb-item">
                <label class="fs-14 text-primary fw-5 text-hover-primary-900 pointer" id="breadcrumb_folder_<?php echo e($parent->id_secure); ?>">
                    <?php echo e($parent->name); ?>

                    <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="<?php echo e($parent->id_secure); ?>" id="breadcrumb_folder_<?php echo e($parent->id_secure); ?>">
                </label>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <li class="breadcrumb-item fs-14 text-gray-400 active" aria-current="page"><?php echo e($folder->name); ?></li>
      </ol>
    </nav>
    <?php endif; ?>

    <div class="col-md-12 <?php echo e(!$folder?"mt-5":""); ?>">
        <?php if($folders->count() > 0): ?>
        <div class="d-flex pb-3">
            <div class="fs-18 text-gray-900 fw-5"><?php echo e(__("Folder")); ?></div>  
        </div>
        <?php endif; ?>

        <?php if($folders): ?>
        <div class="row">
            <?php $__currentLoopData = $folders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <label class="position-relative bg-light border b-r-10 p-4 w-100 pointer" for="folder_<?php echo e($value->id_secure); ?>">
                    <div class="position-absolute r-10 t-10">
                        <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="<?php echo e($value->id_secure); ?>" id="folder_<?php echo e($value->id_secure); ?>">
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fs-28 text-warning">
                            <i class="fa-light fa-folder-open"></i>
                        </div>
                        <div class="position-relative">
                            <div class="dropdown dropdown-hover">
                                <a class="dropdown-toggle dropdown-arrow-hide text-gray-900" data-bs-toggle="dropdown" data-bs-animation="fade">
                                    <i class="fa-light fa-grid-2"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end right-0 p-2 border-1 border-gray-300 max-w-80 w-100">
                                    <li>
                                        <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem" href="<?php echo e(module_url("update_folder")); ?>" data-id="<?php echo e($value->id_secure); ?>" data-popup="updateFolderModal">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-pen-to-square"></i></span>
                                            <span class="fw-5"><?php echo e(__("Edit")); ?></span>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem" href="<?php echo e(module_url("destroy")); ?>" data-id="<?php echo e($value->id_secure); ?>" data-call-success="Main.ajaxScroll(true)">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can"></i></span>
                                            <span class="fw-5"><?php echo e(__("Delete")); ?></span>
                                        </a>
                                    </li>
                                </ul>       
                            </div>
                        </div>
                    </div>
                    <div class="fw-5 fs-14 text-gray-800 mb-1 text-truncate">
                        <?php echo e($value->name); ?>

                    </div>
                    <div class="d-flex align-items-center justify-content-between fw-5 text-gray-400">
                        <div class="fs-12 d-flex align-items-center gap-8">
                            <span><?php echo e(sprintf("%d Files", $value->file_count)); ?> </span>
                            <span class="d-inline-block size-4 b-r-50 bg-gray-400"></span> 
                            <span><?php echo e(sprintf("%d Folder", $value->folder_count)); ?></span>
                        </div>
                        <div class="fs-11"><?php echo e(Number::fileSize($value->total_size)); ?></div>
                    </div>
                </label>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

        <?php if($folders->count() > 0 && $files->count() > 0): ?>
        <div class="d-flex pb-3 pt-3">
            <div class="fs-18 text-gray-900 fw-5"><?php echo e(__("Files")); ?></div>   
        </div>
        <?php endif; ?>

    </div>

    <?php if($folders->count() == 0 && $files->count() == 0): ?>
        <div class="empty"></div>
    <?php endif; ?>

<?php endif; ?>

<?php if($files): ?>
    <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

    <?php
    $detectType = Media::detectFileIcon($value->detect);
    ?>

    <div class="col-lg-2 col-md-4 col-sm-6 col-6 mb-4">
        <div class="bg-light border b-r-10 position-relative">
            <label class="w-100 h-150 bg-cover border-bottom btl-r-10 btr-r-10 d-flex align-items-center justify-content-center text-<?php echo e($detectType['color']); ?> bg-<?php echo e($detectType['color']); ?>-100" for="file_<?php echo e($value->id_secure); ?>" style="background-image: url('<?php echo e(Media::url($value->file)); ?>');">
                <div class="position-absolute r-10 t-10">
                    <input class="form-check-input checkbox-item" type="checkbox" name="id[]" value="<?php echo e($value->id_secure); ?>" id="file_<?php echo e($value->id_secure); ?>">
                </div>

                <?php if($value->detect != "image"): ?>
                <div class="fs-40">
                    <i class="<?php echo e($detectType['icon']); ?>"></i>
                </div>
                <?php endif; ?>
            </label>
            <div class="px-2 py-1 d-flex align-items-center justify-content-between gap-8">
                <div class="text-truncate">
                    <div class="text-gray-800 text-truncate fw-5 fs-12"><?php echo e($value->name); ?></div>
                    <div class="text-gray-400 text-truncate fs-11"><?php echo e(Number::fileSize($value->size)); ?></div>
                </div>
                <div class="position-relative">
                    <div class="btn-group">
                        <a class="dropdown-toggle dropdown-arrow-hide text-gray-900" data-bs-toggle="dropdown" data-bs-animation="fade">
                            <i class="fa-light fa-grid-2"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end right-0 p-2 border-1 border-gray-300">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('appfiles.image_editor')): ?>
                            <li>
                                <button type="button" class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 editImage" data-file="<?php echo e(Media::url($value->file)); ?>" data-id="<?php echo e($value->id_secure); ?>">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-edit"></i></span>
                                    <span class="fw-5"><?php echo e(__("Edit Image")); ?></span>
                                </button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem" href="<?php echo e(module_url("destroy")); ?>" data-id="<?php echo e($value->id_secure); ?>" data-call-success="Main.ajaxScroll(true)">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can"></i></span>
                                    <span class="fw-5"><?php echo e(__("Delete")); ?></span>
                                </a>
                            </li>
                        </ul>       
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>




<?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppFiles/resources/views/list.blade.php ENDPATH**/ ?>