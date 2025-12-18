<?php if( $captions->Total() > 0 ): ?>

	<?php $__currentLoopData = $captions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
	<div class="col-12 mb-4">
	    <label class="card shadow-none" for="caption_<?php echo e($value->id_secure); ?>">
	    	<div class="card-header px-3 gap-16">
	    		<div class="fs-13 fw-5 text-truncate d-flex align-items-center gap-8">
	    			<?php if($value->type == 2): ?>
	    			<span class="badge badge-outline badge-xs badge-info">
                     	<?php echo e(__("AI")); ?>

                    </span> 
                    <?php endif; ?>
                    <span class="text-truncate"><?php echo e($value->name); ?></span>
	    		</div>
	    	</div>
	        <div class="card-body px-3">
	            <div class="d-flex flex-grow-1 align-items-top gap-8">
	                <div class="flex-grow-1 fs-13 text-truncate-5 min-h-100">
	                    <div class="text-truncate-5 text-gray-700">
	                    	<i class="fa-light fa-quote-left text-gray-900"></i>
                    		<?php echo nl2br($value->content); ?>

	                    </div>
	                </div>
	                <div class="d-flex fs-14">
		                
	                </div>
	            </div>
	        </div>

	        <a href="javascript:void(0);" class="card-footer d-flex align-items-center justify-content-center bg-hover-dark text-hover-white gap-8 fs-14 bbr-r-10 bbl-r-10 addToField" data-field=".post-caption" data-refresh="1" data-content="<?php echo e($value->content); ?>" onclick="Main.closeOffCanvas('getCaptionOffCanvas');">
	        	<i class="fa-light fa-plus"></i> <?php echo e(__("Use this caption")); ?>

	        </a>
	    </label>
	</div>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
	<div class="empty"></div>
<?php endif; ?><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppCaptions\resources/views/popup_list.blade.php ENDPATH**/ ?>