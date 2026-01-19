@if(!empty($inbox_list))
    <div class="inbox-messages-list">
        @foreach($inbox_list as $item)
            <div class="inbox-item inbox-<?php echo $item['id']; ?> py-3 border-bottom {{ $item['is_completed'] ? 'completed' : '' }}" 
                 data-id="{{ $item['id'] }}" 
                 data-type="{{ empty($item['conversation_id']) ? 'comment' : 'message' }}"
				 data-network="{{ $item['media_type'] }}"
				 data-post-id="{{ $item['post_id'] }}"
				 data-conversation-id="{{ $item['conversation_id'] }}">
				 <div class="d-flex align-items-center gap-4 justify-content-between">
				<div class="d-flex" 
					 data-id="{{ $item['id'] }}" 
					 data-conversation-id="{{ $item['conversation_id'] }}" 
					 data-post-id="{{ $item['post_id'] }}" 
					 data-network="{{ $item['media_type'] }}" 
					 style="cursor:pointer">
					
					<div class="custom-control custom-checkbox me-2 d-flex align-items-center">
						<input name="inbox_check[]" 
							   type="checkbox" 
							   class="inbox_checkbox_input custom-control-input" 
							   value="{{ (empty($item['conversation_id']) ? 'inbox_comments--' : 'inbox--') . $item['id'] }}" 
							   id="{{ (empty($item['conversation_id']) ? 'inbox_comments--' : 'inbox--') . $item['id'] }}">
						<label class="custom-control-label" 
							   for="{{ (empty($item['conversation_id']) ? 'inbox_comments--' : 'inbox--') . $item['id'] }}">
						</label>
					</div>	
										
					<div onclick="loadDetail(this)" data-id="{{ $item['id'] }}" 
                 data-type="{{ empty($item['conversation_id']) ? 'comment' : 'message' }}"
				 data-network="{{ $item['media_type'] }}"
				 data-post-id="{{ $item['post_id'] }}"
				 data-conversation-id="{{ $item['conversation_id'] }}" class="post-account">
						<div class="text-gray-600 size-30 min-w-30 d-flex align-items-center justify-content-between position-relative">
							<img data-src="{{ Media::url($item['to_image']) }}" src="{{ theme_public_asset('img/default.png') }}" class="b-r-100 w-full h-full border-1 lazyload" onerror="this.src='{{ theme_public_asset('img/default.png') }}'">
							<span class="position-absolute b-0 r-0">
								<div class="w-100">{!! get_social_media_icon($item['media_type']) !!}</div>
							</span>
						</div>
						
					</div>
					
					<span onclick="loadDetail(this)" 
						  data-id="{{ $item['id'] }}" 
                 data-type="{{ empty($item['conversation_id']) ? 'comment' : 'message' }}"
				 data-network="{{ $item['media_type'] }}"
				 data-post-id="{{ $item['post_id'] }}"
				 data-conversation-id="{{ $item['conversation_id'] }}"
						  style="cursor:pointer" 
						  class="ms-2 fw-7 mb-0 fs-11">
						@if($item['media_type'] == 'twitter')
							{{ $item['to_name'] }}
							<span class="chip chip-blue">
								<span>Direct Message</span>
							</span>
						@else
							{{ $item['to_name'] }} (Page) 
							<span class="chip chip-blue d-block fs-10 fw-5">
								<span>{{ $item['inbox_type'] }}</span>
							</span>
						@endif
					</span>
				</div> 
				<span class="d-flex">
					<a 
					   data-id="{{ $item['id'] }}" 
					   data-conversation-id="{{ $item['conversation_id'] }}" 
					   data-post-id="{{ $item['post_id'] }}" 
					   data-network="{{ $item['media_type'] }}" 
					   data-toggle="tooltip" 
					   data-placement="top" 
					   title="Reply" 
					   href="javascript:void(0);" 
					   class="icon-with-circle me-2">
						{!! file_get_contents(theme_public_asset('img/reply.svg')) !!}
					</a>
					
					<a data-toggle="tooltip" 
					   data-placement="top" 
					   title="Tag It" 
					   href="javascript:void(0);" 
					   class="icon-with-circle me-2 tag-icon-ids" 
					   data-tag-ids="{{ !empty($item['tag_ids']) ? $item['tag_ids'] : '' }}" 
					   data-id="{{ $item['id'] }}" 
					   onclick="open_list_tag(this,'{{ empty($item['conversation_id']) ? 'inbox_comments' : 'inbox' }}')">
						{!! file_get_contents(theme_public_asset('img/tag.svg')) !!}
					</a>
					
					<a data-toggle="tooltip" 
					   data-placement="top" 
					   title="Assign It" 
					   href="javascript:void(0);" 
					   class="icon-with-circle me-2 user-icon-ids" 
					   data-user-ids="{{ !empty($item['user_ids']) ? $item['user_ids'] : '' }}" 
					   data-id="{{ $item['id'] }}" 
					   onclick="open_list_user(this,'{{ empty($item['conversation_id']) ? 'inbox_comments' : 'inbox' }}')">
						{!! file_get_contents(theme_public_asset('img/flag.svg')) !!}
					</a>
					
					<a data-toggle="tooltip" 
					   data-placement="top" 
					   title="Add to Favorite" 
					   href="javascript:void(0);" 
					   class="icon-with-circle me-2 {{ $item['is_favourite'] == 1 ? 'is_fav' : '' }}" 
					   data-id="{{ $item['id'] }}" 
					   data-fav="{{ $item['is_favourite'] }}" 
					   onclick="favourite_toggle(this,'{{ empty($item['conversation_id']) ? 'inbox_comments' : 'inbox' }}')">
						{!! file_get_contents(theme_public_asset('img/heart.svg')) !!}
					</a>
					
					<a data-toggle="tooltip" 
					   data-placement="top" 
					   title="Delete" 
					   href="javascript:void(0)" 
					   class="icon-with-circle" 
					   onclick="delete_inbox_message('{{ $item['id'] }}','{{ empty($item['conversation_id']) ? 'inbox_comments' : 'inbox' }}')">
						{!! file_get_contents(theme_public_asset('img/delete.svg')) !!}
					</a>
					
					@if(!empty($item['post_url']))
						<div class="d-inline-block dropdown">
							<a href="javascript:void(0)" data-bs-toggle="dropdown" class="dropdown-toggle link text-muted d-flex w-30 h-30 icon-with-circle fs-18 justify-content-center ms-2" aria-expanded="false">
								<i class="fa fa-ellipsis-v"></i>
							</a>
						
							<div style="min-width: 240px; top: auto !important; left: auto; right: 0;" 
								 class="py-0 dropdown-menu post-account">	
								 
								 <a href="{{ $item['post_url'] }}" 
								   target="_blank" 
								   class="dropdown-item link d-flex py-3 px-3 align-i r">
									<div class="icon-container mx-2 d-flex">
									{{get_social_media_image($item['media_type'])}}
									</div>
									<span class="text-dark">Show in {{$item['media_type']}}</span>
								</a>
								
							</div>
						</div>
					@endif
				</span>
				</div>


                <div class="d-flex align-items-start mt-4" onclick="loadDetail(this)" data-id="{{ $item['id'] }}" 
                 data-type="{{ empty($item['conversation_id']) ? 'comment' : 'message' }}"
				 data-network="{{ $item['media_type'] }}"
				 data-post-id="{{ $item['post_id'] }}"
				 data-conversation-id="{{ $item['conversation_id'] }}">
				
					<div class="text-gray-600 size-30 min-w-30 d-flex align-items-center justify-content-between position-relative">
						<img data-src="{{ Media::url($item['from_image']) }}" src="{{ theme_public_asset('img/default.png') }}" class="b-r-100 w-full h-full border-1 lazyload" onerror="this.src='{{ theme_public_asset('img/default.png') }}'">
						<span class="position-absolute b-0 r-0">
							<div class="w-100">{!! get_social_media_icon($item['media_type']) !!}</div>
						</span>
					</div>
                    <div class="flex-grow-1 ms-2">
                        <div class="">
                            <h6 class="fw-7 mb-0 fs-11">{{ $item['from_name'] }}</h6>
                            <small class="text-muted fs-10">{{ date('M d, Y, h:i a', strtotime($item['created_time'])) }}</small>
                        </div>
                        <p class="mb-1">{{ $item['message'] }}</p>
                    </div>
                </div>
                
				@if(!empty($item['user_names']))
                    <div class="mt-2 user-roles-{{ $item['id'] }}">
                        @foreach(explode(',', $item['user_names']) as $us)
                            <span class="badge bg-secondary me-1">
                                <i class="fa-flag fal me-1"></i>{{ $us }}
                            </span>
                        @endforeach
                    </div>
                @endif
				
				
                @if(!empty($item['tag_names']))
                    <div class="mt-2 tag-roles-{{ $item['id'] }}">
                        @foreach(explode(',', $item['tag_names']) as $tag)
                            <span class="badge bg-secondary me-1">
                                <i class="fa-tag fal me-1"></i>{{ $tag }}
                            </span>
                        @endforeach
                    </div>
                @endif
				
				
				
				
				<div class="mt-3 d-flex justify-content-between align-items-center">
					@if($item['last_reviewed_user_id'] > 0)
						<p class="fs-11 text-gray-600 mb-0">
							Last reviewed: {{ get_user_name($item['last_reviewed_user_id']) }} {{ date("M d, Y, h:ia", strtotime($item['last_reviewed_date'])) }}
						</p>
					@else
						<p class="fs-11 text-gray-600 mb-0"></p>
					@endif
					
					@if($item['is_completed'] == 0)
						<a href="javascript:void(0)" 
						   data-completed="{{ $item['is_completed'] }}" 
						   data-id="{{ $item['id'] }}" 
						   data-conversation-id="{{ $item['conversation_id'] }}" 
						   class="btn btn-primary btn-sm text-nowrap" 
						   onclick="click_complete(this)">
							Mark Complete
						</a>
					@else
						<a href="javascript:void(0)" 
						   data-completed="{{ $item['is_completed'] }}" 
						   data-id="{{ $item['id'] }}" 
						   data-conversation-id="{{ $item['conversation_id'] }}" 
						   class="btn btn-primary btn-sm text-nowrap" 
						   onclick="click_complete(this)">
							Mark Incomplete
						</a>
					@endif
				</div>
				
            </div>
        @endforeach
    </div>

    <div class="pagination-container mt-3">
        {!! $pagerContainer !!}
    </div>
@else
    <div class="text-center py-5">
        <i class="fa-light fa-inbox fa-3x text-muted"></i>
        <p class="mt-3 text-muted">No messages found</p>
    </div>
@endif

