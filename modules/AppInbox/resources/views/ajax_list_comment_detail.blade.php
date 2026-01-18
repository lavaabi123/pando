@if(!empty($lists))
    <div class="comment-detail">
			<div class="border-0">
				<div class="d-flex align-items-start justify-content-between">
					<div class="d-flex">
					<?php if($lists[0]['media_type'] == 'facebook'){ ?>
						<div class="post-account">
						
						<?php if($lists[0]['inbox_type'] == 'Comment' || $lists[0]['inbox_type'] == 'AdComment'){ ?>
						<img alt="user" width="30" height="30" class="rounded-circle" src="<?php echo $lists[0]['to_image']; ?>">
						<?php }else{ ?>
						<img alt="user" width="30" height="30" class="rounded-circle" src="<?php echo $lists[0]['to_image']; ?>">
						<?php } ?>
							<i class="fa-facebook-f fab post-media2" style="background-color: #0074fa;color: #fff;right: 10px;"></i>
						</div>	
					<?php }else if($lists[0]['media_type'] == 'linkedin'){ ?>
						<div class="post-account">
						
						<?php if($lists[0]['inbox_type'] == 'Comment' || $lists[0]['inbox_type'] == 'AdComment'){ ?>
						<img alt="user" width="30" height="30" class="rounded-circle" src="<?php echo $lists[0]['to_image']; ?>">
						<?php }else{ ?>
						<img alt="user" width="30" height="30" class="rounded-circle" src="<?php echo $lists[0]['to_image']; ?>">
						<?php } ?>
							<i class="fa-linkedin fab post-media2" style="background-color: #0077b5;color: #fff;right: 10px;"></i>
						</div>	
					<?php }else{ ?>	
						<div class="post-account">
						<?php if($lists[0]['inbox_type'] == 'Comment' || $lists[0]['inbox_type'] == 'AdComment'){ ?>
						<img alt="user" width="30" height="30" class="rounded-circle" src="<?php echo $lists[0]['to_image']; ?>">
						<?php }else{ ?>
						<img alt="user" width="30" height="30" class="rounded-circle" src="<?php echo $lists[0]['to_image']; ?>">
						<?php } ?>
							<i class="fa-instagram fab post-media2" style="background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);color: #fff;right: 10px;"></i>
						</div>
						<?php } ?>								
					</div>
					
				<?php if(!empty($post_detail) && ($lists[0]['inbox_type'] == 'Comment' || $lists[0]['inbox_type'] == 'AdComment')){ 	?>
					<span class="ml-2 mb-0"><p class="fw-7 mb-0 fs-12"><?php echo !empty($post_detail['from']['name']) ? $post_detail['from']['name'] : $post_detail['username']; ?></p><p class="mb-0 fs-12 text-gray-600"><?php echo !empty($post_detail['from']['name']) ? date("M d, Y, h:i a", strtotime($post_detail['created_time'])) : date("M d, Y, h:i a", strtotime($post_detail['timestamp'])) ; ?></p>
						<!--<div class="text-muted small pt-1"> 5055 Patrick Ln., Suite 105, Las Vegas, NV 89119</div>-->
					</span>
				<?php }else{ ?>
					<span class="ml-2 mb-0"><p class="fw-7 mb-0 fs-12"><?php echo !empty($lists[0]['to_name']) ? $lists[0]['to_name'] : ''; ?>(Page)<span class="chip chip-blue ml-2">
					<span><?php echo $lists[0]['inbox_type']; ?></span></span></p>
						<!--<div class="text-muted small pt-1"> 5055 Patrick Ln., Suite 105, Las Vegas, NV 89119</div>-->
					</span>
					
				<?php } ?>
					<div class="dropdown ml-auto">
						
					</div>				
				</div>
				
				<?php if(!empty($post_detail) && ($lists[0]['inbox_type'] == 'Comment' || $lists[0]['inbox_type'] == 'AdComment')){ 	?>
				<div class="d-flex mt-3 mb-3">
				<div class="col pl-0">
				<p class="mb-3 content-white-space text-break"><?php 
				if(!empty($post_detail['message'])){
					echo $post_detail['message'];
				}else if(!empty($post_detail['caption'])){
					echo $post_detail['caption'];
				}else{
					echo '';
				} ?>		
				</div>
				<div class="col-auto pr-0">
				<?php if($lists[0]['media_type'] == 'facebook'){  ?>
				<img width="80" height="80" class="b-r-15" src="<?php echo (!empty($post_detail['full_picture']) && @getimagesize($post_detail['full_picture'])) ? $post_detail['full_picture'] : get_theme_url()."Assets/img/default.jpg"; ?>">
				<?php }else if($lists[0]['media_type'] == 'linkedin' || $lists[0]['media_type'] == 'instagram'){ ?>
				<img width="80" height="80" class="b-r-15" src="<?php echo (!empty($lists[0]['media_url']) && @getimagesize($lists[0]['media_url'])) ? $lists[0]['media_url'] : get_theme_url()."Assets/img/default.jpg"; ?>">
				<?php }else{ ?>
				<img width="80" height="80" class="b-r-15" src="<?php echo (!empty($post_detail['media_url']) && @getimagesize($post_detail['media_url'])) ? $post_detail['media_url'] : get_theme_url()."Assets/img/default.jpg"; ?>">
				<?php } ?>
				</div>
				</div>
				<?php } ?>
				</div>
       

        <div class="comments-section p-3 maxHeight-wos" style="max-height: 500px; overflow-y: auto;">
            @foreach($lists as $comment)
                <div class="comment-item mb-3">
                    <div class="d-flex">
                        <img src="{{ $comment['from_image'] }}" class="rounded-circle me-3" width="35" height="35" alt="">
                        <div class="flex-grow-1">
                            <div class="comment-header d-flex justify-content-between">
                                <strong>{{ $comment['from_name'] }}</strong>
                                <small class="text-muted">
                                    {{ date('M d, Y H:i', strtotime($comment['created_time'])) }}
                                </small>
                            </div>
                            <p class="mb-1">{{ $comment['message'] }}</p>
                            
                            @if(!empty($comment['tag_names']))
                                <div class="mt-1">
                                    @foreach(explode(',', $comment['tag_names']) as $tag)
                                        <span class="badge bg-secondary me-1">
                                            <i class="fa-tag fal me-1"></i>{{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
							
							
							<?php if($id == $comment['id'] && $lists[0]['media_type'] != 'linkedin'){ ?>
								<input type="hidden" name="detail_id" value="<?php echo $id; ?>" >
								
							<?php if($lists[0]['inbox_type'] != 'Tags'){ ?>
								
								<form onsubmit="sendCommentReply(event, '{{ $id }}', '', 'comment')">
								<div class="bg-white border b-r-30 p-15">
									<div class="d-flex align-items-center gap-2">
										<div class="post-account">
											<img alt="user" width="30" height="30" class="rounded-circle" src="<?php echo $lists[0]['to_image']; ?>">
										</div>	
										<p class="fw-7 mb-0 fs-12"><?php echo $lists[0]['to_name']; ?></p>					
									</div>
									<textarea class="form-control fw-4 mt-2 b-r-30 p-15 border-0" rows="4" cols="10"  name="comment" placeholder="" required></textarea>
									<div class="btm-option d-flex justify-content-between">
									<ul class="d-flex align-items-center">
										
									</ul>
									<span class="countText">121</span>
									</div>
								</div>
								<div class="d-flex align-items-center flex-row-reverse justify-content-between my-3">
									<div class="custom-control custom-checkbox  d-flex align-items-center flex-row-reverse">
										<input name="complete_id" type="checkbox" class="custom-control-input ng-untouched ng-pristine ng-valid me-2" value="1" >
										<label for="complete6" class="custom-control-label font-normal fs-12 fw-6"><span class="d-md-inline-block me-2">Mark Complete</span></label>
									</div>
									<ec-post-buttons >
										<div  class="save-buttons">
											<button type="submit" class="btn btn-primary me-2"> Send</button>
											<button class="btn btn-secondary" onclick="clear_detail_form()">Cancel</button>
										</div>
									</ec-post-buttons>
								</div>
								</form>
							<?php } } ?> 

                            <!-- Child comments/replies -->
                            @if(!empty($comment['child']))
                                <div class="child-comments mt-2 ms-4">
                                    @foreach($comment['child'] as $child)
                                        <div class="child-comment-item mb-2 p-2 bg-light rounded">
                                            <div class="d-flex">
                                                <img src="{{ $child['from_image'] }}" class="rounded-circle me-2" width="25" height="25" alt="">
                                                <div class="flex-grow-1">
                                                    <strong class="small">{{ $child['from_name'] }}</strong>
                                                    <p class="mb-0 small">{{ $child['message'] }}</p>
                                                    <small class="text-muted">
                                                        {{ date('M d, Y H:i', strtotime($child['created_time'])) }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
										<?php if($id == $child['id'] && $lists[0]['media_type'] != 'linkedin'){ ?>
					<input type="hidden" name="detail_id" value="<?php echo $id; ?>" >
					
				<?php if($lists[0]['inbox_type'] != 'Tags'){ ?>
					
				
					<form onsubmit="sendCommentReply(event, '{{ $id }}', '', 'comment')">
					<div class="bg-white border b-r-30 p-15 ml-3rem">
						<div class="d-flex align-items-center gap-2">
							<div class="post-account">
								<img alt="user" width="30" height="30" class="rounded-circle" src="<?php echo $lists[0]['to_image']; ?>">
							</div>	
							<p class="fw-7 mb-0 fs-12"><?php echo $lists[0]['to_name']; ?></p>				
						</div>
						<textarea class="form-control fw-4 mt-2 border b-r-30 p-15" rows="4" cols="10"  name="comment" placeholder="" required></textarea>
						<div class="btm-option d-flex justify-content-between">
						<ul class="d-flex align-items-center">
						</ul>
						<span class="countText">121</span>
						</div>
					</div>
					<div class="d-flex align-items-center flex-row-reverse justify-content-between my-3 ml-3rem">
						<div class="custom-control custom-checkbox  d-flex align-items-center flex-row-reverse">
							<input name="complete_id" type="checkbox" class="custom-control-input ng-untouched ng-pristine ng-valid me-2" value="1" >
							<label for="complete6" class="custom-control-label font-normal fs-12 fw-6"><span class="d-md-inline-block">Mark Complete</span></label>
						</div>
						<ec-post-buttons >
							<div  class="save-buttons">
								<button type="submit" class="btn btn-primary me-2"> Send</button>
								<button class="btn btn-secondary" onclick="clear_detail_form()">Cancel</button>
							</div>
						</ec-post-buttons>
					</div>
					</form>
				<?php } ?> 
				<?php } ?>
                                    @endforeach
                                </div>
                            @endif
<!--
                            <button class="btn btn-sm btn-link p-0" onclick="showReplyForm({{ $comment['id'] }})">
                                <i class="fa-light fa-reply"></i> Reply
                            </button>-->
                        </div>
                    </div>
                </div>
            @endforeach
			
			<?php if($lists[0]['media_type'] == 'linkedin'){ ?>
					<form onsubmit="sendCommentReply(event, '{{ $id }}', '', 'comment')">
					<div class="bg-white border b-r-30 p-15">
						<div class="d-flex align-items-center gap-2">
							<div class="post-account">
								<img alt="user" width="30" height="30" class="rounded-circle" src="<?php echo $lists[0]['to_image']; ?>">
							</div>	
						<p class="fw-7 mb-0 fs-12"><?php echo $lists[0]['to_name']; ?></p>							
						</div>
						<textarea class="form-control fw-4 mt-2 border b-r-30 p-15" rows="6" cols="10"  name="comment" placeholder="" required></textarea>
						<div class="btm-option d-flex justify-content-between">
						<ul class="d-flex align-items-center">
						</ul>
						<span class="countText">121</span>
						</div>
					</div>
					<div class="d-flex align-items-center flex-row-reverse justify-content-between my-3">
						<div class="custom-control custom-checkbox  d-flex align-items-center flex-row-reverse">
							<input name="complete_id" type="checkbox" class="custom-control-input ng-untouched ng-pristine ng-valid me-2" value="1" >
							<label for="complete6" class="custom-control-label font-normal fs-12 fw-6"><span class="d-md-inline-block me-2">Mark Complete</span></label>
						</div>
						<ec-post-buttons >
							<div  class="save-buttons">
								<button type="submit" class="btn btn-primary me-2"> Send</button>
								<button class="btn btn-secondary" onclick="clear_detail_form()">Cancel</button>
							</div>
						</ec-post-buttons>
					</div>
					</form>
					<?php } ?>
        </div>

        <!--<div class="reply-section p-3 border-top">
            <form id="comment-reply-form" onsubmit="sendCommentReply(event, '{{ $id }}', '', 'comment')">
                <div class="input-group">
                    <textarea class="form-control" name="comment" rows="2" placeholder="Type your reply..." required></textarea>
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-light fa-paper-plane"></i> Reply
                    </button>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="complete_id" value="1" id="markCommentComplete">
                    <label class="form-check-label" for="markCommentComplete">
                        Mark as complete after sending
                    </label>
                </div>
            </form>
        </div>-->
    </div>
@else
    <div class="text-center py-5">
        <p class="text-muted">No comments found</p>
    </div>
@endif

<script>
function sendCommentReply(event, detailId, conversationId, inboxType) {
    event.preventDefault();
    const form = $(event.target);
    const formData = form.serialize() + '&detail_id=' + detailId + '&conversation_id=' + conversationId + '&inbox_type=' + inboxType;
    
    $.ajax({
        url: '{{ route("inbox.save_comment") }}',
        type: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.status === 'success') {
                Main.showNotify('', 'Reply sent successfully', 'success');
                form[0].reset();
                loadInboxList();
            } else {
                Main.showNotify('', 'Error: ' + response.message, 'error');
            }
        },
        error: function(xhr) {
            Main.showNotify('', 'Error sending reply. Please try again.', 'error');
			//console.error('Error sending reply:', xhr);
        }
    });
}

function showReplyForm(commentId) {
    // Implementation for showing reply form for specific comment
    //console.log('Reply to comment:', commentId);
}
</script>
