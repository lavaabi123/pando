

<?php
$postType = Access::permission("appfiles") ? "media" : "link";
$caption = "";
$medias = [];
$link = "";
    
if($post){

    switch ($post->type) {
        case 'media':
            $postType = "media";
            break;

        case 'link':
            $postType = "link";
            break;

        case 'text':
            $postType = "text";
            break;
    }

    $postData = json_decode($post->data, 0);

    $caption = $postData->caption;
    $medias = $postData->medias;
    $link = $postData->link;
}

?>
<?php $__env->startSection('content'); ?>
<div class="composer-scheduling compose position-absolute l-0 t-0 wp-100 hp-100 bg-white zIndex-9 overflow-hidden">

    <div class="d-flex hp-100">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check("appfiles")): ?>
        <div class="compose-media d-flex flex-column flex-fill max-w-400 min-w-300 bg-white">
            <?php echo $__env->make('appfiles::block_files', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <?php endif; ?>

        <form class="compose-editor d-flex flex-column flex-fill border-start border-end actionForm bg-white max-w-600 min-w-600" action="<?php echo e(url_app("publishing/save")); ?>" id="compose-editor" data-redirect="<?php echo e(module_url("calendar")); ?>">

            <div class="d-flex flex-column flex-column-fluid overflow-y-auto py-2">
                <div class="max-w-750 wp-100 mx-auto p-3">
                    <div class="mb-3">
                        <?php echo $__env->make('appchannels::block_channels', [
                            'permission' => 'apppublishing',
                            'accounts' => isset($accountIds)?$accountIds:[]
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>

                    <div class="mb-3">
                        <div class="mb-3 wrap-input-emoji">
                            <textarea class="form-control btl-r-15 btr-r-15 input-emoji post-caption fw-4" name="caption" placeholder="<?php echo e(__("Enter caption")); ?>"><?php echo e($caption); ?></textarea>
                            <div class="p-3 border-end border-start border-bottom border-gray-400 compose-type-media">
                                <div class="compose-type-link <?php echo e($postType == 'link' ? '' : 'd-none'); ?>">
                                    <div class="form-control mb-3">
                                        <input placeholder="<?php echo e(__("Enter url")); ?>" class="actionChange" data-url="<?php echo e(module_url("getLinkInfo")); ?>" data-call-success="AppPubishing.previewLink(result);" name="link" type="text" value="<?php echo e($link); ?>" data-loading="false">
                                        <button type="button" class="btn btn-icon">
                                            <i class="fa-light fa-link"></i>
                                        </button>
                                    </div>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check("appfiles")): ?>
                                    <div class="mb-3">
                                        <label class="form-label text-uppercase mb-0 d-flex align-items-center gap-8">
                                            <span><?php echo e(__("Thumbnail")); ?></span>
                                            <span><i class="fa-light fa-circle-question" data-bs-title="<?php echo e(__('Note: Some social networks will take the image of the link without using the thumbnail image.')); ?>" data-bs-toggle="tooltip" data-bs-placement="top"></i></span>    
                                        </label>
                                        <span class="fs-12 text-gray-600"></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check("appfiles")): ?>
                                <div class="compose-type-media">
                                    <?php echo $__env->make('appfiles::block_selected_files', [
                                        "files" => $medias
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center overflow-x-auto border-gray-400 border border-top-0 bbr-r-15 bbl-r-15">
                                <div class="d-flex compose-type">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check("appfiles")): ?>
                                    <div class="border-end border-gray-400">
                                        <label for="compose_type_media" class="px-3 py-2 d-block text-gray-700 activeItem <?php echo e($postType=="media"?"bg-primary-100 text-primary":""); ?>" data-parent=".compose-type" data-add="bg-primary-100 text-primary" data-remove="text-gray-700">
                                            <i class="fa-light fa-camera"></i>
                                        </label>
                                        <input type="radio" name="type" class="d-none" id="compose_type_media" value="media" <?php echo e($postType=="media"?"checked":""); ?>>
                                    </div>
                                    <?php endif; ?>
                                    <div class="border-end border-gray-400">
                                        <label for="compose_type_link" class="px-3 py-2 d-block text-gray-700 activeItem <?php echo e($postType=="link"?"bg-primary-100 text-primary":""); ?>" data-parent=".compose-type" data-add="bg-primary-100 text-primary" data-remove="text-gray-700">
                                            <i class="fa-light fa-link"></i>
                                        </label>
                                        <input type="radio" name="type" class="d-none" id="compose_type_link" value="link" <?php echo e($postType=="link"?"checked":""); ?>>
                                    </div>
                                    <div class="border-end border-gray-400">
                                        <label for="compose_type_text" class="px-3 py-2 d-block text-gray-700 activeItem <?php echo e($postType=="text"?"bg-primary-100 text-primary":""); ?>" data-parent=".compose-type" data-add="bg-primary-100 text-primary" data-remove="text-gray-700">
                                            <i class="fa-light fa-align-center"></i>
                                            <input type="radio" name="type" class="d-none" id="compose_type_text" value="text" <?php echo e($postType=="text"?"checked":""); ?>>
                                        </label>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    
									<?php if(get_option("ai_status", 1) && Gate::allows('appaicontents')): ?>
									<div class="border-start">
										<a href="<?php echo e(route("app.ai-contents.popupAIContent")); ?>" class="px-3 py-2 d-block text-gray-700 actionItem" data-popup="aiContentModal" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="<?php echo e(__('AI Template')); ?>" data-bs-html="true" data-bs-content="<?php echo __('Start by choosing a prompt from the Prompt Templates panel.'); ?>"><i class="fa-light fa-sparkles"></i></a>
									</div>
									<?php endif; ?>
									
                                    <?php if(get_option("ai_status", 1) && Gate::allows('appaicontents')): ?>
                                    <div class="border-start">
                                        <a href="javascript:void(0);" class="px-3 py-2 d-block text-gray-700 generalAIContent" data-url="<?php echo e(route('app.ai-contents.create_content')); ?>" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="<?php echo e(__('AI Content')); ?>" data-bs-html="true" data-bs-content="<?php echo __('Enter a prompt in the caption box and click this button. Our AI will generate the perfect content for you with just one click.<br/><br/><b>Example:</b> Create a motivational quote for Monday morning.'); ?>"><i class="fa-light fa-wand-magic-sparkles p-0"></i></a>
                                    </div>
                                    <?php endif; ?>

                                    <?php if(get_option("url_shorteners_platform", 0) && Gate::allows('appmediasearch')): ?>
                                    <div class="border-start border-gray-400">
                                        <a href="<?php echo e(url_app("url-shorteners/shorten")); ?>" class="px-3 py-2 d-block text-gray-700 text-nowrap actionMultiItem" data-call-success="AppPubishing.shorten(result);" data-bs-title="<?php echo e(__("Shorten Links")); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fa-light fa-link-simple"></i></a>
                                    </div>
                                    <?php endif; ?>

                                    <?php if(Gate::allows('appcaptions')): ?>
                                    <div class="border-start border-gray-400">
                                        <a href="<?php echo e(route('app.hashtags.get_hashtag')); ?>" class="px-3 py-2 d-block text-gray-700 actionItem" data-offcanvas="getHashtagOffCanvas" data-bs-title="<?php echo e(__("Get Hashtag")); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fal fa-hashtag p-0"></i></a>
                                    </div>
                                    <div class="border-start border-gray-400">
                                        <a href="<?php echo e(route('app.captions.get_caption')); ?>" class="px-3 py-2 d-block text-gray-700 actionItem" data-offcanvas="getCaptionOffCanvas" data-bs-title="<?php echo e(__("Get Caption")); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fal fa-closed-captioning p-0"></i></a>
                                    </div>
                                    <div class="border-start border-gray-400">
                                        <a href="<?php echo e(route('app.handles.get_handle')); ?>" class="px-3 py-2 d-block text-gray-700 actionItem" data-offcanvas="getHandleOffCanvas" data-bs-title="<?php echo e(__("Get Handle")); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fal fa-grip p-0"></i></a>
                                    </div>
                                    <div class="border-start border-gray-400">
                                        <a href="<?php echo e(route('app.replies.get_reply')); ?>" class="px-3 py-2 d-block text-gray-700 actionItem" data-offcanvas="getReplyOffCanvas" data-bs-title="<?php echo e(__("Get Replies")); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fal fa-comment-alt-lines p-0"></i></a>
                                    </div>
                                    <!--<div class="border-start">
                                        <a href="<?php echo e(route('app.captions.save_caption')); ?>" class="px-3 py-2 d-block text-gray-700 actionItem" data-popup="saveCaptionModal" data-bs-title="<?php echo e(__("Save caption")); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fal fa-save p-0"></i></a>
                                    </div>-->
                                    <?php endif; ?>
                                    <div class="count-word px-3 text-gray-700 border-gray-400 py-2 border-start">
                                        <span>0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php $__currentLoopData = Channels::channels("apppublishing"); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(!empty($value['items'])): ?>
                            <?php $__currentLoopData = $value['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $view = $item['key'].'::options';
                                ?>

                                <?php if(view()->exists($view)): ?>
                                    <div class="d-none option-network" data-option-network="<?php echo e($value['social_network']); ?>">
                                    <?php echo $__env->make($view, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <?php if (\Illuminate\Support\Facades\Blade::check('canany', 'apppublishingcampaigns', 'apppublishinglabels')): ?>
                    <div class="mb-3">
                        <div class="card shadow-none b-r-15 border-gray-400">
                            <div class="card-header px-3">
                                <div class="fw-5 fs-14">
									<span class="add-icon"><?php echo file_get_contents(public_path('img/add.svg')); ?></span>
                                    <?php if( Gate::allows('apppublishingcampaigns') && Gate::allows('apppublishinglabels')): ?>
                                        <?php echo e(__("Tags & Campaigns")); ?>

                                    <?php elseif(Gate::allows('apppublishingcampaigns')): ?>
                                        <?php echo e(__("Campaigns")); ?>

                                    <?php elseif(Gate::allows('apppublishinglabels')): ?>
                                        <?php echo e(__("Tags")); ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body px-3">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check("apppublishinglabels")): ?>
                                <div class="mb-3">
                                    <label for="labels" class="form-label mb-1"><?php echo e(__("Labels")); ?></label>
                                    <div class="text-gray-600 fs-12 mb-2"><?php echo e(__("Use Labels to organize, filter and report on your content.")); ?></div>
                                    <select class="form-select h-auto" data-control="select2" name="labels" multiple="true" data-placeholder="<?php echo e(__("Add labels")); ?>">
                                        <?php if(!empty( $labels )): ?> 
                                            <?php $__currentLoopData = $labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($value->id_secure); ?>"><?php echo e($value->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <?php endif; ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check("apppublishingcampaigns")): ?>
                                <div class="mb-3">
                                    <label for="labels" class="form-label mb-1"><?php echo e(__("Campaign")); ?></label>
                                    <div class="text-gray-600 fs-12 mb-2"><?php echo e(__("Track and report on your social marketing campaigns with the Campaign Planner, notes and more.")); ?></div>
                                    <select class="form-select h-35" data-control="select2" name="campaign">
                                        <option value=""><?php echo e(__("Add a campaign")); ?></option>
                                        <?php if(!empty( $labels )): ?>
                                            <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($value->id_secure); ?>" data-icon="fa-light fa-bullhorn text-<?php echo e($value->color); ?>"><?php echo e($value->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <div class="card shadow-none b-r-15 border-gray-400 <?php echo e(in_array($post->status??0, [-1,4,5,6]) ? 'd-none' : ''); ?>">

                            <?php if( empty($post) ): ?>

                                <div class="card-header px-3">
                                    <div class="fw-5 fs-14">
                                        <span class="svg-icons"><?php echo file_get_contents(public_path('img/time.svg')); ?></span> <?php echo e(__("When to post")); ?>

                                    </div>
                                    <div class="card-toolbar">
                                        <select class="form-select mw-150 fs-12 b-r-20" name="post_by">
                                            <option value="1" <?php echo e(old('post_by', $post->post_by ?? '') == 1 ? 'selected' : ''); ?>><?php echo e(__("Immediately")); ?></option>
                                            <!--<option value="2" <?php echo e(old('post_by', $post->post_by ?? '') == 2 || isset($date) ? 'selected' : ''); ?>><?php echo e(__("Schedule & Repost")); ?></option>-->
                                            <option value="3" <?php echo e(old('post_by', $post->post_by ?? '') == 3  || isset($date) ? 'selected' : ''); ?>><?php echo e(__("Schedule")); ?></option>
                                            <option value="4" <?php echo e(old('post_by', $post->post_by ?? '') == 4 ? 'selected' : ''); ?>><?php echo e(__("Draft")); ?></option>
                                            <option value="5" <?php echo e(old('post_by', $post->post_by ?? '') == 5 ? 'selected' : ''); ?>><?php echo e(__("Approval")); ?></option>
                                        </select>
                                    </div>
                                </div>

                            <?php else: ?>

                                <?php if($post->status==1 || $post->status==2 || $post->status==3): ?>

                                    <div class="card-header px-3">
                                        <div class="fw-5 fs-14">
                                            <span class="svg-icons"><?php echo file_get_contents(public_path('img/time.svg')); ?></span><?php echo e(__("When to post")); ?>

                                        </div>
                                        <div class="card-toolbar">
                                            <select class="form-select mw-150 fs-12 b-r-20" name="post_by">
                                                <option value="1" <?php echo e(old('status', $post->status ?? '') == 1 ? 'selected' : ''); ?>><?php echo e(__("Immediately")); ?></option>
                                                <!--<option value="2" <?php echo e(old('post_by', $post->post_by ?? '') == 2 ? 'selected' : ''); ?>><?php echo e(__("Schedule & Repost")); ?></option>-->
                                                <option value="3" <?php echo e(old('status', $post->status ?? '') == 3 ? 'selected' : ''); ?>><?php echo e(__("Schedule")); ?></option>
                                                <option value="4" <?php echo e(old('status', $post->status ?? '') == 1 ? 'selected' : ''); ?>><?php echo e(__("Draft")); ?></option>
                                                <option value="5" <?php echo e(old('status', $post->status ?? '') == 2 ? 'selected' : ''); ?>><?php echo e(__("Approval")); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-none">
                                        <input type="text" name="post_id" value="<?php echo e($post->id_secure??''); ?>">
                                        <input type="text" name="draft" value="1">
                                    </div>

                                <?php else: ?>

                                    <div class="d-none">
                                        <input type="text" name="post_by" value="2">
                                        <input type="text" name="post_id" value="<?php echo e($post->id_secure??''); ?>">
                                    </div>

                                <?php endif; ?>

                            <?php endif; ?>

                            <div class="post-by d-none" data-by="2">
                                <div class="card-body px-3">
                                    <div class="post-by" data-by="2">
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label class="form-label"><?php echo e(__("Time post")); ?></label>
                                                <input type="text" class="form-control datetime datetime fs-12" autocomplete="off" name="time_post" value="<?php echo e(isset($post->time_post) ? datetime_show($post->time_post) : $date); ?>">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label"><?php echo e(__("Interval per post (minute)")); ?></label>
                                                <input type="number" class="form-control fs-12" autocomplete="off" name="interval_per_post" value="<?php echo e($post->delay ?? '0'); ?>">
                                            </div>
                                        </div>
                                        <div class="row post-repost">
                                            <div class="col-6">
                                                <label class="form-label"><?php echo e(__('Repost frequency (day)')); ?></label>
                                                <select class="form-control fs-12" name="repost_frequency">
                                                    <?php for( $i = 0; $i < 60; $i++ ): ?>
                                                        <option value="<?php echo e($i); ?>" <?php if(old('repost_frequency', $post->repost_frequency ?? '') == $i): echo 'selected'; endif; ?> ><?php echo e($i==0?__("Disable"):$i); ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label"><?php echo e(__('Repost until')); ?></label>
                                                <input type="text" class="form-control datetime fs-12" autocomplete="off" name="repost_until" value="<?php echo e(isset($post->repost_until) ? datetime_show($post->repost_until) : $date); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
							<div class="post-by d-none" data-by="5">
                                <div class="card-body px-3">
                                    <div class="post-by" data-by="5">
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label class="form-label"><?php echo e(__("Time post")); ?></label>
                                                <input type="text" class="form-control datetime datetime fs-12" autocomplete="off" name="time_post" value="<?php echo e(isset($post->time_post) ? datetime_show($post->time_post) : $date); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="post-by <?php echo e((!empty($date) || (!empty($post->status) && $post->status == 3))? '' : 'd-none'); ?>" data-by="3">
                                <div class="card-body border-top p-20 listPostByDays">
                                    <div class="item my-1">
                                        <div class="input-group mb-3">
                                            <div class="form-control">
                                                <input type="text" class="datetime" name="time_posts[]" value="<?php echo e(isset($post->time_post) ? datetime_show($post->time_post) : $date); ?>">
                                                <i class="fa-light fa-calendar-days"></i>
                                            </div>
                                            <button type="button" class="btn btn-input remove disabled">
                                                <i class="fad fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer py-1 p-r-20 p-l-20 d-none">
                                    <a href="javascript:void(0);" class="me-5 mb-0 py-2 fs-12 addSpecificDays">
                                        <i class="fal fa-plus"></i> <?php echo e(__("Add more scheduled times")); ?>

                                    </a>

                                    <div class="tempPostByDays d-none">
                                        <div class="item my-1">
                                            <div class="input-group mb-3">
                                                <div class="form-control">
                                                    <input type="text" value="">
                                                    <i class="fa-light fa-calendar-days"></i>
                                                </div>
                                                <button type="button" class="btn btn-input btn-hover-danger remove">
                                                    <i class="fad fa-trash"></i>
                                                </button>
                                            </div>

                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>  
            </div>

            <div class="mt-auto border-top">
                <div class="d-flex justify-content-between align-items-center max-w-750 mx-auto p-3">
                    <div class="d-flex gap-8 align-items-center">
                        <div class="d-block d-sm-block d-md-none ">
                            <div class="btn btn-outline btn-info showPreview">
                                <i class="fa-light fa-eye"></i> <?php echo e(__("Preview")); ?>

                            </div>
                        </div>
                        <?php if(get_option("ai_status", 1) && Gate::allows('appaicontents')): ?>
                        <!--<a href="<?php echo e(route("app.ai-contents.popupAIContent")); ?>" class="btn btn-light actionItem" data-popup="aiContentModal"><i class="fa-light fa-sparkles"></i><?php echo e(__("AI Template")); ?></a> -->
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php
                            if( empty($post) ){
                                if($date){
                                    $button = 2;
                                }else{
                                    $button = 1;
                                }
                            }else{
                                if ($post->status==1){
                                    $button = 3;
                                }else if ($post->status==2){
                                    $button = 4;
                                }else{
                                    $button = 2;
                                }
                            }
                        ?>
                        <button class="btn btn-dark btnPostNow <?php echo e($button == 1 ? '' : 'd-none'); ?>"><?php echo e(__("Post now")); ?></button>
                        <button class="btn btn-dark btnSchedulePost <?php echo e($button == 2 ? '' : 'd-none'); ?>"><?php echo e(__("Schedule")); ?></button>
                        <button class="btn btn-dark btnSaveDraft <?php echo e($button == 3 ? '' : 'd-none'); ?>"><?php echo e(__(" Save as Draft")); ?></button>
                        <button class="btn btn-dark btnSaveApproval <?php echo e($button == 4 ? '' : 'd-none'); ?>"><?php echo e(__(" Save as Approval")); ?></button>
                    </div>
                </div>
            </div>
            
        </form>
		<div class="w-100 position-relative p-4 overflow-auto">
		<ul class="nav nav-tabs position-relative" id="myTab" role="tablist">
			  <li class="nav-item" role="presentation">
				<button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true"><span class="text">Scheduled Post</span></button>
			  </li>
			  <li class="nav-item" role="presentation">
				<button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Draft Post</button>
			  </li>
			  <li class="nav-item" role="presentation">
				<button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Approval List</button>
			  </li>
			  <li class="nav-item" role="presentation">
				<button class="nav-link preview_modal" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview" type="button" role="tab" aria-controls="preview" aria-selected="false">Preview</button>
			  </li>
			</ul>
			<div class="tab-content" id="myTabContent">	
				<div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
					<div class="compose-calendar-new compose-preview d-flex flex-column flex-fill bg-white min-w-300">
					<div class="wp-100 mx-auto p-4">
					<div class="calendar-header">
						
						<div class="border p-2 b-r-20 d-flex flex-wrap gap-8 justify-content-between align-items-center w-sm-100">
							<div class="btn-group btn-group-sm d-none d-sm-flex gap-10">
								<button type="button" class="btn btn-light calendar-event-new active" data-calendar-type="dayGridMonth" data-bs-title="<?php echo e(__("Month view")); ?>" data-bs-toggle="tooltip" data-bs-placement="top">
									Monthly
								</button>
								<button type="button" class="btn btn-light calendar-event-new" data-calendar-type="timeGridWeek" data-bs-title="<?php echo e(__("Week view")); ?>" data-bs-toggle="tooltip" data-bs-placement="top">
									Weekly
								</button>
								<button type="button" class="btn btn-light calendar-event-new d-none" data-calendar-type="listWeek" data-bs-title="<?php echo e(__("List view")); ?>" data-bs-toggle="tooltip" data-bs-placement="top">
									List
								</button>
							</div>
							<div class="d-flex">
								<div class="btn-group">	
									<button data-toggle="tooltip" data-placement="bottom" title="" data-bs-original-title="Add a note to a calendar date" class="bg-transparent d-flex align-items-center dropdown-toggle dropdown-arrow-hide">
										<span class="add-icon"><?php echo file_get_contents(public_path('img/note.svg')); ?></span> <?php echo e(__('Take Notes')); ?>

									</button>
								</div>
							</div>
						</div>
							
						<div class="w-sm-100 position-relative my-3">
							<div class="cTitle d-flex justify-content-center align-items-center gap-20">
								<div class="fs-20 calendar-event-new" data-calendar-type="prev">
									<i class="fa-light fa-angle-left"></i>
								</div>
								<div class="fs-16 fw-6 text-gray-800 calendar-title d-block d-md-none"></div>
								<div class="fs-20 fw-6 text-gray-800 calendar-title d-none d-md-block"></div>
								<div class="fs-20 calendar-event-new" data-calendar-type="next">
									<i class="fa-light fa-angle-right"></i>
								</div>
							</div>
							<!--<div class="d-none d-md-block">
								<div class="btn btn-sm btn-light b-r-50 border-gray-300 calendar-event-new" data-calendar-type="today"><?php echo e(__("Today")); ?></div>
							</div>-->
							<div class="d-flex position-absolute end-0 top-0">
								<div class="btn-group position-static mr-12">
									<button type="button" class="bg-transparent dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
										<i class="fa-light fa-filter text-gray-500"></i> <!--<?php echo e(__("Filters")); ?>-->
									</button>
									<div class="dropdown-menu dropdown-menu-end border-1 border-gray-300 w-full max-w-250" data-popper-placement="bottom-end">
										<div class="d-flex border-bottom px-3 py-2 fw-6 fs-16 gap-8">
											<span><i class="fa-light fa-filter"></i></span>
											<span><?php echo e(__("Filters")); ?></span>
										</div>
										<div class="p-3">
											<div class="mb-3">
												<label class="form-label"><?php echo e(__("Status")); ?></label>
												<select class="form-select calendar-filter" name="status">
													<option value=""><?php echo e(__("All")); ?></option>
													<option value="3"><?php echo e(__("Processing")); ?></option>
													<option value="4"><?php echo e(__("Published")); ?></option>
													<option value="5"><?php echo e(__("Unpublished")); ?></option>
													<option value="1"><?php echo e(__("Active")); ?></option>
													<option value="2"><?php echo e(__("Waiting Approve")); ?></option>
													<option value="6"><?php echo e(__("Pause/Stop")); ?></option>
												</select>
											</div>
											<div class="mb-3">
												<label class="form-label"><?php echo e(__("Social network")); ?></label>
												<select class="form-select calendar-filter" name="module_name">
													<option value=""><?php echo e(__("All")); ?></option>
													<?php if( !empty( Channels::channels() ) ): ?>
														<?php $__currentLoopData = Channels::channels(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
															
															<?php if( !empty( $channel ) && isset( $channel['items']  ) ): ?>
																<?php $__currentLoopData = $channel['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																	<option value="<?php echo e($item['id']); ?>"><?php echo e($item['module_name']); ?></option>
																<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
															<?php endif; ?>
												   
														<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
													<?php endif; ?>
												</select>
											</div>

											<div class="mb-3">
												<label class="form-label"><?php echo e(__("Campaign")); ?></label>
												<select class="form-select calendar-filter" name="campaign">
													<option value=""><?php echo e(__("All")); ?></option>
													<?php if( !empty( $campaigns ) ): ?>
														<?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
															<option value="<?php echo e($value->id); ?>"><?php echo e($value->name); ?></option>
														<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
													<?php endif; ?>
												</select>
											</div>

											<div class="mb-3">
												<label class="form-label"><?php echo e(__("Labels")); ?></label>
												<select class="form-select calendar-filter" name="label">
													<option value=""><?php echo e(__("All")); ?></option>
													<?php if( !empty( $labels ) ): ?>
														<?php $__currentLoopData = $labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
															<option value="<?php echo e($value->id); ?>"><?php echo e($value->name); ?></option>
														<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
													<?php endif; ?>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="btn-group">
									<button class="bg-transparent dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown">
										<i class="fa fa-ellipsis-v fw-bolder text-gray-500"></i> <!--<?php echo e(__('Actions')); ?>-->
									</button>
									<ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-150">
										<li>
											<a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="<?php echo e(module_url("destroy-by-filters")); ?>" data-confirm="<?php echo e(__("Delete all scheduled posts matching your filters. Are you sure?")); ?>" data-call-success="AppPubishing.reloadCalendar();">
												<span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
												<span><?php echo e(__('Delete')); ?></span>
											</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<div class="calendar-scroll">
						<div id='calendar-new'></div>
					</div>
					<div class="schedule-list mt-4">
					
					</div>					
					
				<div class="calendar-event-item-new d-none">
					<div class="card text-wrap border-2 mb-1 shadow-none border-primary-200 event-item wp-100" 
						 data-date="[[date]]" 
						 data-grouping-data="[[grouping_data]]">
						<div class="card-body px-2 py-0">
							<div class="d-flex flex-grow-1 align-items-center justify-content-center gap-8 w-100">
								<div class="text-center">
									<div class="fw-bold fs-10">[[post_count]]</div>
									
								</div>
							</div>	
						</div>
					</div>
				</div>

				</div>			
				
	</div></div>
				 <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
					<div class="d-flex flex-column flex-column-fluid overflow-y-auto p-3 hp-100">
						<div class="max-w-450 wp-100 mx-auto">
							<div id="draft-list-content">
								
								<div class="text-center py-5">
									<div class="spinner-border text-primary" role="status">
										<span class="visually-hidden">Loading...</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				 <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
					<div class="d-flex flex-column flex-column-fluid overflow-y-auto p-3 hp-100">
						<div class="max-w-450 wp-100 mx-auto">
							<div id="approval-list-content">
								
								<div class="text-center py-5">
									<div class="spinner-border text-primary" role="status">
										<span class="visually-hidden">Loading...</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="preview" role="tabpanel" aria-labelledby="preview-tab">				
			
					<div class="d-flex flex-column flex-column-fluid overflow-y-auto p-3 hp-100">			
						
						<div class="max-w-450 wp-100 mx-auto ">

							<?php $__currentLoopData = Channels::channels('apppublishing'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

								<?php
									$view = $value['key'].'::preview';
								?>

								<?php if(view()->exists($view)): ?>
									<div class="cpv pb-3" data-social-network="<?php echo e($value['social_network']); ?>">
										<div class="d-flex align-items-center gap-8 my-3">
											<i class="<?php echo e($value['icon']); ?>" style="color: <?php echo e($value['color']); ?>;"></i>
											<span><?php echo e(__($value['name'])); ?></span>
										</div>
								
										<?php echo $__env->make($view, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
									</div>
								<?php endif; ?>
								
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

							<div class="cpv-empty mt-0">
								<div class="py-2 text-gray-700 fs-13"><?php echo e(__('Choose a profile and enter your post to see a preview.')); ?></div>
								<div class="border border-gray-400 rounded bg-white">
				
									<div class="d-flex pf-13">
										
										<div class="d-flex align-items-center gap-8">
											<div class="size-40 size-child bg-gray-200 b-r-50">
											   
											</div>
											<div class="d-flex align-items-center flex-row-fluid flex-wrap">
												<div class="flex-grow-1 me-2 text-truncate">
													<a href="javascript:void(0);" class=" h-12 bg-gray-200 mb-2 d-block w-180"></a>
													<span class="h-12 bg-gray-200 mb-2 d-block w-120"></span>
												</div>
											</div>
										</div>

									</div>

									<div class="mb-0">
										<div class="fs-14 px-3 mb-3 text-truncate-5">
											<div class="h-12 bg-gray-200 mb-1"></div>
											<div class="h-12 bg-gray-200 mb-1"></div>
											<div class="h-12 bg-gray-200 mb-1 wp-50"></div>
										</div>
										<div class="w-100">
											<img src="<?php echo e(theme_public_asset( "img/default.png" )); ?>" class="w-100">
										</div>
									</div>

								</div>

							</div>

						</div>

					</div>
			
			  </div>
			</div>
		</div>

</div>

</div>

<!-- POST CONFIRM -->
<div class="modal fade" id="confirmPostModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <input type="text" class="d-none" name="type" value="0">
            <div class="modal-header">
                <h1 class="modal-title fs-16"><?php echo e(__("Errors")); ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body data-post-confirm fs-14">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('No, Cancel')); ?></button>
                <a href="<?php echo e(url_app("publishing/save?confirm=true")); ?>" class="btn btn-dark actionMultiItem" data-form=".compose-editor" data-call-before="Main.closeModal('confirmPostModal');" ><?php echo e(__("Yes, I'm sure")); ?></a>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<style>
.fc .fc-daygrid-body-unbalanced .fc-daygrid-day-events{
	min-height:0em;
}
.fc {
    min-width: auto;
}
.fc-highlight-day {
    background-color: #e3f2fd !important;
    border: 2px solid #2196f3 !important;
}
</style>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppPublishing\resources/views/composer.blade.php ENDPATH**/ ?>