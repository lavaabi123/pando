<div class="offcanvas offcanvas-end border-start" data-bs-backdrop="false" tabindex="-1" id="updateAIPrompts">
    <div class="offcanvas-header border-bottom pt-23 pb-22">
        <div class="fw-5"><?php echo e(__("Create new prompt")); ?></div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="actionForm" action="<?php echo e(module_url("save")); ?>" method="POST" data-call-success="Main.closeOffCanvas('updateAIPrompts'); Main.ajaxPages();">
            <input type="text" name="id" class="d-none" value="<?php echo e(data($result, "id_secure")); ?>">
            <div class="mb-3">
                <label class="form-label"><?php echo e(__("Prompt")); ?></label>
                <textarea class="form-control min-h-400" rows="5" name="prompt"><?php echo e(data($result, "prompt")); ?></textarea>
            </div>
            <button class="btn btn-dark w-100"><?php echo e(__("Save changes")); ?></button>
        </form>
    </div>
</div><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppAIPrompts/resources/views/update.blade.php ENDPATH**/ ?>