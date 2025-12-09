<div class="modal fade" id="groupModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="<?php echo e(module_url("save")); ?>" data-call-success="Main.closeModal('groupModal');Main.ajaxScroll(true,'brands'); Main.ajaxScroll(true);">
			<div class="modal-header">
				<h1 class="modal-title fs-16"><?php echo e(__("Create brand")); ?></h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

		    	<input class="d-none" name="id_secure" type="text" value="<?php echo e(data($result, "id_secure")); ?>">
         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="name" class="form-label"><?php echo e(__('Brand Name')); ?></label>
	                     	<input placeholder="<?php echo e(__('Brand Name')); ?>" class="form-control" name="name" id="name" type="text" value="<?php echo e(data($result, "name")); ?>">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="name" class="form-label"><?php echo e(__('Brand Logo')); ?></label>
	                     	<input placeholder="<?php echo e(__('Brand Logo')); ?>" class="form-file-input" name="image" id="image" type="file" value="<?php echo e(data($result, "image")); ?>" accept="image/*">
							<img class="h-auto"
                         src="<?php echo e(!empty($result->image) ? Media::url($result->image) : module_folder_url("/assets/img/mark.png")); ?>"
                         style="width:15%"
                    >
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="primary_name" class="form-label"><?php echo e(__('Primary Contact Name')); ?></label>
	                     	<input placeholder="<?php echo e(__('Primary Contact Name')); ?>" class="form-control" name="primary_name" id="primary_name" type="text" value="<?php echo e(data($result, "primary_name")); ?>">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="primary_email" class="form-label"><?php echo e(__('Primary Email')); ?></label>
	                     	<input placeholder="<?php echo e(__('Primary Email')); ?>" class="form-control" name="primary_email" id="primary_email" type="text" value="<?php echo e(data($result, "primary_email")); ?>">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="primary_number" class="form-label"><?php echo e(__('Primary Phone Number')); ?></label>
	                     	<input placeholder="<?php echo e(__('Primary Phone Number')); ?>" class="form-control" name="primary_number" id="primary_number" type="text" value="<?php echo e(data($result, "primary_number")); ?>">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="notes" class="form-label"><?php echo e(__('Notes')); ?></label>
	                     	<textarea placeholder="<?php echo e(__('Notes')); ?>" class="form-control" name="notes" id="notes"><?php echo e(data($result, "notes")); ?></textarea>
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
	Main.Emoji();
	Main.activeItem();
</script>
<?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppBrands\resources/views/update.blade.php ENDPATH**/ ?>