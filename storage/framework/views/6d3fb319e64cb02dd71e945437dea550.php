<?php if(!empty($inbox_list)): ?>
    <div class="inbox-messages-list">
        <?php $__currentLoopData = $inbox_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="inbox-item inbox-<?php echo $item['id']; ?> py-3 border-bottom <?php echo e($item['is_completed'] ? 'completed' : ''); ?>" 
                 data-id="<?php echo e($item['id']); ?>" 
                 data-type="<?php echo e(empty($item['conversation_id']) ? 'comment' : 'message'); ?>"
				 data-network="<?php echo e($item['media_type']); ?>"
				 data-post-id="<?php echo e($item['post_id']); ?>"
				 data-conversation-id="<?php echo e($item['conversation_id']); ?>">
				 <div class="d-flex align-items-center gap-4 justify-content-between">
				<div class="d-flex" 
					 data-id="<?php echo e($item['id']); ?>" 
					 data-conversation-id="<?php echo e($item['conversation_id']); ?>" 
					 data-post-id="<?php echo e($item['post_id']); ?>" 
					 data-network="<?php echo e($item['media_type']); ?>" 
					 style="cursor:pointer">
					
					<div class="custom-control custom-checkbox me-2 d-flex align-items-center">
						<input name="inbox_check[]" 
							   type="checkbox" 
							   class="inbox_checkbox_input custom-control-input" 
							   value="<?php echo e((empty($item['conversation_id']) ? 'inbox_comments--' : 'inbox--') . $item['id']); ?>" 
							   id="<?php echo e((empty($item['conversation_id']) ? 'inbox_comments--' : 'inbox--') . $item['id']); ?>">
						<label class="custom-control-label" 
							   for="<?php echo e((empty($item['conversation_id']) ? 'inbox_comments--' : 'inbox--') . $item['id']); ?>">
						</label>
					</div>	
										
					<div onclick="loadDetail(this)" data-id="<?php echo e($item['id']); ?>" 
                 data-type="<?php echo e(empty($item['conversation_id']) ? 'comment' : 'message'); ?>"
				 data-network="<?php echo e($item['media_type']); ?>"
				 data-post-id="<?php echo e($item['post_id']); ?>"
				 data-conversation-id="<?php echo e($item['conversation_id']); ?>" class="post-account">
						<div class="text-gray-600 size-30 min-w-30 d-flex align-items-center justify-content-between position-relative">
							<img data-src="<?php echo e(Media::url($item['to_image'])); ?>" src="<?php echo e(theme_public_asset('img/default.png')); ?>" class="b-r-100 w-full h-full border-1 lazyload" onerror="this.src='<?php echo e(theme_public_asset('img/default.png')); ?>'">
							<span class="position-absolute b-0 r-0">
								<div class="w-100"><?php echo get_social_media_icon($item['media_type']); ?></div>
							</span>
						</div>
						
					</div>
					
					<span onclick="loadDetail(this)" 
						  data-id="<?php echo e($item['id']); ?>" 
                 data-type="<?php echo e(empty($item['conversation_id']) ? 'comment' : 'message'); ?>"
				 data-network="<?php echo e($item['media_type']); ?>"
				 data-post-id="<?php echo e($item['post_id']); ?>"
				 data-conversation-id="<?php echo e($item['conversation_id']); ?>"
						  style="cursor:pointer" 
						  class="ms-2 fw-7 mb-0 fs-11">
						<?php if($item['media_type'] == 'twitter'): ?>
							<?php echo e($item['to_name']); ?>

							<span class="chip chip-blue">
								<span>Direct Message</span>
							</span>
						<?php else: ?>
							<?php echo e($item['to_name']); ?> (Page) 
							<span class="chip chip-blue d-block fs-10 fw-5">
								<span><?php echo e($item['inbox_type']); ?></span>
							</span>
						<?php endif; ?>
					</span>
				</div> 
				<span class="d-flex">
					<a 
					   data-id="<?php echo e($item['id']); ?>" 
					   data-conversation-id="<?php echo e($item['conversation_id']); ?>" 
					   data-post-id="<?php echo e($item['post_id']); ?>" 
					   data-network="<?php echo e($item['media_type']); ?>" 
					   data-toggle="tooltip" 
					   data-placement="top" 
					   title="Reply" 
					   href="javascript:void(0);" 
					   class="icon-with-circle me-2">
						<?php echo file_get_contents(theme_public_asset('img/reply.svg')); ?>

					</a>
					
					<a data-toggle="tooltip" 
					   data-placement="top" 
					   title="Tag It" 
					   href="javascript:void(0);" 
					   class="icon-with-circle me-2 tag-icon-ids" 
					   data-tag-ids="<?php echo e(!empty($item['tag_ids']) ? $item['tag_ids'] : ''); ?>" 
					   data-id="<?php echo e($item['id']); ?>" 
					   onclick="open_list_tag(this,'<?php echo e(empty($item['conversation_id']) ? 'inbox_comments' : 'inbox'); ?>')">
						<?php echo file_get_contents(theme_public_asset('img/tag.svg')); ?>

					</a>
					
					<a data-toggle="tooltip" 
					   data-placement="top" 
					   title="Assign It" 
					   href="javascript:void(0);" 
					   class="icon-with-circle me-2 user-icon-ids" 
					   data-user-ids="<?php echo e(!empty($item['user_ids']) ? $item['user_ids'] : ''); ?>" 
					   data-id="<?php echo e($item['id']); ?>" 
					   onclick="open_list_user(this,'<?php echo e(empty($item['conversation_id']) ? 'inbox_comments' : 'inbox'); ?>')">
						<?php echo file_get_contents(theme_public_asset('img/flag.svg')); ?>

					</a>
					
					<a data-toggle="tooltip" 
					   data-placement="top" 
					   title="Add to Favorite" 
					   href="javascript:void(0);" 
					   class="icon-with-circle me-2 <?php echo e($item['is_favourite'] == 1 ? 'is_fav' : ''); ?>" 
					   data-id="<?php echo e($item['id']); ?>" 
					   data-fav="<?php echo e($item['is_favourite']); ?>" 
					   onclick="favourite_toggle(this,'<?php echo e(empty($item['conversation_id']) ? 'inbox_comments' : 'inbox'); ?>')">
						<?php echo file_get_contents(theme_public_asset('img/heart.svg')); ?>

					</a>
					
					<a data-toggle="tooltip" 
					   data-placement="top" 
					   title="Delete" 
					   href="javascript:void(0)" 
					   class="icon-with-circle" 
					   onclick="delete_inbox_message('<?php echo e($item['id']); ?>','<?php echo e(empty($item['conversation_id']) ? 'inbox_comments' : 'inbox'); ?>')">
						<?php echo file_get_contents(theme_public_asset('img/delete.svg')); ?>

					</a>
					
					<?php if(!empty($item['post_url'])): ?>
						<div class="d-inline-block dropdown">
							<a href="javascript:void(0)" 
							   class="dropdown-toggle link text-muted d-flex w-30 h-30 icon-with-circle fs-18 justify-content-center" 
							   aria-expanded="false">
								<i class="fa fa-ellipsis-v"></i>
							</a>
						
							<div style="min-width: 240px; top: auto !important; left: auto; right: 0;" 
								 class="py-0 dropdown-menu post-account">	
								 
								 <a href="<?php echo e($item['post_url']); ?>" 
								   target="_blank" 
								   class="dropdown-item link d-flex py-3 px-3 align-i r">
									<div class="icon-container mx-2 d-flex">
									<?php echo e(get_social_media_image($item['media_type'])); ?>

									</div>
									<span class="text-dark">Show in <?php echo e($item['media_type']); ?></span>
								</a>
								
							</div>
						</div>
					<?php endif; ?>
				</span>
				</div>


                <div class="d-flex align-items-start mt-4" onclick="loadDetail(this)" data-id="<?php echo e($item['id']); ?>" 
                 data-type="<?php echo e(empty($item['conversation_id']) ? 'comment' : 'message'); ?>"
				 data-network="<?php echo e($item['media_type']); ?>"
				 data-post-id="<?php echo e($item['post_id']); ?>"
				 data-conversation-id="<?php echo e($item['conversation_id']); ?>">
				
					<div class="text-gray-600 size-30 min-w-30 d-flex align-items-center justify-content-between position-relative">
						<img data-src="<?php echo e(Media::url($item['from_image'])); ?>" src="<?php echo e(theme_public_asset('img/default.png')); ?>" class="b-r-100 w-full h-full border-1 lazyload" onerror="this.src='<?php echo e(theme_public_asset('img/default.png')); ?>'">
						<span class="position-absolute b-0 r-0">
							<div class="w-100"><?php echo get_social_media_icon($item['media_type']); ?></div>
						</span>
					</div>
                    <div class="flex-grow-1 ms-2">
                        <div class="">
                            <h6 class="fw-7 mb-0 fs-11"><?php echo e($item['from_name']); ?></h6>
                            <small class="text-muted fs-10"><?php echo e(date('M d, Y, h:i a', strtotime($item['created_time']))); ?></small>
                        </div>
                        <p class="mb-1"><?php echo e($item['message']); ?></p>
                    </div>
                </div>
                
				<?php if(!empty($item['user_names'])): ?>
                    <div class="mt-2 user-roles-<?php echo e($item['id']); ?>">
                        <?php $__currentLoopData = explode(',', $item['user_names']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $us): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge bg-secondary me-1">
                                <i class="fa-flag fal me-1"></i><?php echo e($us); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
				
				
                <?php if(!empty($item['tag_names'])): ?>
                    <div class="mt-2 tag-roles-<?php echo e($item['id']); ?>">
                        <?php $__currentLoopData = explode(',', $item['tag_names']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge bg-secondary me-1">
                                <i class="fa-tag fal me-1"></i><?php echo e($tag); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
				
				
				
				
				<div class="mt-3 d-flex justify-content-between align-items-center">
					<?php if($item['last_reviewed_user_id'] > 0): ?>
						<p class="fs-11 text-gray-600 mb-0">
							Last reviewed: <?php echo e(get_user_name($item['last_reviewed_user_id'])); ?> <?php echo e(date("M d, Y, h:ia", strtotime($item['last_reviewed_date']))); ?>

						</p>
					<?php else: ?>
						<p class="fs-11 text-gray-600 mb-0"></p>
					<?php endif; ?>
					
					<?php if($item['is_completed'] == 0): ?>
						<a href="javascript:void(0)" 
						   data-completed="<?php echo e($item['is_completed']); ?>" 
						   data-id="<?php echo e($item['id']); ?>" 
						   data-conversation-id="<?php echo e($item['conversation_id']); ?>" 
						   class="btn btn-primary btn-sm text-nowrap" 
						   onclick="click_complete(this)">
							Mark Complete
						</a>
					<?php else: ?>
						<a href="javascript:void(0)" 
						   data-completed="<?php echo e($item['is_completed']); ?>" 
						   data-id="<?php echo e($item['id']); ?>" 
						   data-conversation-id="<?php echo e($item['conversation_id']); ?>" 
						   class="btn btn-primary btn-sm text-nowrap" 
						   onclick="click_complete(this)">
							Mark Incomplete
						</a>
					<?php endif; ?>
				</div>
				
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