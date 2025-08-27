<div class="file-selected-media" id="medias">
    <div class="items clearfix ui-sortable">

        <?php if(!empty($files)): ?>

            <?php 
                $results = DB::table('files')->whereIn("file", $files)->get();
            ?>

            <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                <?php
                    $detectType = Media::detectFileIcon($value->detect);
                ?>

                <div class="file-item w-100 ratio ratio-1x1 min-h-80 border b-r-6 rounded selected text-<?php echo e($detectType['color']); ?> bg-<?php echo e($detectType['color']); ?>-100" data-id="file_<?php echo e($value->id_secure); ?>" data-file="<?php echo e(Media::url($value->file)); ?>" data-type="<?php echo e($value->detect); ?>">
                    <label class="d-flex flex-column flex-fill">
                        <div class="position-absolute r-6 t-6 zIndex-1">
                            <div class="form-check form-check-sm">
                                <input class="form-check-input" name="medias[]" type="text" value="<?php echo e($value->file); ?>" id="file_<?php echo e($value->id_secure); ?>" style="display: none;">
                            </div>
                        </div>
                        <div class="d-flex flex-fill align-items-center justify-content-center overflow-y-auto bg-cover position-relative btl-r-6 btr-r-6 file-item-media" <?php echo $value->detect=="image"?'style="background-image: url(\''.Media::url($value->file).'\');"':''; ?>>
                            <?php if($value->detect != "image"): ?>
                            <div class="fs-30">
                                <i class="<?php echo e($detectType['icon']); ?>"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </label>
                    <button type="button" href="javascript:void(0)" class="remove bg-white border b-r-100 text-danger w-20 h-20 fs-12 position-absolute r-0"><i class="fal fa-times"></i></button>
                </div>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php endif; ?>
        
    </div>
    <div class="drophere px-3 py-4 text-center text-gray-400 text-uppercase fs-12 bg-gray-100 border-3 border-dashed b-r-6" style="<?php echo e($files?"display: none;":""); ?>">
        <span class="has-action d-none"><?php echo e(__("Drop here")); ?></span>
        <span class="no-action"><?php echo e(__("Drag media here to post")); ?></span>
    </div>
</div>

<button type="button" class="btn btn-dark btn-lg d-lg-none d-md-none d-sm-block mt-3 w-100 showMedia"><?php echo e(__("Select media")); ?></button>

<?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppFiles/resources/views/block_selected_files.blade.php ENDPATH**/ ?>