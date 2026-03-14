@foreach($schedules as $value)
    @php
        $data = is_string($value->data) ? json_decode($value->data, true) : (is_array($value->data) ? $value->data : []);
        $caption = $data['caption'] ?? '';
        $link = $data['link'] ?? '';
        $medias = $data['medias'] ?? [];
        $img = is_array($medias) && isset($medias[0]) && $medias[0] ? $medias[0] : 'https://placehold.co/80x80';
        
        $network = $value->social_network ?? 'N/A';
        $type = $value->type ?? 'N/A';
        $status = ($value->result == 1) ? 'Ready' : 'Draft';
		
        // Get comments
        $comments = $value->comments ?? collect();
        $commentCount = $comments->count();
    @endphp

    <div class="{{ (!empty($from) ? 'col-md-12 col-lg-12' : 'col-md-6 col-lg-4') }}  mb-4">
        <div class="card hp-100 draft-card">
			<div class="card-header px-3">
                <input class="form-check-input checkbox-item me-2" type="checkbox" name="id[]">
				<div class="me-auto">
                    <div class="position-relative w-140 after-shadow overflow-hidden">				
						<div class="card-title fw-normal fs-12 horizontal-scroll d-flex">
								@php
    $avatars = collect(explode('|||', $value->avatars ?? ''))->map(function($item) {
        return explode(':::', $item)[1] ?? '';
    })->filter();
    
    $urls = collect(explode('|||', $value->urls ?? ''))->map(function($item) {
        return explode(':::', $item)[1] ?? '';
    })->filter();
    
    $socialNetworks = collect(explode('|||', $value->social_networks ?? ''))->map(function($item) {
        return explode(':::', $item)[1] ?? '';
    })->filter();
    
@endphp
							
							@if(!empty($value->avatars))
								@foreach($avatars as $kj => $ava)								
									<div class="me-2">
										<div class="text-gray-600 size-30 min-w-30 d-flex align-items-center justify-content-between position-relative">
											<img data-src="{{ Media::url($ava) }}" src="{{ theme_public_asset('img/default.png') }}" class="b-r-100 w-full h-full border-1 lazyload" onerror="this.src='{{ theme_public_asset('img/default.png') }}'">									
											<span class="position-absolute b-0 r-0">
												<div class="w-100">{!! get_social_media_icon($socialNetworks[$kj]) !!}</div>
											</span>
										</div>
									</div>
								@endforeach
							@endif
						</div>				
                    </div>
                </div>
				
				
                <div class="d-flex gap-6">
                    <a href="{{ url_app("publishing/preview") }}" class="icon-with-circle actionItem" data-popup="pubishingPreviewModal" data-id="{{ $value->grouping_data }}" data-call-success="AppPubishing.closePopoverCalendar();" data-bs-title="Move to Approval" data-bs-toggle="tooltip" data-bs-placement="top">{!! file_get_contents(public_path('img/arrow.svg')) !!}</a>
                    <a href="{{ module_url("update") }}" class="icon-with-circle actionItem" data-id="{{ $value->id_secure }}" data-popup="groupModal" data-call-success="" data-bs-title="Duplicate Post" data-bs-toggle="tooltip" data-bs-placement="top">
                        {!! file_get_contents(public_path('img/duplicate.svg')) !!}
                    </a>
                    <a href="{{ url_app('publishing/composer?id=' . $value->id_secure) }}" class="icon-with-circle" data-id="{{ $value->id_secure }}" data-append-content="composer-scheduling" data-call-success="AppPubishing.openCompose(); AppPubishing.closePopoverCalendar();" data-bs-title="Edit" data-bs-toggle="tooltip" data-bs-placement="top">
                        {!! file_get_contents(public_path('img/post.svg')) !!}
                    </a>
                    <a href="{{ url_app("publishing/destroy") }}" class="icon-with-circle actionItem" data-id="{{ $value->grouping_data }}" data-call-success="location.reload();" data-bs-title="Delete Post" data-bs-toggle="tooltip" data-bs-placement="top" data-confirm="{{ __('Are you sure to delete this post?') }}">
                        {!! file_get_contents(public_path('img/delete.svg')) !!}
                    </a>
                </div>
            </div>
            <div class="card-body p-3 pt-2">
                <div class="card-content">
                    <p class="lastEdit text-primary fs-12 mb-0">Last Edited: {{ $value->updated_at ? $value->updated_at->format('M d, Y h:i A') : 'N/A' }}</p>
                    <div class="d-flex mt-3">
                        <div class="flex-grow-1">
                            <p class="card-text mb-3 fs-13">{{ $caption ?: 'No caption.' }}</p>
                            <div class="d-flex gap-2 flex-wrap">
                            </div>
                        </div>
                        <div class="size-80 ms-3 overflow-hidden b-r-10 d-flex justify-content-center align-items-center fs-30 text-primary bg-primary-100 border border-primary-200 img-wrap">
                        	@switch($type)
							    @case('media')
							        <img src="{{ Media::url($img) }}" class="img-fluid rounded-3 shadow-sm"/>
							        @break

							    @case('link')
							        <a href="{{ $link }}" target="_blank"><i class="fa-light fa-link"></i></a>
							        @break

							    @default
							        <i class="fa-light fa-align-center"></i>
							@endswitch
                        </div>
                    </div>
                </div>


            </div>
            <div class="card-footer fs-12 d-flex flex-column border-0 gap-8 px-3 align-items-start">
                <div class="scheduleDetails d-flex gap-2 flex-column gap-8 py-2">
                    <div class="d-flex align-items-center gap-8">
                        <div class="text-primary">{!! file_get_contents(public_path('img/time.svg')) !!}</div>
                        <a href="#" class="btn btn-secondary btn-sm">Schedule</a>
                        <p class="s_dateTime mb-0 fw-6 text-gray-500 fs-12">
                            <input type="text" style="color:#7ec476;cursor: pointer;" data-selecteddate="" class="border-0 date_approval fs-12" autocomplete="off" data-id="{{ $value->id }}" name="" value="{{ $value->time_post ? date('m/d/Y h:i A', $value->time_post) : '' }}" readonly="">
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-8">
                        <div class="text-primary">{!! file_get_contents(public_path('img/msg.svg')) !!}</div>
                        <a href="javascript:void(0);" class="btn btn-secondary btn-sm open-comment-modal" data-post-id="{{ $value->id_secure }}">
                            Comment @if($commentCount > 0)<span class="badge bg-primary ms-1">{{ $commentCount }}</span>@endif
                        </a>
                        @if($commentCount > 0)
                            @php $lastComment = $comments->last(); @endphp
                            <p class="s_comment mb-0 fw-6 text-gray-500 fs-12">
                                {{ $lastComment->user_name ?? 'User' }} - {{ Str::limit($lastComment->comment, 30) }} ({{ $lastComment->created_at ? $lastComment->created_at->format('M d, Y h:i A') : '' }})
                            </p>
                        @else
                            <p class="s_comment mb-0 fw-6 text-gray-500 fs-12">No comments yet</p>
                        @endif
                    </div>
                </div>
               
            </div>

        </div>
    </div>
@endforeach

@if($schedules->total() == 0 && $schedules->currentPage() == 1)
<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
    <span class="fs-70 mb-3 text-primary">
        <i class="fa-light fa-file-pen"></i>
    </span>
    <div class="fw-semibold fs-5 mb-2 text-gray-800">
        {{ __('No drafts yet') }}
    </div>
    <div class="text-body-secondary mb-4">
        {{ __('Start by creating a new draft to save your ideas and prepare content before publishing.') }}
    </div>
</div>
@endif