<div class="modal fade" id="pubishingPreviewModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content actionForm" action="<?php echo e(module_url('save')); ?>" data-call-success="Main.closeModal('pubishingPreviewModal'); Main.ajaxScroll(true);">
            <input type="text" class="d-none" name="type" value="0">
            <div class="modal-header">
                <h1 class="modal-title fs-16"><?php echo e(__("Preview")); ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if(isset($frame_posts) && $frame_posts->count() > 0): ?>
                    <div class="d-flex justify-content-between">
                        
                        <ul class="nav nav-pills ms-3" style="float:left" role="tablist">
                            <?php $__currentLoopData = $frame_posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>                                
                                <li class="nav-item">
                                    <a class="btn btn-active-light text-center border-0 <?php echo e($key == 0 ? 'active' : ''); ?>" 
                                       data-bs-toggle="pill" 
                                       data-bs-target="#pills-<?php echo e($value->id); ?>" 
                                       role="tab">
                                        <?php echo e(get_social_media_icon_large($value->social_network)); ?>

                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>

                        
                        <?php if(empty(request()->input('from'))): ?>
                            <?php
								// Check if any post has a URL
								$hasAnyUrl = $frame_posts->contains(function($post) {
									$resApi = json_decode($post->result, true) ?? [];
									return !empty($resApi['url']);
								});
							?>

							<?php if($hasAnyUrl): ?>
								<div class="">
									<div class="sp-menu-dropdown dropdown dropdown-hide-arrow" data-dropdown-spacing="0">
										<a class="dropdown-toggle text-gray-800 d-flex w-30 h-30 icon-with-circle fs-18 justify-content-center" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="fal fa-ellipsis-v fw-bold"></i>
										</a>
										<ul class="dropdown-menu" data-dropdown-spacing="0">
											<?php $__currentLoopData = $frame_posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
												<?php
													$module = $value->social_network ?? '';
													$resApi = json_decode($value->result, true) ?? [];
													$displayName = $module == 'twitter' ? 'X' : ucfirst($module);
												?>
												
												<?php if(!empty($resApi['url'])): ?>
													<li class="p-3">
														<a class="dropdown-item p-0" target="_blank" href="<?php echo e($resApi['url']); ?>">
															<?php echo e(get_social_media_icon_large($value->social_network)); ?>

															Show in <?php echo e($displayName); ?>

														</a>
													</li>
												<?php endif; ?>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										</ul>
									</div>
								</div>
							<?php endif; ?>
                        <?php endif; ?>
                    </div>

                    
                    <div class="tab-content" style="clear: both;">
                        <?php $__currentLoopData = $frame_posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            
                            <div class="tab-pane <?php echo e($key == 0 ? 'active' : ''); ?>" id="pills-<?php echo e($value->id); ?>" role="tabpanel">
                                <?php if($value): ?>
                                     <?php
										$postType = 'media';
										$caption = '';
										$medias = [];
										$link = '';

										if ($value) {
											$postType = $value->type ?? 'media';
											$postData = json_decode($value->data, false);
											$caption = $postData->caption ?? '';
											$medias = $postData->medias ?? [];
											$link = $postData->link ?? '';
										}
									?>

									<div class="d-none">
										<input type="hidden" class="preview-post-type" value="<?php echo e($postType); ?>">

										<?php if($value && $value->account): ?>
											<input type="hidden" class="preview-profile"
												data-social-network="<?php echo e($value->account->social_network ?? ''); ?>"
												data-avatar="<?php echo e($value->account->avatar ? Media::url($value->account->avatar) : ''); ?>"
												data-name="<?php echo e($value->account->name ?? ''); ?>"
												data-username="<?php echo e($value->account->username ?? ''); ?>"
												data-link="<?php echo e($value->account->link ?? ''); ?>">
										<?php endif; ?>

										<div class="preview-list-medias">
											<?php $__currentLoopData = $medias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
												<img src="<?php echo e(Media::url($media)); ?>">
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										</div>

										<textarea class="form-control input-emoji fw-4 border" name="caption" placeholder="<?php echo e(__("Enter caption")); ?>"><?php echo e($caption); ?></textarea>
									</div>

									<?php
										$module = strtolower($value->module ?? '');
										$view = $module ? $module.'::preview' : null;
									?>

									<?php if($view && view()->exists($view)): ?>
										<div class="cpvx" data-social-network="<?php echo e($value->social_network ?? ''); ?>">
											<?php echo $__env->make($view, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
										</div>
									<?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        Preview not available for <?php echo e(ucfirst($module)); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <?php echo e(__('No data found!')); ?>

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