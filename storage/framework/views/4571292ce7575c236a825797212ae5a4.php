<div class="modal fade" id="proxiesModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" 
		      action="<?php echo e(module_url('save')); ?>" 
		      data-call-success="Main.closeModal('proxiesModal'); Main.DataTable_Reload('#DataTable')">

			<div class="modal-header">
				<h1 class="modal-title fs-16"><?php echo e(__("Update Proxy")); ?></h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<input type="hidden" name="id" value="<?php echo e(old('id', $result->id_secure ?? '')); ?>">
				<div class="msg-errors"></div>

				<div class="row">

					
					<div class="col-md-12">
						<div class="mb-4">
							<label class="form-label"><?php echo e(__('Status')); ?></label>
							<div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
								<?php $__currentLoopData = [1 => __('Enable'), 0 => __('Disable')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<div class="form-check me-3">
										<input class="form-check-input" type="radio" name="status" 
										       value="<?php echo e($value); ?>" id="status_<?php echo e($value); ?>"
										       <?php echo e(old('status', $result->status ?? 1) == $value ? 'checked' : ''); ?>>
										<label class="form-check-label mt-1" for="status_<?php echo e($value); ?>"><?php echo e($label); ?></label>
									</div>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</div>
						</div>
					</div>

					
					<div class="col-md-12">
						<div class="mb-4">
							<label for="proxy" class="form-label mb-1"><?php echo e(__('Proxy Address')); ?></label>
							<div class="text-muted fs-12 mb-2">
								<?php echo e(__('Format: username:password@ip:port or ip:port')); ?>

							</div>
							<input type="text" class="form-control" name="proxy" id="proxy"
								   placeholder="username:password@ip:port"
								   value="<?php echo e(old('proxy', $result->proxy ?? '')); ?>">
						</div>
					</div>

					
					<div class="col-md-12">
						<div class="mb-4">
							<label for="limit" class="form-label mb-1"><?php echo e(__('Account Limit')); ?></label>
							<div class="text-muted fs-12 mb-1">
								<?php echo e(__('Maximum number of accounts allowed to use this proxy.')); ?><br>
								<?php echo e(__('Enter -1 for unlimited.')); ?>

							</div>
							<input type="number" class="form-control" name="limit" id="limit"
								   placeholder="<?php echo e(__('e.g. 10 or -1')); ?>"
								   min="-1"
								   value="<?php echo e(old('limit', $result->limit ?? '-1')); ?>">
						</div>
					</div>

					
					<div class="col-md-12">
						<div class="mb-4">
							<label for="description" class="form-label"><?php echo e(__('Description (Optional)')); ?></label>
							<input type="text" class="form-control" name="description" id="description"
								   placeholder="<?php echo e(__('Proxy description')); ?>"
								   value="<?php echo e(old('description', $result->description ?? '')); ?>">
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
</div><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppProxies\resources/views/update.blade.php ENDPATH**/ ?>