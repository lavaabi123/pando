<?php $__env->startSection('form', json_encode([
    'method' => 'POST'
])); ?>

<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('Files')).'','description' => ''.e(__('Effortlessly organize, manage, and access your files')).'','count' => $total] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sub-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\SubHeader::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div class="d-flex">
            <div class="form-control form-control-sm">
                <span class="btn btn-icon">
                    <i class="fa-light fa-magnifying-glass"></i>
                </span>
                <input class="ajax-scroll-filter" name="keyword" placeholder="<?php echo e(__('Search')); ?>" type="text">
                <button class="btn btn-icon">
                    <div class="form-check form-check-sm mb-0">
                        <input class="form-check-input checkbox-all" id="select_all" type="checkbox">
                    </div>
                </button>
            </div>
        </div>
        <div class="d-flex">
            <div class="btn-group position-static">
                <button class="btn btn-outline btn-light btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
                    <i class="fa-light fa-filter"></i> <?php echo e(__("Filters")); ?>

                </button>
                <div class="dropdown-menu dropdown-menu-end border-1 border-gray-300 w-full max-w-250" data-popper-placement="bottom-end">
                    <div class="d-flex border-bottom px-3 py-2 fw-6 fs-16 gap-8">
                        <span><i class="fa-light fa-filter"></i></span>
                        <span><?php echo e(__("Filters")); ?></span>
                    </div>
                    <div class="p-3">
                        <div class="mb-3">
                            <label class="form-label"><?php echo e(__("File Type")); ?></label>
                            <select class="form-select ajax-scroll-filter" name="file_type">
                                <option value="-1"><?php echo e(__("All")); ?></option>
                                <option value="image"><?php echo e(__("Image")); ?></option>
                                <option value="video"><?php echo e(__("Video")); ?></option>
                                <option value="doc"><?php echo e(__("Document")); ?></option>
                                <option value="pdf"><?php echo e(__("PDF")); ?></option>
                                <option value="csv"><?php echo e(__("CSV")); ?></option>
                                <option value="other"><?php echo e(__("Other")); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex">
            <div class="btn-group position-static">
                <button class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
                    <i class="fa-light fa-grid-2"></i> <?php echo e(__("Actions")); ?>

                </button>
                <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-210" data-popper-placement="bottom-end">
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 text-truncate uploadFromURL actionItem" href="<?php echo e(module_url("upload_from_url")); ?>" data-id="" data-popup="uploadFileFromURLModal" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-link"></i></span>
                            <span class="text-truncate"><?php echo e(__("Upload From URL")); ?></span>
                        </a>
                    </li>
                    <?php if(get_option('file_google_drive_status') && Gate::allows('appfiles.google_drive')): ?>
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14"  id="google-drive-chooser" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-brands fa-google-drive"></i></span>
                            <span class="text-truncate"><?php echo e(__("Google Drive")); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if(get_option('file_dropbox_status') && Gate::allows('appfiles.dropbox')): ?>
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14" id="dropbox-chooser" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-brands fa-dropbox"></i></span>
                            <span class="text-truncate"><?php echo e(__("Dropbox")); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if(get_option('file_onedrive_status') && Gate::allows('appfiles.onedrive')): ?>
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14" id="onedrive-chooser" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-regular fa-cloud"></i></span>
                            <span class="text-truncate"><?php echo e(__("OneDrive")); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if(get_option('file_addobe_express_status') && Gate::allows('appfiles.image_editor')): ?>
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14" id="adobe-express" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-wand-magic"></i></span>
                            <span class="text-truncate"><?php echo e(__("Adobe Express")); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if(Gate::allows('appmediasearch')): ?>
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" href="<?php echo e(route("app.search_media.popup_search")); ?>" data-popup="searchMediaModel">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-magnifying-glass"></i></span>
                            <span class="text-truncate"><?php echo e(__("Search Media Online")); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="<?php echo e(module_url("destroy")); ?>" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                            <span><?php echo e(__("Delete")); ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="d-flex gap-8">
            <div class="btn-group position-static">
                <button class="btn btn-dark btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
                    <span><i class="fa-light fa-plus"></i></span>
                    <span><?php echo e(__('New')); ?></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end border-1 border-gray-300  px-2 w-100 max-w-125" data-popper-placement="bottom-end">
                    <div>
                        <label for="file-upload" class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-up-from-bracket"></i></span> <?php echo e(__("Upload file")); ?>

                        </label>
                        <input id="file-upload" class="d-none form-file-input" name="avatar" type="file" multiple="true" data-call-success="Main.ajaxScroll(true)" />
                    </div>
                    <div>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" href="<?php echo e(module_url("update_folder")); ?>" data-popup="updateFolderModal" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-folder-plus"></i></span>
                            <span><?php echo e(__("New folder")); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
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
<form class="justify-content-between" action="<?php echo e(module_url()); ?>" method="POST">
    <div class="container justify-content-between">


        <div class="ajax-scroll" data-url="<?php echo e(module_url("list")); ?>" data-resp=".file-list" data-scroll="document" data-call-success="Files.loadSuccess(result);">
            <div class="row file-list">

            </div>

            <div class="ajax-scroll-loading d-none position-fixed b-0 l-0 zIndex-200 w-100">
                <div class="app-loading progress-primary-500 mx-auto pl-0 pr-0 w-100"></div>
            </div>
        </div>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppFiles/resources/views/index.blade.php ENDPATH**/ ?>