<?php if(!empty($inbox_list)): ?>
    <div class="inbox-messages-list">
        <?php $__currentLoopData = $inbox_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="inbox-item p-3 border-bottom <?php echo e($item['is_completed'] ? 'completed' : ''); ?>" 
                 data-id="<?php echo e($item['id']); ?>" 
                 data-type="<?php echo e(empty($item['conversation_id']) ? 'comment' : 'message'); ?>"
				 data-network="<?php echo e($item['media_type']); ?>"
				 data-post-id="<?php echo e($item['post_id']); ?>"
				 data-conversation-id="<?php echo e($item['conversation_id']); ?>"
                 onclick="loadDetail(this)">
                <div class="d-flex align-items-center">
                    <img src="<?php echo e($item['from_image']); ?>" class="rounded-circle me-3" width="40" height="40" alt="">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1"><?php echo e($item['from_name']); ?></h6>
                            <small class="text-muted"><?php echo e(date('M d, Y', strtotime($item['created_time']))); ?></small>
                        </div>
                        <p class="mb-1"><?php echo e($item['message']); ?></p>
                        <small class="text-muted">
                            <i class="fab fa-<?php echo e(strtolower($item['media_type'])); ?> me-1"></i>
                            <?php echo e($item['inbox_type']); ?>

                        </small>
                    </div>
                </div>
                
                <?php if(!empty($item['tag_names'])): ?>
                    <div class="mt-2">
                        <?php $__currentLoopData = explode(',', $item['tag_names']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge bg-secondary me-1">
                                <i class="fa-tag fal me-1"></i><?php echo e($tag); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="pagination-container mt-3">
        <?php echo $pagerContainer; ?>

    </div>
<?php else: ?>
    <div class="text-center py-5">
        <i class="fa-light fa-inbox fa-3x text-muted"></i>
        <p class="mt-3 text-muted">No messages found</p>
    </div>
<?php endif; ?>

<?php /**PATH C:\xampp82\htdocs\pando-laravel\modules\AppInbox\app\Providers/../../resources/views/ajax_list.blade.php ENDPATH**/ ?>