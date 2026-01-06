<?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $data = is_string($value->data) ? json_decode($value->data, true) : (is_array($value->data) ? $value->data : []);
        $caption = $data['caption'] ?? '';
        $link = $data['link'] ?? '';
        $medias = $data['medias'] ?? [];
        $img = is_array($medias) && isset($medias[0]) && $medias[0] ? $medias[0] : 'https://placehold.co/80x80';
        
        $network = $value->social_network ?? 'N/A';
        $type = $value->type ?? 'N/A';
        $status = ($value->result == 1) ? 'Ready' : 'Approval';
        
        // Get comments
        $comments = $value->comments ?? collect();
        $commentCount = $comments->count();
    ?>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card hp-100 approval-card">
            <div class="card-header px-3">
                <input class="form-check-input checkbox-item" type="checkbox" name="id[]">
                <div class="me-auto ms-2">
                    <div class="w-40 me-3 position-relative me-3">
                        <img src="https://itspando.com/writable/avatar/672fe716b4bee.png" class="w-100 rounded-circle" alt="">	
                        <i class="fab fa-facebook-square" style="bottom: 0;position: absolute;right: -7px;color: #3b5998;"></i>
                    </div>
                </div>
                <div class="d-flex gap-6">
                    <a href="<?php echo e(url_app("publishing/preview")); ?>" class="icon-with-circle actionItem" data-popup="pubishingPreviewModal" data-id="<?php echo e($value->grouping_data); ?>" data-call-success="AppPubishing.closePopoverCalendar();" data-bs-title="Preview" data-bs-toggle="tooltip" data-bs-placement="top"><?php echo file_get_contents(public_path('img/search.svg')); ?></a>
                    <a href="<?php echo e(module_url("update")); ?>" class="icon-with-circle actionItem" data-id="<?php echo e($value->id_secure); ?>" data-popup="groupModal" data-call-success="" data-bs-title="Duplicate Post" data-bs-toggle="tooltip" data-bs-placement="top">
                        <?php echo file_get_contents(public_path('img/duplicate.svg')); ?>

                    </a>
                    <a href="<?php echo e(url_app("publishing/composer")); ?>" class="icon-with-circle actionItem" data-id="<?php echo e($value->id_secure); ?>" data-append-content="composer-scheduling" data-call-success="AppPubishing.openCompose(); AppPubishing.closePopoverCalendar();" data-bs-title="Edit" data-bs-toggle="tooltip" data-bs-placement="top">
                        <?php echo file_get_contents(public_path('img/post.svg')); ?>

                    </a>
                    <a href="<?php echo e(url_app("publishing/comments")); ?>" class="icon-with-circle actionItem open-comment-modal" data-id="<?php echo e($value->grouping_data); ?>" data-popup="commentModal" data-bs-title="Comment" data-bs-toggle="tooltip" data-bs-placement="top">
                        <?php echo file_get_contents(public_path('img/msg.svg')); ?>

                    </a>
                    <a href="<?php echo e(url_app("publishing/destroy")); ?>" class="icon-with-circle actionItem" data-id="<?php echo e($value->grouping_data); ?>" data-call-success="location.reload();" data-bs-title="Delete Post" data-bs-toggle="tooltip" data-bs-placement="top" data-confirm="<?php echo e(__('Are you sure to delete this post?')); ?>">
                        <?php echo file_get_contents(public_path('img/delete.svg')); ?>

                    </a>
                </div>
            </div>
            <div class="card-body p-3 pt-2">
                <div class="card-content">
                    <p class="lastEdit text-primary fs-12 mb-0">Last Edited: <?php echo e($value->updated_at ? $value->updated_at->format('M d, Y h:i A') : 'N/A'); ?></p>
                    <div class="d-flex mt-3">
                        <div class="flex-grow-1">
                            <p class="card-text text-gray-600 mb-3 fs-14"><?php echo e($caption ?: 'No caption.'); ?></p>
                            <div class="d-flex gap-2 flex-wrap">
                            </div>
                        </div>
                        <div class="size-80 ms-3 overflow-hidden b-r-10 d-flex justify-content-center align-items-center fs-30 text-primary bg-primary-100 border border-primary-200 img-wrap">
                            <?php switch($type):
                                case ('media'): ?>
                                    <img src="<?php echo e(Media::url($img)); ?>" class="img-fluid rounded-3 shadow-sm"/>
                                    <?php break; ?>

                                <?php case ('link'): ?>
                                    <a href="<?php echo e($link); ?>" target="_blank"><i class="fa-light fa-link"></i></a>
                                    <?php break; ?>

                                <?php default: ?>
                                    <i class="fa-light fa-align-center"></i>
                            <?php endswitch; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer fs-12 d-flex flex-column border-0 gap-8 px-3">
                <div class="scheduleDetails d-flex gap-2 flex-column gap-8 py-2">
                    <div class="d-flex align-items-center gap-8">
                        <div class="text-primary"><?php echo file_get_contents(public_path('img/time.svg')); ?></div>
                        <a href="#" class="btn btn-secondary btn-sm">Schedule</a>
                        <p class="s_dateTime mb-0 fw-6 text-gray-500 fs-12">
                            <input type="text" style="color:#7ec476;cursor: pointer;" data-selecteddate="" class="border-0 date_approval fs-12" autocomplete="off" data-id="<?php echo e($value->id); ?>" name="" value="<?php echo e($value->time_post ? date('m/d/Y h:i A', $value->time_post) : ''); ?>" readonly="">
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-8">
                        <div class="text-primary"><?php echo file_get_contents(public_path('img/account.svg')); ?></div>
                        <a href="#" class="btn btn-secondary btn-sm">Assign to</a>
                        <p class="assign_name mb-0 fw-6 text-gray-500 fs-12"><?php echo e($value->assigned_user_name ?? 'N/A'); ?></p>
                    </div>
                    <div class="d-flex align-items-center gap-8">
                        <div class="text-primary"><?php echo file_get_contents(public_path('img/msg.svg')); ?></div>
                        <a href="javascript:void(0);" class="btn btn-secondary btn-sm open-comment-modal" data-post-id="<?php echo e($value->id_secure); ?>">
                            Comment <?php if($commentCount > 0): ?><span class="badge bg-primary ms-1"><?php echo e($commentCount); ?></span><?php endif; ?>
                        </a>
                        <?php if($commentCount > 0): ?>
                            <?php $lastComment = $comments->last(); ?>
                            <p class="s_comment mb-0 fw-6 text-gray-500 fs-12">
                                <?php echo e($lastComment->user_name ?? 'User'); ?> - <?php echo e(Str::limit($lastComment->comment, 30)); ?> (<?php echo e($lastComment->created_at ? $lastComment->created_at->format('M d, Y h:i A') : ''); ?>)
                            </p>
                        <?php else: ?>
                            <p class="s_comment mb-0 fw-6 text-gray-500 fs-12">No comments yet</p>
                        <?php endif; ?>
                    </div>
                </div>
               
                <div class="w-100 text-end mb-1">
                    <a href="<?php echo e(url_app("publishing/move_to_queue")); ?>" 
                       class="btn btn-primary w-110 px-4" 
                       data-call-success="Main.ajaxScroll(true)"
                       data-id="<?php echo e($value->grouping_data); ?>" 
                       data-confirm="<?php echo e(__('Are you sure to move this item to queue?')); ?>" 					   
                       title="<?php echo e(__('Move to Queue')); ?>" 
                       data-toggle="tooltip" 
                       data-placement="top">
                        <span><?php echo e(__('Approve')); ?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($schedules->total() == 0 && $schedules->currentPage() == 1): ?>
<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
    <span class="fs-70 mb-3 text-primary">
        <i class="fa-light fa-file-pen"></i>
    </span>
    <div class="fw-semibold fs-5 mb-2 text-gray-800">
        <?php echo e(__('No approvals yet')); ?>

    </div>
    <div class="text-body-secondary mb-4">
        <?php echo e(__('Start by creating a new approval to save your ideas and prepare content before publishing.')); ?>

    </div>
</div>
<?php endif; ?>

<style>
.comment-item {
    transition: all 0.3s ease;
}

.comment-item:hover {
    background-color: #f8f9fa;
}

.comments-container {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.comments-container::-webkit-scrollbar {
    width: 6px;
}

.comments-container::-webkit-scrollbar-track {
    background: #f7fafc;
}

.comments-container::-webkit-scrollbar-thumb {
    background-color: #cbd5e0;
    border-radius: 3px;
}

.icon-with-circle {
    position: relative;
}
</style><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppPublishingApproval\resources/views/list.blade.php ENDPATH**/ ?>