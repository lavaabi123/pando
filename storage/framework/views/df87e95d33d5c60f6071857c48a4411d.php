<div class="modal fade" id="pubishingPreviewModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content actionForm" action="<?php echo e(module_url('save')); ?>" data-call-success="Main.closeModal('pubishingPreviewModal'); Main.ajaxScroll(true);">
            <input type="text" class="d-none" name="type" value="0">
            <div class="modal-header">
                <h1 class="modal-title fs-16"><?php echo e(__("Preview")); ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <?php
                    $postType = 'media';
                    $caption = '';
                    $medias = [];
                    $link = '';

                    if ($post) {
                        $postType = $post->type ?? 'media';
                        $postData = json_decode($post->data, false);
                        $caption = $postData->caption ?? '';
                        $medias = $postData->medias ?? [];
                        $link = $postData->link ?? '';
                    }
                ?>

                <div class="d-none">
                    <input type="hidden" class="preview-post-type" value="<?php echo e($postType); ?>">

                    <?php if($post && $post->account): ?>
                        <input type="hidden" class="preview-profile"
                            data-social-network="<?php echo e($post->account->social_network ?? ''); ?>"
                            data-avatar="<?php echo e($post->account->avatar ? Media::url($post->account->avatar) : ''); ?>"
                            data-name="<?php echo e($post->account->name ?? ''); ?>"
                            data-username="<?php echo e($post->account->username ?? ''); ?>"
                            data-link="<?php echo e($post->account->link ?? ''); ?>">
                    <?php endif; ?>

                    <div class="preview-list-medias">
                        <?php $__currentLoopData = $medias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <img src="<?php echo e(Media::url($media)); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <textarea class="form-control input-emoji fw-4 border" name="caption" placeholder="<?php echo e(__("Enter caption")); ?>"><?php echo e($caption); ?></textarea>
                </div>

                <?php
                    $module = strtolower($post->module ?? '');
                    $view = $module ? $module.'::preview' : null;
                ?>

                <?php if($view && view()->exists($view)): ?>
                    <div class="cpvx" data-social-network="<?php echo e($post->social_network ?? ''); ?>">
                        <?php echo $__env->make($view, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    Main.init(false);
    AppPubishing.init(false);
    Files.init(false);
</script><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppPublishing\resources/views/preview.blade.php ENDPATH**/ ?>