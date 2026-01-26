<?php if(!empty($lists)): ?>
    <div class="conversation-detail h-100">
        <div class="conversation-header pb-3 border-bottom">
            <h6 class="mb-0">Conversation Details</h6>
        </div>

        <div class="conversation-messages pt-3" style="max-height: 500px; overflow-y: auto;">
            <?php $__currentLoopData = $lists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="message-item mb-3 <?php echo e($message['to_type'] == 'me' ? 'received' : 'sent'); ?>">
                    <div class="d-flex flex-wrap flex-column <?php echo e($message['to_type'] == 'me' ? 'align-items-start align-content-start' : 'align-items-end align-content-end'); ?>">
                       
						
						<div class="d-flex align-items-start <?php echo e($message['to_type'] == 'me' ? '' : 'flex-row-reverse'); ?>">
							<div class="">								
								<div class="post-account">								
									<div class="text-gray-600 size-30 min-w-30 d-flex align-items-center justify-content-between position-relative">
										<img data-src="<?php echo e(Media::url($message['from_image'])); ?>" src="<?php echo e(theme_public_asset('img/default.png')); ?>" class="b-r-100 w-full h-full border-1 lazyload" onerror="this.src='<?php echo e(theme_public_asset('img/default.png')); ?>'">
										<span class="position-absolute b-0 r-0">
											<div class="w-100"><?php echo get_social_media_icon($message['media_type']); ?></div>
										</span>
									</div>									
								</div>															
							</div>
							<span class="ms-2 mb-0"><p class="fw-7 mb-0 fs-12"><?php echo e($message['from_name']); ?></p><p class="fs-12 text-gray-600 mb-0"><?php echo e(date('M d, Y H:i', strtotime($message['created_time']))); ?></p>
							</span>
							<div class="dropdown ml-auto">
								
							</div>				
						</div>
					
                        <div class="message-content ms-2">
                            <div class="message-bubble p-2 b-r-10 <?php echo e($message['to_type'] == 'me' ? 'bg-light' : 'bg-primary text-white'); ?>">
                                <p class="mb-0"><?php echo e($message['message']); ?></p>
								<?php if(!empty($message['attachments'])){  
									$type = getMediaType($message['attachments']);
									if ($type === 'image') {
										echo "<img src='".$message['attachments']."' style='max-width:100%;width:50%; border-radius:10px;' />";
									} elseif ($type === 'video') {
										echo "<video controls style='max-width:100%;width:50%; border-radius:10px;'>
												<source src='".$message['attachments']."' type='video/mp4'>
												Your browser does not support the video tag.
											  </video>";
									} 
								?>
								<?php } ?>
								<?php if(!empty($message['shares'])){  
								$type = getMediaType($message['shares']);
								if ($type === 'image') {
									echo "<img src='".$message['shares']."' style='max-width:100%;width:50%; border-radius:10px;' />";
								} elseif ($type === 'video') {
									echo "<video controls style='max-width:100%;width:50%; border-radius:10px;'>
											<source src='".$message['shares']."' type='video/mp4'>
											Your browser does not support the video tag.
										  </video>";
								}
								?>
								<?php } ?>
								<?php if(!empty($message['story'])){ 
								$type = getMediaType($message['story']);
								if ($type === 'image') {
									echo "<img src='".$message['story']."' style='max-width:100%;width:50%; border-radius:10px;' />";
								} elseif ($type === 'video') {
									echo "<video controls style='max-width:100%;width:50%; border-radius:10px;'>
											<source src='".$message['story']."' type='video/mp4'>
											Your browser does not support the video tag.
										  </video>";
								}  ?>
								<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="reply-section py-3 border-top">
            <form id="reply-form" onsubmit="sendReply(event, '<?php echo e($id); ?>', '<?php echo e($conversation_id); ?>', '<?php echo e($lists[0]['inbox_type']); ?>')">
					<div class="bg-white border b-r-30 p-3">
						<div class="d-flex align-items-center gap-2">
							<div class="post-account">
								<div class="text-gray-600 size-30 min-w-30 d-flex align-items-center justify-content-between position-relative">
									<img data-src="<?php echo e(Media::url($lists[0]['from_image'])); ?>" src="<?php echo e(theme_public_asset('img/default.png')); ?>" class="b-r-100 w-full h-full border-1 lazyload" onerror="this.src='<?php echo e(theme_public_asset('img/default.png')); ?>'">
								</div>
							</div>	
							<p class="fw-7 mb-0 fs-11"><?php echo $lists[0]['from_name']; ?></p>					
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