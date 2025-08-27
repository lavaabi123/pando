

<div class="mb-3">
    <div class="card shadow-none b-r-6">
        <div class="card-header px-3">
            <div class="fs-12 fw-6 text-gray-700">
                <?php echo e(__("Google business profile")); ?>

            </div>
        </div>
        <div class="card-body px-3">

        	<div class="mb-3">
				<label class="form-label"><?php echo e(__("Call To Action")); ?></label>
				<select class="form-select" name="options[gbp_action]">
			        <option value=""><?php echo e(__('No Action')); ?></option>
			        <option value="LEARN_MORE"><?php echo e(__('Learn more')); ?></option>
			        <option value="BOOK"><?php echo e(__('Book')); ?></option>
			        <option value="ORDER"><?php echo e(__('Order online')); ?></option>
			        <option value="SHOP"><?php echo e(__('Shop')); ?></option>
			        <option value="SIGN_UP"><?php echo e(__('Sign up')); ?></option>
			    </select>
			</div>
			<div class="mb-3">
				<label class="form-label"><?php echo e(__("Action Link")); ?></label>
				<input type="text" class="form-control" name="options[gbp_link]" placeholder="<?php echo e(__('Enter your link')); ?>">
			</div>
        </div>
    </div>
</div><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppChannelGBPLocations/resources/views/options.blade.php ENDPATH**/ ?>