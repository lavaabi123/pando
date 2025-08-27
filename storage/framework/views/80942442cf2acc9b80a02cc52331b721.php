<div class="form-file">
    <?php if( isset($large) && $large): ?>
    <label class="w-100 mb-1 form-img ratio ratio-1x1" for="<?php echo e($id ?? ''); ?>">
        <div></div>                                            
    </label>
    <?php endif; ?>
    <?php if( isset($name) ): ?>
    <label for="<?php echo e($id ?? ''); ?>" class="btn btn-light w-100">
        <?php echo e($name); ?>

    </label>
    <?php endif; ?>
    <input class="d-none form-file-input-edit" type="text" value="<?php echo e($value ?? ''); ?>" />
    <input id="<?php echo e($id ?? ''); ?>" class="d-none form-file-input" name="<?php echo e($id ?? ''); ?>" type="file" accept="image/*" />
</div>
<?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppFiles/resources/views/block_upload.blade.php ENDPATH**/ ?>