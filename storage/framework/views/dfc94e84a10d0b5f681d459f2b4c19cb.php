<?php
    $id_custom = str_replace("[", "_", $id);
    $id_custom = str_replace("]", "", $id_custom);

    if(!isset($ratio))
        $ratio = "1x1";
?>

<?php if(isset($name)): ?>
<label for="name" class="form-label"><?php echo e(__($name)); ?>

    <?php if(isset($required) && $required): ?>
    (<span class="text-danger">*</span>)
    <?php endif; ?>
</label>
<?php endif; ?>
<div class="p-3 border-1 b-r-6">
    <div class="form-file">
        <a class="w-100 mb-1 form-img ratio ratio-<?php echo e($ratio); ?> d-block actionItem bg-cover <?php echo e($id_custom); ?>" style="<?php echo e($value!=""?"background: url( ". Media::url($value) ." )":""); ?>" href="<?php echo e(route("app.files.popup_files")); ?>" data-id="<?php echo e($id_custom); ?>" data-filter="<?php echo e(serialize([ "type" => "image", "multi" => false ])); ?>" data-popup="filesModal">
        </a>
        <input class="form-control d-none" id="<?php echo e($id_custom); ?>" name="<?php echo e($id); ?>" placeholder="<?php echo e(__("Select file")); ?>" type="text" value="<?php echo e(Media::url($value)); ?>">
        <a class="btn btn-input actionItem pointer w-100" href="<?php echo e(route("app.files.popup_files")); ?>" data-id="<?php echo e($id_custom); ?>" data-filter="<?php echo e(serialize([ "type" => "image", "multi" => false ])); ?>" data-popup="filesModal">
            <i class="fa-light fa-folder-open"></i> <?php echo e(__("Files")); ?>

        </a>
    </div>
</div>
<?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppFiles/resources/views/block_select_file_large.blade.php ENDPATH**/ ?>