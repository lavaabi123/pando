<?php if( $captions->Total() > 0 ): ?>

	<?php $__currentLoopData = $captions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
	<div class="col-12 mb-2">
	    <label class="card rounded-5 border-gray-300 shadow-none" for="caption_<?php echo e($value->id_secure); ?>">
			<div class="d-flex flex-row border-bottom border-gray-300">
				<div class="card-body d-flex gap-8 p-2 align-items-center">
					<div class="d-flex gap-8 ps-1">
						<input class="form-check-input checkbox-item" type="checkbox" name="id[]" value="<?php echo e($value->id_secure); ?>" id="caption_<?php echo e($value->id_secure); ?>">
					</div>
					<div class="fs-14 fw-7">
						<?php if($value->type == 2): ?>
						<span class="badge badge-outline badge-xs badge-info">
							<?php echo e(__("AI")); ?>

						</span> 
						<?php endif; ?>
						<span class="text-truncate"><?php echo e($value->name); ?></span>
					</div>
				</div>
				<div class="card-footer px-2 me-2 d-flex justify-content-center border-none">
					<div class="card-toolbar d-flex gap-16">
						<div class="btn-group position-static">
							<div class="dropdown-toggle dropdown-arrow-hide text-gray-900 fs-14" data-bs-toggle="dropdown" aria-expanded="true">
								<i class="fa-light fa-grid-2"></i>
							</div>
							<ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-1 w-100 max-w-120 min-w-120">
								<li>
									<a class="dropdown-item px-2 p-t-2 p-b-2 rounded d-flex align-items-center gap-8 fw-5 fs-13 actionItem" href="<?php echo e(module_url("update")); ?>" data-id="<?php echo e($value->id_secure); ?>" data-popup="captionModal" data-call-success="">
										<i class="fa-light fa-pen-to-square"></i> <span ><?php echo e(__('Edit')); ?></span>
									</a>
								</li>
								<li><hr class="dropdown-divider"></li>
								<li>
									<a class="dropdown-item px-2 p-t-2 p-b-2 rounded d-flex align-items-center gap-8 fw-5 fs-13 actionItem" href="<?php echo e(module_url("destroy")); ?>" data-id="<?php echo e($value->id_secure); ?>" data-call-success="Main.ajaxScroll(true);">
										<i class="fa-light fa-trash-can"></i> <span><?php echo e(__('Delete')); ?></span>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="px-3 py-3">
				<div class="flex-grow-1 fs-14">
					<div class="text-truncate-5 text-gray-700">
						<i class="fa-light fa-quote-left text-gray-900 ps-1 fw-9"></i>
						<?php echo nl2br($value->content); ?>

					</div>
				</div>
				<div class="d-flex fs-14">
					
				</div>
			</div>
	    </label>
		
	</div>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
	<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
	    <span class="fs-70 mb-3 text-primary">
	        <i class="fa-light fa-quote-right"></i>
	    </span>
	    <div class="fw-semibold fs-5 mb-2 text-gray-900">
	        <?php echo e(__('No Captions Yet')); ?>

	    </div>
	    <div class="text-body-secondary mb-4 text-center max-w-500">
	        <?php echo e(__('Start saving your favorite captions to reuse and streamline your content creation process.')); ?>

	    </div>
	    <a class="btn btn-dark actionItem" href="<?php echo e(module_url("update")); ?>" data-popup="captionModal" data-call-success="Main.ajaxScroll(true);">
	        <i class="fa-light fa-plus me-1"></i> <?php echo e(__('Add new caption')); ?>

	    </a>
	</div>
<?php endif; ?><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppCaptions\resources/views/list.blade.php ENDPATH**/ ?>