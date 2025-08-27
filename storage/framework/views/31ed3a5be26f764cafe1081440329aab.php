<div class="modal fade" id="AITemplatesModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content actionForm" action="<?php echo e(module_url('save')); ?>" method="POST"
              data-call-success="Main.closeModal('AITemplatesModal'); Main.DataTable_Reload('#DataTable')">
            
            <?php echo csrf_field(); ?>

            <div class="modal-header">
                <h1 class="modal-title fs-16"><?php echo e(__('Update AI Templates')); ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <input class="d-none" name="id" type="text" value="<?php echo e(data($result, 'id_secure')); ?>">
                <div class="msg-errors mb-3"></div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Status')); ?></label>
                            <div class="d-flex gap-3 flex-column flex-lg-row">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="status" value="1" id="status_1" 
                                           <?php echo e(data($result, 'status', 'radio', 1, 1)); ?>>
                                    <label class="form-check-label mt-1" for="status_1"><?php echo e(__('Enable')); ?></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" value="0" id="status_0"
                                           <?php echo e(data($result, 'status', 'radio', 0, 1)); ?>>
                                    <label class="form-check-label mt-1" for="status_0"><?php echo e(__('Disable')); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Categories')); ?></label>
                            <select class="form-select" data-select2-dropdown-class="mt--1" data-control="select2" name="cate_id">
                                <option value="0"><?php echo e(__('Select categories')); ?></option>
                                <?php if(!empty($categories)): ?>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value->id); ?>"
                                                data-icon="<?php echo e($value->icon); ?> text-<?php echo e($value->color); ?>"
                                                <?php echo e(data($result, 'cate_id', 'select', $value->id)); ?>>
                                            <?php echo e($value->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="content" class="form-label"><?php echo e(__('Content')); ?></label>
                            <textarea class="form-control input-emoji" name="content"
                                      placeholder="<?php echo e(__('Enter your content')); ?>"><?php echo e(data($result, 'content')); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
                <button type="submit" class="btn btn-dark"><?php echo e(__('Save changes')); ?></button>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
	Main.Select2();
	Main.Emoji();
</script>
<?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AdminAITemplates/resources/views/update.blade.php ENDPATH**/ ?>