@if(!empty($inbox_list))
    <div class="inbox-messages-list">
        @foreach($inbox_list as $item)
            <div class="inbox-item py-3 border-bottom {{ $item['is_completed'] ? 'completed' : '' }}" 
                 data-id="{{ $item['id'] }}" 
                 data-type="{{ empty($item['conversation_id']) ? 'comment' : 'message' }}"
				 data-network="{{ $item['media_type'] }}"
				 data-post-id="{{ $item['post_id'] }}"
				 data-conversation-id="{{ $item['conversation_id'] }}"
                 onclick="loadDetail(this)">
				 
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
							   value="{{ (empty($item['conversation_id']) ? 'sp_inbox_comments--' : 'sp_inbox--') . $item['id'] }}" 
							   id="{{ (empty($item['conversation_id']) ? 'sp_inbox_comments--' : 'sp_inbox--') . $item['id'] }}">
						<label class="custom-control-label" 
							   for="{{ (empty($item['conversation_id']) ? 'sp_inbox_comments--' : 'sp_inbox--') . $item['id'] }}">
						</label>
					</div>	
										
					<div onclick="load_detail(this)" class="post-account">
						<div class="text-gray-600 size-40 min-w-40 d-flex align-items-center justify-content-between position-relative">
							<img data-src="{{ Media::url($item['to_image']) }}" src="{{ theme_public_asset('img/default.png') }}" class="b-r-100 w-full h-full border-1 lazyload" onerror="this.src='{{ theme_public_asset('img/default.png') }}'">
							<span class="size-17 border-1 b-r-100 position-absolute fs-9 d-flex align-items-center justify-content-between text-center text-white b-0 r-0">
								<div class="w-100">{{get_social_media_icon($item['media_type'])}}</div>
							</span>
						</div>
						
					</div>
					
					<span onclick="load_detail(this)" 
						  data-id="{{ $item['id'] }}" 
						  data-conversation-id="{{ $item['conversation_id'] }}" 
						  data-post-id="{{ $item['post_id'] }}" 
						  data-network="{{ $item['media_type'] }}" 
						  style="cursor:pointer" 
						  class="ml-2 fw-7 mb-0 fs-11">
						@if($item['media_type'] == 'twitter')
							{{ $item['to_name'] }}
							<span class="chip chip-blue ml-2">
								<span>Direct Message</span>
							</span>
						@else
							{{ $item['to_name'] }} (Page) 
							<span class="chip chip-blue ml-2 fs-10 fw-5">
								<span>{{ $item['inbox_type'] }}</span>
							</span>
						@endif
						{{-- <div class="text-muted small pt-1">5055 Patrick Ln., Suite 105, Las Vegas, NV 89119</div> --}}
					</span>
				</div> 
				<span class="d-flex">
					<a onclick="load_detail(this)" 
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
					   onclick="open_list_tag(this,'{{ empty($item['conversation_id']) ? 'sp_inbox_comments' : 'sp_inbox' }}')">
						{!! file_get_contents(theme_public_asset('img/tag.svg')) !!}
					</a>
					
					<a data-toggle="tooltip" 
					   data-placement="top" 
					   title="Assign It" 
					   href="javascript:void(0);" 
					   class="icon-with-circle me-2 user-icon-ids" 
					   data-user-ids="{{ !empty($item['user_ids']) ? $item['user_ids'] : '' }}" 
					   data-id="{{ $item['id'] }}" 
					   onclick="open_list_user(this,'{{ empty($item['conversation_id']) ? 'sp_inbox_comments' : 'sp_inbox' }}')">
						{!! file_get_contents(theme_public_asset('img/flag.svg')) !!}
					</a>
					
					<a data-toggle="tooltip" 
					   data-placement="top" 
					   title="Add to Favorite" 
					   href="javascript:void(0);" 
					   class="icon-with-circle me-2 {{ $item['is_favourite'] == 1 ? 'is_fav' : '' }}" 
					   data-id="{{ $item['id'] }}" 
					   data-fav="{{ $item['is_favourite'] }}" 
					   onclick="favourite_toggle(this,'{{ empty($item['conversation_id']) ? 'sp_inbox_comments' : 'sp_inbox' }}')">
						{!! file_get_contents(theme_public_asset('img/heart.svg')) !!}
					</a>
					
					<a data-toggle="tooltip" 
					   data-placement="top" 
					   title="Delete" 
					   href="javascript:void(0)" 
					   class="delete-btn me-2" 
					   onclick="delete_message('{{ $item['id'] }}','{{ empty($item['conversation_id']) ? 'sp_inbox_comments' : 'sp_inbox' }}')">
						{!! file_get_contents(theme_public_asset('img/delete.svg')) !!}
					</a>
					
					@if(!empty($item['post_url']))
						<div class="d-inline-block dropdown">
							<a href="javascript:void(0)" 
							   class="dropdown-toggle link text-muted d-flex w-30 h-30 icon-with-circle fs-18 justify-content-center" 
							   aria-expanded="false">
								<i class="fa fa-ellipsis-v"></i>
							</a>
						
							<div style="min-width: 240px; top: auto !important; left: auto; right: 0;" 
								 class="py-0 dropdown-menu post-account">	
								 
								 <a href="{{ $item['post_url'] }}" 
								   target="_blank" 
								   class="dropdown-item link d-flex py-3 px-3 align-items-center">
									<div class="icon-container mx-2 d-flex">
									{{get_social_media_image($item['media_type'])}}
									</div>
									<span class="text-dark">Show in {{$item['media_type']}}</span>
								</a>
								
							</div>
						</div>
					@endif
				</span>


                <div class="d-flex align-items-start">
                    <img src="{{ $item['from_image'] }}" class="rounded-circle me-3" width="35" height="35" alt="">
                    <div class="flex-grow-1">
                        <div class="">
                            <h6 class="mb-1">{{ $item['from_name'] }}</h6>
                            <small class="text-muted">{{ date('M d, Y, h:i a', strtotime($item['created_time'])) }}</small>
                        </div>
                        <p class="mb-1">{{ $item['message'] }}</p>
                        <small class="text-muted">
                            <i class="fab fa-{{ strtolower($item['media_type']) }} me-1"></i>
                            {{ $item['inbox_type'] }}
                        </small>
                    </div>
                </div>
                
                @if(!empty($item['tag_names']))
                    <div class="mt-2">
                        @foreach(explode(',', $item['tag_names']) as $tag)
                            <span class="badge bg-secondary me-1">
                                <i class="fa-tag fal me-1"></i>{{ $tag }}
                            </span>
                        @endforeach
                    </div>
                @endif
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

