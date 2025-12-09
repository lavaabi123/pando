@php
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

@endphp

<div class="compose position-absolute l-0 t-0 wp-100 hp-100 bg-white zIndex-9 overflow-hidden d-none">

    <div class="d-flex hp-100">
        @can("appfiles")
        <div class="compose-media d-flex flex-column flex-fill max-w-400 min-w-300 bg-white d-none">
            @include('appfiles::block_files')
        </div>
        @endcan

        <form class="compose-editor d-flex flex-column flex-fill border-start border-end actionForm bg-white max-w-600 min-w-600" action="{{ url_app("publishing/save") }}" id="compose-editor" data-call-after="AppPubishing.confirmPostModal(result);" data-call-success="AppPubishing.closeCompose(); AppPubishing.reloadCalendar(); Main.ajaxScroll(true);">

            <div class="d-flex flex-column flex-column-fluid overflow-y-auto py-2">
                <div class="max-w-750 wp-100 mx-auto p-3">
                    <div class="mb-3">
                        @include('appchannels::block_channels', [
                            'permission' => 'apppublishing',
                            'accounts' => isset($post->account_id)?[$post->account_id]:[]
                        ])
                    </div>

                    <div class="mb-3">
                        <div class="mb-3 wrap-input-emoji">
                            <textarea class="form-control btl-r-15 btr-r-15 input-emoji post-caption fw-4" name="caption" placeholder="{{ __("Enter caption") }}">{{ $caption }}</textarea>
                            <div class="p-3 border-end border-start border-bottom border-gray-400 compose-type-media">
                                <div class="compose-type-link {{ $postType == 'link' ? '' : 'd-none' }}">
                                    <div class="form-control mb-3">
                                        <input placeholder="{{ __("Enter url") }}" class="actionChange" data-url="{{ module_url("getLinkInfo") }}" data-call-success="AppPubishing.previewLink(result);" name="link" type="text" value="{{ $link }}" data-loading="false">
                                        <button type="button" class="btn btn-icon">
                                            <i class="fa-light fa-link"></i>
                                        </button>
                                    </div>
                                    @can("appfiles")
                                    <div class="mb-3">
                                        <label class="form-label text-uppercase mb-0 d-flex align-items-center gap-8">
                                            <span>{{ __("Thumbnail") }}</span>
                                            <span><i class="fa-light fa-circle-question" data-bs-title="{{ __('Note: Some social networks will take the image of the link without using the thumbnail image.') }}" data-bs-toggle="tooltip" data-bs-placement="top"></i></span>    
                                        </label>
                                        <span class="fs-12 text-gray-600"></span>
                                    </div>
                                    @endcan
                                </div>
                                @can("appfiles")
                                <div class="compose-type-media">
                                    @include('appfiles::block_selected_files', [
                                        "files" => $medias
                                    ])
                                </div>
                                @endcan
                            </div>
                            <div class="d-flex justify-content-between align-items-center overflow-x-auto border-gray-400 border border-top-0 bbr-r-15 bbl-r-15">
                                <div class="d-flex compose-type">
                                    @can("appfiles")
                                    <div class="border-end border-gray-400">
                                        <label for="compose_type_media" class="px-3 py-2 d-block text-gray-700 activeItem {{ $postType=="media"?"bg-primary-100 text-primary":"" }}" data-parent=".compose-type" data-add="bg-primary-100 text-primary" data-remove="text-gray-700">
                                            <i class="fa-light fa-camera"></i>
                                        </label>
                                        <input type="radio" name="type" class="d-none" id="compose_type_media" value="media" {{ $postType=="media"?"checked":"" }}>
                                    </div>
                                    @endcan
                                    <div class="border-end border-gray-400">
                                        <label for="compose_type_link" class="px-3 py-2 d-block text-gray-700 activeItem {{ $postType=="link"?"bg-primary-100 text-primary":"" }}" data-parent=".compose-type" data-add="bg-primary-100 text-primary" data-remove="text-gray-700">
                                            <i class="fa-light fa-link"></i>
                                        </label>
                                        <input type="radio" name="type" class="d-none" id="compose_type_link" value="link" {{ $postType=="link"?"checked":"" }}>
                                    </div>
                                    <div class="border-end border-gray-400">
                                        <label for="compose_type_text" class="px-3 py-2 d-block text-gray-700 activeItem {{ $postType=="text"?"bg-primary-100 text-primary":"" }}" data-parent=".compose-type" data-add="bg-primary-100 text-primary" data-remove="text-gray-700">
                                            <i class="fa-light fa-align-center"></i>
                                            <input type="radio" name="type" class="d-none" id="compose_type_text" value="text" {{ $postType=="text"?"checked":"" }}>
                                        </label>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    
                                    @if(get_option("ai_status", 1) && Gate::allows('appaicontents'))
                                    <div class="border-start">
                                        <a href="javascript:void(0);" class="px-3 py-2 d-block text-gray-700 generalAIContent" data-url="{{ route('app.ai-contents.create_content') }}" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-title="{{ __('AI Content') }}" data-bs-html="true" data-bs-content="{!! __('Enter a prompt in the caption box and click this button. Our AI will generate the perfect content for you with just one click.<br/><br/><b>Example:</b> Create a motivational quote for Monday morning.') !!}"><i class="fa-light fa-wand-magic-sparkles p-0"></i></a>
                                    </div>
                                    @endif

                                    @if(get_option("url_shorteners_platform", 0) && Gate::allows('appmediasearch'))
                                    <div class="border-start border-gray-400">
                                        <a href="{{ url_app("url-shorteners/shorten") }}" class="px-3 py-2 d-block text-gray-700 text-nowrap actionMultiItem" data-call-success="AppPubishing.shorten(result);" data-bs-title="{{ __("Shorten Links") }}" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fa-light fa-link-simple"></i></a>
                                    </div>
                                    @endif

                                    @if(Gate::allows('appcaptions'))
                                    <div class="border-start border-gray-400">
                                        <a href="{{ route('app.captions.get_cation') }}" class="px-3 py-2 d-block text-gray-700 actionItem" data-offcanvas="getCaptionOffCanvas" data-bs-title="{{ __("Get Hashtag") }}" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fal fa-comment-alt-lines p-0"></i></a>
                                    </div>
                                    <div class="border-start border-gray-400">
                                        <a href="{{ route('app.handles.get_handle') }}" class="px-3 py-2 d-block text-gray-700 actionItem" data-offcanvas="getHandleOffCanvas" data-bs-title="{{ __("Get Handle") }}" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fal fa-comment-alt-lines p-0"></i></a>
                                    </div>
                                    <div class="border-start border-gray-400">
                                        <a href="{{ route('app.replies.get_reply') }}" class="px-3 py-2 d-block text-gray-700 actionItem" data-offcanvas="getReplyOffCanvas" data-bs-title="{{ __("Get Replies") }}" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fal fa-comment-alt-lines p-0"></i></a>
                                    </div>
                                    <!--<div class="border-start">
                                        <a href="{{ route('app.captions.save_cation') }}" class="px-3 py-2 d-block text-gray-700 actionItem" data-popup="saveCaptionModal" data-bs-title="{{ __("Save caption") }}" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fal fa-save p-0"></i></a>
                                    </div>-->
                                    @endif
                                    <div class="count-word px-3 text-gray-700 border-gray-400 py-2 border-start">
                                        <span>0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @foreach(Channels::channels("apppublishing") as $value)
                        @if(!empty($value['items']))
                            @foreach($value['items'] as $item)
                                @php
                                    $view = $item['key'].'::options';
                                @endphp

                                @if(view()->exists($view))
                                    <div class="d-none option-network" data-option-network="{{ $value['social_network'] }}">
                                    @include($view)
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @canany('apppublishingcampaigns', 'apppublishinglabels')
                    <div class="mb-3">
                        <div class="card shadow-none b-r-15 border-gray-400">
                            <div class="card-header px-3">
                                <div class="fs-12 fw-6 text-gray-700">
                                    @if( Gate::allows('apppublishingcampaigns') && Gate::allows('apppublishinglabels'))
                                        {{ __("Tags & Campaigns") }}
                                    @elseif(Gate::allows('apppublishingcampaigns'))
                                        {{ __("Campaigns") }}
                                    @elseif(Gate::allows('apppublishinglabels'))
                                        {{ __("Tags") }}
                                    @endif
                                </div>
                            </div>
                            <div class="card-body px-3">
                                @can("apppublishinglabels")
                                <div class="mb-3">
                                    <label for="labels" class="form-label mb-1">{{ __("Labels") }}</label>
                                    <div class="text-gray-600 fs-12 mb-2">{{ __("Use Labels to organize, filter and report on your content.") }}</div>
                                    <select class="form-select h-auto" data-control="select2" name="labels" multiple="true" data-placeholder="{{ __("Add labels") }}">
                                        @if(!empty( $labels )) 
                                            @foreach($labels as $value)
                                            <option value="{{ $value->id_secure }}">{{ $value->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @endcan

                                @can("apppublishingcampaigns")
                                <div class="mb-3">
                                    <label for="labels" class="form-label mb-1">{{ __("Campaign") }}</label>
                                    <div class="text-gray-600 fs-12 mb-2">{{ __("Track and report on your social marketing campaigns with the Campaign Planner, notes and more.") }}</div>
                                    <select class="form-select h-35" data-control="select2" name="campaign">
                                        <option value="">{{ __("Add a campaign") }}</option>
                                        @if(!empty( $labels ))
                                            @foreach($campaigns as $value)
                                            <option value="{{ $value->id_secure }}" data-icon="fa-light fa-bullhorn text-{{ $value->color }}">{{ $value->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endcanany

                    <div class="mb-3">
                        <div class="card shadow-none b-r-15 border-gray-400 {{ in_array($post->status??0, [-1,4,5,6]) ? 'd-none' : '' }}">

                            @if( empty($post) )

                                <div class="card-header px-3">
                                    <div class="fs-12 fw-6 text-gray-700">
                                        {{ __("When to post") }}
                                    </div>
                                    <div class="card-toolbar">
                                        <select class="form-select mw-150 fs-12" name="post_by">
                                            <option value="1" {{ old('post_by', $post->post_by ?? '') == 1 ? 'selected' : '' }}>{{ __("Immediately") }}</option>
                                            <option value="2" {{ old('post_by', $post->post_by ?? '') == 2 || isset($date) ? 'selected' : '' }}>{{ __("Schedule & Repost") }}</option>
                                            <option value="3" {{ old('post_by', $post->post_by ?? '') == 3 ? 'selected' : '' }}>{{ __("Specific Days & Times") }}</option>
                                            <option value="4" {{ old('post_by', $post->post_by ?? '') == 4 ? 'selected' : '' }}>{{ __("Draft") }}</option>
                                            <option value="5" {{ old('post_by', $post->post_by ?? '') == 5 ? 'selected' : '' }}>{{ __("Approval") }}</option>
                                        </select>
                                    </div>
                                </div>

                            @else

                                @if ($post->status==1 || $post->status==2)

                                    <div class="card-header px-3">
                                        <div class="fs-12 fw-6 text-gray-700">
                                            {{ __("When to post") }}
                                        </div>
                                        <div class="card-toolbar">
                                            <select class="form-select mw-150 fs-12" name="post_by">
                                                <option value="1" {{ old('post_by', $post->post_by ?? '') == 1 ? 'selected' : '' }}>{{ __("Immediately") }}</option>
                                                <option value="2" {{ old('post_by', $post->post_by ?? '') == 2 ? 'selected' : '' }}>{{ __("Schedule & Repost") }}</option>
                                                <option value="3" {{ old('post_by', $post->post_by ?? '') == 3 ? 'selected' : '' }}>{{ __("Specific Days & Times") }}</option>
                                                <option value="4" {{ old('status', $post->status ?? '') == 1 ? 'selected' : '' }}>{{ __("Draft") }}</option>
                                                <option value="5" {{ old('status', $post->status ?? '') == 2 ? 'selected' : '' }}>{{ __("Approval") }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-none">
                                        <input type="text" name="post_id" value="{{ $post->id_secure??'' }}">
                                        <input type="text" name="draft" value="1">
                                    </div>

                                @else

                                    <div class="d-none">
                                        <input type="text" name="post_by" value="2">
                                        <input type="text" name="post_id" value="{{ $post->id_secure??'' }}">
                                    </div>

                                @endif

                            @endif

                            <div class="post-by {{ (old('status', $post->status ?? '') == 1 || old('status', $post->status ?? '') == 2) || (empty($post) && empty($date))? 'd-none' : '' }}" data-by="2">
                                <div class="card-body px-3">
                                    <div class="post-by" data-by="2">
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label class="form-label">{{ __("Time post") }}</label>
                                                <input type="text" class="form-control datetime datetime fs-12" autocomplete="off" name="time_post" value="{{ isset($post->time_post) ? datetime_show($post->time_post) : $date }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">{{ __("Interval per post (minute)") }}</label>
                                                <input type="number" class="form-control fs-12" autocomplete="off" name="interval_per_post" value="{{ $post->delay ?? '0' }}">
                                            </div>
                                        </div>
                                        <div class="row post-repost">
                                            <div class="col-6">
                                                <label class="form-label">{{ __('Repost frequency (day)') }}</label>
                                                <select class="form-control fs-12" name="repost_frequency">
                                                    @for( $i = 0; $i < 60; $i++ )
                                                        <option value="{{ $i }}" @selected(old('repost_frequency', $post->repost_frequency ?? '') == $i) >{{ $i==0?__("Disable"):$i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">{{ __('Repost until') }}</label>
                                                <input type="text" class="form-control datetime fs-12" autocomplete="off" name="repost_until" value="{{ isset($post->repost_until) ? datetime_show($post->repost_until) : $date }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
							<div class="post-by {{ old('status', $post->status ?? '') == 2 || (!empty($post) && !empty($date))? '' : 'd-none' }}" data-by="5">
                                <div class="card-body px-3">
                                    <div class="post-by" data-by="5">
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label class="form-label">{{ __("Time post") }}</label>
                                                <input type="text" class="form-control datetime datetime fs-12" autocomplete="off" name="time_post" value="{{ isset($post->time_post) ? datetime_show($post->time_post) : $date }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="post-by d-none" data-by="3">
                                <div class="card-body border-top p-20 listPostByDays">
                                    <div class="item my-1">
                                        <div class="input-group mb-3">
                                            <div class="form-control">
                                                <input type="text" class="datetime" name="time_posts[]" value="">
                                                <i class="fa-light fa-calendar-days"></i>
                                            </div>
                                            <button type="button" class="btn btn-input remove disabled">
                                                <i class="fad fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer py-1 p-r-20 p-l-20">
                                    <a href="javascript:void(0);" class="me-5 mb-0 py-2 fs-12 addSpecificDays">
                                        <i class="fal fa-plus"></i> {{ __("Add more scheduled times") }}
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
                                <i class="fa-light fa-eye"></i> {{ __("Preview") }}
                            </div>
                        </div>
                        @if(get_option("ai_status", 1) && Gate::allows('appaicontents'))
                        <a href="{{ route("app.ai-contents.popupAIContent") }}" class="btn btn-light actionItem" data-popup="aiContentModal"><i class="fa-light fa-sparkles"></i>{{ __("AI Template") }}</a>
                        @endif
                    </div>
                    <div>
                        @php
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
                        @endphp
                        <button class="btn btn-dark btnPostNow {{ $button == 1 ? '' : 'd-none' }}">{{ __("Post now") }}</button>
                        <button class="btn btn-dark btnSchedulePost {{ $button == 2 ? '' : 'd-none' }}">{{ __("Schedule") }}</button>
                        <button class="btn btn-dark btnSaveDraft {{ $button == 3 ? '' : 'd-none' }}">{{ __(" Save as Draft") }}</button>
                        <button class="btn btn-dark btnSaveApproval {{ $button == 4 ? '' : 'd-none' }}">{{ __(" Save as Approval") }}</button>
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
					<div class="d-flex justify-content-between align-items-center gap-8 w-sm-100">
						<div>
							<div class="btn btn-sm btn-light rounded-circle border-gray-300 size-32 fs-20 calendar-event-new" data-calendar-type="prev">
								<i class="fa-light fa-angle-left"></i>
							</div>
						</div>
						<div class="fs-16 fw-6 text-gray-800 calendar-title d-block d-md-none"></div>
						<div class="fs-20 fw-6 text-gray-800 calendar-title d-none d-md-block"></div>
						<div>
							<div class="btn btn-sm btn-light rounded-circle border-gray-300 size-32 fs-20 calendar-event-new" data-calendar-type="next">
								<i class="fa-light fa-angle-right"></i>
							</div>
						</div>
						<!--<div class="d-none d-md-block">
							<div class="btn btn-sm btn-light b-r-50 border-gray-300 calendar-event-new" data-calendar-type="today">{{ __("Today") }}</div>
						</div>-->
					</div>
					<div class="d-flex flex-wrap gap-8 justify-content-center align-items-center w-sm-100">
						<div class="btn-group btn-group-sm d-none d-sm-block">
							<button type="button" class="btn btn-light calendar-event-new" data-calendar-type="dayGridMonth" data-bs-title="{{ __("Month view") }}" data-bs-toggle="tooltip" data-bs-placement="top">
								<i class="fa-light fa-calendar-days"></i>
							</button>
							<button type="button" class="btn btn-light calendar-event-new" data-calendar-type="timeGridWeek" data-bs-title="{{ __("Week view") }}" data-bs-toggle="tooltip" data-bs-placement="top">
								<i class="fa-light fa-columns-3"></i>
							</button>
							<button type="button" class="btn btn-light calendar-event-new" data-calendar-type="listWeek" data-bs-title="{{ __("List view") }}" data-bs-toggle="tooltip" data-bs-placement="top">
								<i class="fa-duotone fa-light fa-list-ul"></i>
							</button>
						</div>

						<div class="d-flex">
							<div class="btn-group position-static">
								<button type="button" class="btn btn-outline btn-light btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
									<i class="fa-light fa-filter"></i> {{ __("Filters") }}
								</button>
								<div class="dropdown-menu dropdown-menu-end border-1 border-gray-300 w-full max-w-250" data-popper-placement="bottom-end">
									<div class="d-flex border-bottom px-3 py-2 fw-6 fs-16 gap-8">
										<span><i class="fa-light fa-filter"></i></span>
										<span>{{ __("Filters") }}</span>
									</div>
									<div class="p-3">
										<div class="mb-3">
											<label class="form-label">{{ __("Status") }}</label>
											<select class="form-select calendar-filter" name="status">
												<option value="">{{ __("All") }}</option>
												<option value="3">{{ __("Processing") }}</option>
												<option value="4">{{ __("Published") }}</option>
												<option value="5">{{ __("Unpublished") }}</option>
												<option value="1">{{ __("Active") }}</option>
												<option value="2">{{ __("Waiting Approve") }}</option>
												<option value="6">{{ __("Pause/Stop") }}</option>
											</select>
										</div>
										<div class="mb-3">
											<label class="form-label">{{ __("Social network") }}</label>
											<select class="form-select calendar-filter" name="module_name">
												<option value="">{{ __("All") }}</option>
												@if( !empty( Channels::channels() ) )
													@foreach( Channels::channels() as $channel )
														
														@if( !empty( $channel ) && isset( $channel['items']  ) )
															@foreach( $channel['items'] as $item )
																<option value="{{ $item['id'] }}">{{ $item['module_name'] }}</option>
															@endforeach
														@endif
											   
													@endforeach
												@endif
											</select>
										</div>

										<div class="mb-3">
											<label class="form-label">{{ __("Campaign") }}</label>
											<select class="form-select calendar-filter" name="campaign">
												<option value="">{{ __("All") }}</option>
												@if( !empty( $campaigns ) )
													@foreach( $campaigns as $value )
														<option value="{{ $value->id }}">{{ $value->name }}</option>
													@endforeach
												@endif
											</select>
										</div>

										<div class="mb-3">
											<label class="form-label">{{ __("Labels") }}</label>
											<select class="form-select calendar-filter" name="label">
												<option value="">{{ __("All") }}</option>
												@if( !empty( $labels ) )
													@foreach( $labels as $value )
														<option value="{{ $value->id }}">{{ $value->name }}</option>
													@endforeach
												@endif
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="d-flex">
							<div class="btn-group">	
								<button data-toggle="tooltip" data-placement="bottom" title="" data-bs-original-title="Add a note to a calendar date" class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide">
									<i class="fa-light fa-notes"></i> {{ __('Take Notes') }}
								</button>
							</div>
						</div>
						<div class="d-flex">
							<div class="btn-group">
								<button class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown">
									<i class="fa-light fa-grid-2"></i> {{ __('Actions') }}
								</button>
								<ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-150">
									<li>
										<a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("destroy-by-filters") }}" data-confirm="{{ __("Delete all scheduled posts matching your filters. Are you sure?") }}" data-call-success="AppPubishing.reloadCalendar();">
											<span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
											<span>{{ __('Delete') }}</span>
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
        {{-- Posts will be loaded here when clicking on a date --}}
    </div>
				
				
            <div class="calendar-event-item-new d-none">
				<div class="card text-wrap border-2 mb-1 shadow-none border-primary-200 event-item wp-100" 
					 data-date="[[date]]" 
					 data-grouping-data="[[grouping_data]]">
					<div class="card-body px-2 py-0">
						<div class="d-flex flex-grow-1 align-items-center justify-content-center gap-8 w-100">
							<div class="text-center">
								<div class="fw-bold fs-12">[[post_count]]</div>
								
							</div>
						</div>	
					</div>
				</div>
			</div>

			</div>			
			
</div></div>
<div class="tab-pane fade" id="preview" role="tabpanel" aria-labelledby="preview-tab">
				
			
            <div class="d-flex flex-column flex-column-fluid overflow-y-auto p-3 hp-100">			
                
                <div class="max-w-450 wp-100 mx-auto ">

                    @foreach(Channels::channels('apppublishing') as $value)

                        @php
                            $view = $value['key'].'::preview';
                        @endphp

                        @if(view()->exists($view))
                            <div class="cpv pb-3" data-social-network="{{ $value['social_network'] }}">
                                <div class="d-flex align-items-center gap-8 my-3">
                                    <i class="{{ $value['icon'] }}" style="color: {{ $value['color'] }};"></i>
                                    <span>{{ __($value['name']) }}</span>
                                </div>
                        
                                @include($view)
                            </div>
                        @endif
                        
                    @endforeach

                    <div class="cpv-empty mt-0">
                        <div class="py-2 text-gray-700 fs-13">{{ __('Choose a profile and enter your post to see a preview.') }}</div>
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
                                    <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
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
                <h1 class="modal-title fs-16">{{ __("Errors") }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body data-post-confirm fs-14">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('No, Cancel') }}</button>
                <a href="{{ url_app("publishing/save?confirm=true") }}" class="btn btn-dark actionMultiItem" data-form=".compose-editor" data-call-before="Main.closeModal('confirmPostModal');" >{{ __("Yes, I'm sure") }}</a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    Main.init(false);
    AppPubishing.init(false);
    Files.init(false);
</script>

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