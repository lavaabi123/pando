<?php if(!empty($lists)): ?>
    <div class="conversation-detail h-100">
        <div class="conversation-header py-3 border-bottom">
            <h5 class="mb-0">Conversation Details</h5>
        </div>

        <div class="conversation-messages pt-3" style="max-height: 500px; overflow-y: auto;">
            <?php $__currentLoopData = $lists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="message-item mb-3 <?php echo e($message['to_type'] == 'me' ? 'received' : 'sent'); ?>">
                    <div class="d-flex <?php echo e($message['to_type'] == 'me' ? '' : 'flex-row-reverse'); ?>">
                        <img src="<?php echo e($message['from_image']); ?>" class="rounded-circle <?php echo e($message['to_type'] == 'me' ? 'me-2' : 'ms-2'); ?>" width="35" height="35" alt="">
                        <div class="message-content">
                            <div class="message-bubble p-2 b-r-10 <?php echo e($message['to_type'] == 'me' ? 'bg-light' : 'bg-primary text-white'); ?>">
                                <p class="mb-0"><?php echo e($message['message']); ?></p>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <?php echo e(date('M d, Y H:i', strtotime($message['created_time']))); ?>

                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="reply-section py-3 border-top">
            <form id="reply-form" onsubmit="sendReply(event, '<?php echo e($id); ?>', '<?php echo e($conversation_id); ?>', '<?php echo e($lists[0]['inbox_type']); ?>')">
					<div class="bg-white border b-r-30 p-15">
						<div class="d-flex align-items-center gap-2">
							<div class="post-account">
								<img alt="user" width="30" height="30" class="rounded-circle" src="<?php echo $lists[0]['from_image']; ?>">
							</div>	
							<p class="fw-7 mb-0 fs-12"><?php echo $lists[0]['from_name']; ?></p>					
						</div>
						<textarea class="form-control fw-4 border-0 p-0 p-t-10" rows="3" cols="10" id="textarea" name="comment" placeholder="Type your reply.." required></textarea>
					</div>
					<div class="d-flex align-items-center flex-row-reverse justify-content-between my-3">
						<ec-post-buttons >
							<div  class="save-buttons">
								<button  type="submit" class="btn btn-primary me-2"> Send</button>
								<button class="btn btn-secondary" onclick="clear_detail_form()">Cancel</button>
							</div>
						</ec-post-buttons>
					</div>
					<div class="form-check mt-2">
						<input class="form-check-input" type="checkbox" name="complete_id" value="1" id="markComplete">
						<label class="form-check-label" for="markComplete">
							Mark as complete after sending
						</label>
					</div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="text-center py-5">
        <p class="text-muted">No messages in this conversation</p>
    </div>
<?php endif; ?>

<script>
function sendReply(event, detailId, conversationId, inboxType) {
    event.preventDefault();
    const form = $(event.target);
    const formData = form.serialize() + '&detail_id=' + detailId + '&conversation_id=' + conversationId + '&inbox_type=' + inboxType;
    
    $.ajax({
        url: '<?php echo e(route("inbox.save_comment")); ?>',
        type: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.status === 'success') {
                Main.showNotify('', 'Reply sent successfully', 'success');
                form[0].reset();
                // Reload the conversation
                loadInboxList();
            } else {
                Main.showNotify('', 'Error: ' + response.message, 'error');
            }
        },
        error: function(xhr) {
            Main.showNotify('', 'Error sending reply. Please try again.', 'error');
        }
    });
}
</script>
<?php /**PATH C:\xampp82\htdocs\pando-laravel\modules\AppInbox\app\Providers/../../resources/views/ajax_list_detail.blade.php ENDPATH**/ ?>