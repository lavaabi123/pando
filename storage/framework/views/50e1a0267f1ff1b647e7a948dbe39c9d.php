<?php if(!empty($lists)): ?>
    <div class="conversation-detail">
        <div class="conversation-header p-3 border-bottom">
            <h5 class="mb-0">Conversation Details</h5>
        </div>

        <div class="conversation-messages p-3" style="max-height: 500px; overflow-y: auto;">
            <?php $__currentLoopData = $lists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="message-item mb-3 <?php echo e($message['to_type'] == 'me' ? 'received' : 'sent'); ?>">
                    <div class="d-flex <?php echo e($message['to_type'] == 'me' ? '' : 'flex-row-reverse'); ?>">
                        <img src="<?php echo e($message['from_image']); ?>" class="rounded-circle me-3" width="35" height="35" alt="">
                        <div class="message-content">
                            <div class="message-bubble p-3 <?php echo e($message['to_type'] == 'me' ? 'bg-light' : 'bg-primary text-white'); ?>">
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

        <div class="reply-section p-3 border-top">
            <form id="reply-form" onsubmit="sendReply(event, '<?php echo e($id); ?>', '<?php echo e($conversation_id); ?>', '<?php echo e($lists[0]['inbox_type']); ?>')">
                <div class="input-group">
                    <textarea class="form-control" name="comment" rows="2" placeholder="Type your reply..." required></textarea>
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-light fa-paper-plane"></i> Send
                    </button>
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
                alert('Reply sent successfully');
                form[0].reset();
                // Reload the conversation
                loadInboxList();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            console.error('Error sending reply:', xhr);
            alert('Error sending reply. Please try again.');
        }
    });
}
</script>
<?php /**PATH C:\xampp82\htdocs\pando-laravel\modules\AppInbox\app\Providers/../../resources/views/ajax_list_detail.blade.php ENDPATH**/ ?>