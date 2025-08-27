<?php
$providers = \AI::getPlatforms();
$modelList = [];
foreach ($providers as $provider => $title) {
    $modelList[$provider] = \AI::getAvailableModels($provider);
}

?>

<div class="card shadow-none border-gray-300 mb-4">
    <div class="card-header fw-6"><?php echo e(__("AI Model Rates")); ?></div>
	    <div class="card-body">
	    	<div class="card shadow-none border-gray-300 mb-4">
		        <div class="card-body">
		            <ul class="mb-0 fs-14">
		                <li>
		                    <b><?php echo e(__("Purpose:")); ?></b>
		                    <?php echo e(__("Customize the conversion rate from token to credit for each AI model to control AI usage costs in your system.")); ?>

		                </li>
		                <li class="mt-3">
		                    <b><?php echo e(__("How to use:")); ?></b>
		                    <?php echo e(__("For each model, enter the number of credits that will be deducted for each token used.")); ?><br>
		                    <span class="text-900"><?php echo e(__("Example:")); ?></span> <b>1</b> <?php echo e(__("means 1 token = 1 credit (default);")); ?> <b>20</b> <?php echo e(__("means 20 token = 1 credits (using this model will cost double).")); ?>

		                </li>
		                <li class="mt-3">
		                    <b><?php echo e(__("Note:")); ?></b>
		                    <?php echo e(__("If you leave a field blank, the system will use the default value of 1 credit/token.")); ?><br>
		                    <?php echo e(__("You can adjust this rate at any time to suit your pricing strategy or cost control needs.")); ?>

		                </li>
		            </ul>
		        </div>
		    </div>
		<?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider => $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
	    	<div class="fw-6 mb-3 mt-20 fs-18 text-primary"><?php echo e(__($title)); ?></div>
	        <div class="row">
	            <?php $__currentLoopData = $modelList[$provider]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $model => $desc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
	                <div class="col-md-12 mb-2">
	                    <div class="p-2 border rounded d-flex justify-content-between align-items-center fs-14 gap-16">
	                        <div>
	                            <div class="fw-5"><?php echo e($model); ?></div>
	                            <small class="text-muted fs-12"><?php echo e($desc); ?></small>
	                        </div>
	                        <div>
	                            <input type="number" step="0.01" min="0.01"
	                                class="form-control text-end w-70"
	                                name="credit_rates[<?php echo e($model); ?>]"
	                                value="<?php echo e($rates[$model] ?? 1); ?>"
	                                required>
	                        </div>
	                    </div>
	                </div>
	            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	        </div>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	    </div>
</div><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AdminAIConfiguration/resources/views/credit-rates.blade.php ENDPATH**/ ?>