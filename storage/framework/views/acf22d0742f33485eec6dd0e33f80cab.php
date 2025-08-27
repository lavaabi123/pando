<div class="mb-3">
    <div class="card shadow-none b-r-6">
        <div class="card-header px-3">
            <div class="fs-12 fw-6 text-gray-700">
                <?php echo e(__("Instagram")); ?>

            </div>
        </div>
        <div class="card-body px-3">
        	<div class="mb-3">
                <div class="col-md-12">
                    <div class="mb-4">
                        <label class="form-label"><?php echo e(__('Post To')); ?></label>
                        <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="options[ig_type]" value="feed" id="instagram_type_1" checked>
                                <label class="form-check-label mt-1" for="instagram_type_1">
                                    <?php echo e(__('Feed')); ?>

                                </label>
                            </div>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="options[ig_type]" value="reels" id="instagram_type_2">
                                <label class="form-check-label mt-1" for="instagram_type_2">
                                    <?php echo e(__('Reels')); ?>

                                </label>
                            </div>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="options[ig_type]" value="stories" id="instagram_type_3">
                                <label class="form-check-label mt-1" for="instagram_type_3">
                                    <?php echo e(__('Stories')); ?>

                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo e(__('First comment')); ?></label>
                        <textarea class="form-control input-emoji bbr-r-6 bbl-r-6" name="options[ig_comment]"></textarea>
                    </div>

                    <div class="mb-0">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="options[ig_pin]" id="ig_pin" value="1">
                            <label for="ig_comment" class="form-check-label">
                                <?php echo e(__('Pin Post')); ?>

                            </label>
                        </div>
                    </div>
                </div>
			</div>
        </div>
    </div>
</div><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppChannelInstagramProfiles/resources/views/options.blade.php ENDPATH**/ ?>