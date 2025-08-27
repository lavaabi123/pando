<div class="mb-3">
    <div class="card shadow-none b-r-6">
        <div class="card-header px-3">
            <div class="fs-12 fw-6 text-gray-700">
                <?php echo e(__("Youtube")); ?>

            </div>
        </div>
        <div class="card-body px-3">
        	<div class="mb-3">
                <div class="col-md-12">
                    <div class="mb-3 max-w-300">
                        <?php echo $__env->make('appfiles::block_select_file_large', [
                            "id" => "options[youtube_thumbnail]",
                            "name" => __("Thumbnail"),
                            "required" => false,
                            "ratio" => "16x9",
                            "value" => ""
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><?php echo e(__('Video Type')); ?></label>
                        <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="options[youtube_type]" value="video" id="youtube_type_1" checked>
                                <label class="form-check-label mt-1" for="youtube_type_1">
                                    <?php echo e(__('Video')); ?>

                                </label>
                            </div>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="options[youtube_type]" value="short" id="youtube_type_0">
                                <label class="form-check-label mt-1" for="youtube_type_0">
                                    <?php echo e(__('Short Video')); ?>

                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning fs-12 mb-3" role="alert">
                        <?php echo e(__("To upload a video to YouTube Shorts, ensure that your video meets the required resolution and that its duration does not exceed 3 minutes. We strongly recommend using a video with a 9:16 aspect ratio")); ?>

                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('Title')); ?></label>
                        <input type="text" class="form-control" name="options[youtube_title]" value="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-12 fw-400"><?php echo e(__("Category")); ?></label>
                        <select class="form-select" name="options[youtube_category]">
                            <?php $__currentLoopData = getYoutubeCategories(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label"><?php echo e(__('Tags')); ?></label>
                        <input type="text" class="form-control input-tags" name="options[youtube_tags]" value="">
                    </div>
                </div>
			</div>
        </div>
    </div>
</div><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppChannelYoutubeChannels/resources/views/options.blade.php ENDPATH**/ ?>