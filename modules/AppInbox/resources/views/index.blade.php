@extends('layouts.app')

@section('title', $title ?? 'Inbox')

@section('content')
<div class="container mw-100">
    <div class="row pb-4 pt-3">
        <!-- Filter Sidebar -->
        <div class="col-3 mw-300">
            <div class="my-1">
                <h3 class="fw-7 mb-4 fs-22">
                    <i class="fa-light fa-filter me-2"></i>Filter
                </h3>
            </div>
            
            <div class="b-r-30 border bg-white p-3">
                <form id="filter_form">
                    <input type="hidden" id="pagenos" name="page" value="1" />

                    <div class="px-3 pb-3 border-bottom filtered-list" style="display:none;">
                        <ul class="list-unstyled one-column scrollable mh-100 load-filter-text mb-0">
                        </ul>
                    </div>

                    <div class="accordion accordion-flush" id="accordionFlushExample">
                        <!-- Brand Filter -->
                        <div class="accordion-item b-r-25 border overflow-hidden mb-3">
                            <h2 class="accordion-header" id="flush-headingBrand">
                                <div class="accordion-button fw-7 collapsed" type="button" data-bs-toggle="collapse" 
                                     data-bs-target="#flush-collapseBrand" aria-expanded="false">
                                    Brand
                                </div>
                            </h2>
                            <div id="flush-collapseBrand" class="accordion-collapse collapse" aria-labelledby="flush-headingBrand">
                                <div class="accordion-body">
                                    @if(!empty($brands_list))
                                        <ul class="list-unstyled symbol py-1">
                                            @foreach($brands_list as $brand)
                                                <li class="py-1 d-flex">
                                                    <input type="checkbox" name="brand[]" value="{{ $brand->id }}" class="me-2">
                                                    <label class="form-check-label">{{ $brand->name }}</label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- User Filter -->
                        <div class="accordion-item b-r-25 border overflow-hidden mb-3">
                            <h2 class="accordion-header" id="flush-headingUser">
                                <div class="accordion-button fw-7 collapsed" type="button" data-bs-toggle="collapse" 
                                     data-bs-target="#flush-collapseUser" aria-expanded="false">
                                    User
                                </div>
                            </h2>
                            <div id="flush-collapseUser" class="accordion-collapse collapse" aria-labelledby="flush-headingUser">
                                <div class="accordion-body">
                                    @if(!empty($users_list))
                                        <ul class="list-unstyled symbol py-1">
                                            @foreach($users_list as $user)
                                                <li class="py-1 d-flex">
                                                    <input type="checkbox" name="users[]" value="{{ $user->id }}" class="me-2">
                                                    <label class="form-check-label">{{ $user->fullname }}</label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
						
						<div class="accordion-item b-r-25 border overflow-hidden mb-3">
					<h2 class="accordion-header" id="flush-heading4">
					  <div class="accordion-button fw-7 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse4" aria-expanded="false" aria-controls="flush-collapse4">
						Profiles
					  </div>
					</h2>
					<div id="flush-collapse4" class="accordion-collapse collapse" aria-labelledby="flush-heading4" data-bs-parent="#accordionFlushExample">
					  <div class="accordion-body">
					  @if(!empty($accounts))
					  <ul class="list-unstyled symbol py-1">
						@foreach($accounts as $value)
						  <li class="py-0 d-flex align-items-center">
						  <input type="checkbox" name="accounts[]" value="{{$value->id}}" class="me-2">
						  <div class="symbol symbol-35px px-3 py-2" style="padding-left: 0 !important;">
									<img src="{{ Media::url($value->avatar) }}" style="width:25px; height:25px" class="rounded-circle align-self-center" alt="">
									{!! get_social_media_icon($value->social_network) !!}							
								</div><span data-toggle="tooltip" data-placement="top" title="{{$value->name}}" class="text-truncate">{{$value->name}}</span><!----><!---->
						  
						  </li>							  
						@endforeach
					  @endif
					  </div>
					</div>
				  </div>
                        <!-- Type Filter -->
                        <div class="accordion-item b-r-25 border overflow-hidden mb-3">
                            <h2 class="accordion-header" id="flush-headingType">
                                <div class="accordion-button fw-7 collapsed" type="button" data-bs-toggle="collapse" 
                                     data-bs-target="#flush-collapseType" aria-expanded="false">
                                    Types
                                </div>
                            </h2>
                            <div id="flush-collapseType" class="accordion-collapse collapse" aria-labelledby="flush-headingType">
                                  <div class="accordion-body">
									  <ul class="list-unstyled symbol pb-2">
									  
									  <!-- For Facebook -->
									  
									  <li class="text-truncate d-flex me-2 py-1">
									  <input type="checkbox" name="eventType[]" class="custom-control-input me-2" value="facebook_Comment">
									  <label class="custom-control-label font-normal d-flex w-100" for="if_FBComments">
									  <div class="post-account me-2">{{ get_social_media_image('facebook') }}</div><span class="text-truncate text-dark pr-1" style="top: auto;">Comments</span></label></li>
									  <!--<li class="text-truncate d-flex me-2 py-1">
									  <input type="checkbox" name="eventType[]" class="custom-control-input me-2" value="facebook_AdComment">
									  <label class="custom-control-label font-normal d-flex w-100" for="if_FBAdComments">
									  <div class="post-account me-2">{{ get_social_media_image('facebook') }}</div><span class="text-truncate text-dark pr-1" style="top: auto;">Ad Comments</span></label></li>-->
									  <li class="text-truncate d-flex me-2 py-1">
									  <input type="checkbox" name="eventType[]" class="custom-control-input me-2" value="facebook_Messenger">
									  <label class="custom-control-label font-normal d-flex w-100" for="if_FBM">
									  <div class="post-account me-2">{{ get_social_media_image('facebook') }}</div><span class="text-truncate text-dark pr-1" style="top: auto;">Messenger</span></label></li>
									  <li class="text-truncate d-flex me-2 py-1">
									  <input type="checkbox" name="eventType[]" class="custom-control-input me-2" value="facebook_Mentions">
									  <label class="custom-control-label font-normal d-flex w-100" for="if_FBME">
									  <div class="post-account me-2">{{ get_social_media_image('facebook') }}</div><span class="text-truncate text-dark pr-1" style="top: auto;">Mentions</span></label></li>
									  <li class="text-truncate d-flex me-2 py-1">
									  <input type="checkbox" name="eventType[]" class="custom-control-input me-2" value="facebook_Review">
									  <label class="custom-control-label font-normal d-flex w-100" for="if_FBR">
									  <div class="post-account me-2">{{ get_social_media_image('facebook') }}</div><span class="text-truncate text-dark pr-1" style="top: auto;">Reviews</span></label></li>
									  
									  <!-- For Instagram -->
									  
									  <li class="text-truncate d-flex me-2 py-1">
									  <input type="checkbox" name="eventType[]" class="custom-control-input me-2" value="instagram_Comment">
									  <label class="custom-control-label font-normal d-flex w-100" for="if_IComments">
									  <div class="post-account me-2">{{ get_social_media_image('instagram') }}</div><span class="text-truncate text-dark pr-1" style="top: auto;">Comments</span></label></li>
									  <li class="text-truncate d-flex me-2 py-1">
									  <input type="checkbox" name="eventType[]" class="custom-control-input me-2" value="instagram_Messenger">
									  <label class="custom-control-label font-normal d-flex w-100" for="if_IM">
									  <div class="post-account me-2">{{ get_social_media_image('instagram') }}</div><span class="text-truncate text-dark pr-1" style="top: auto;">Messenger</span></label></li>
									  <li class="text-truncate d-flex me-2 py-1">
									  <input type="checkbox" name="eventType[]" class="custom-control-input me-2" value="instagram_Tags">
									  <label class="custom-control-label font-normal d-flex w-100" for="if_IT">
									  <div class="post-account me-2">{{ get_social_media_image('instagram') }}</div><span class="text-truncate text-dark pr-1" style="top: auto;">Tags</span></label></li>
									  
									  <!-- For X -->
									 <!-- <li class="text-truncate d-flex me-2 py-1">
									  <input type="checkbox" name="eventType[]" class="custom-control-input me-2" value="twitter_Messenger">
									  <label class="custom-control-label font-normal d-flex w-100" for="if_IM">
									  <div class="post-account me-2">{{ get_social_media_image('x') }}</div><span class="text-truncate text-dark pr-1" style="top: auto;">Direct Messages</span></label></li>-->
									  
									  <!-- For Linkedin -->
									  
									  <li class="text-truncate d-flex me-2 py-1">
									  <input type="checkbox" name="eventType[]" class="custom-control-input me-2" value="linkedin_Comment">
									  <label class="custom-control-label font-normal d-flex w-100" for="if_IComments">
									  <div class="post-account me-2">{{ get_social_media_image('linkedin') }}</div><span class="text-truncate text-dark pr-1" style="top: auto;">Comments</span></label></li>
									  
									  
									  </ul>
									  </div>
									
                            </div>
                        </div>

                        <!-- Tags Filter -->
                        <div class="accordion-item b-r-25 border overflow-hidden mb-3">
                            <h2 class="accordion-header" id="flush-headingTags">
                                <div class="accordion-button fw-7 collapsed" type="button" data-bs-toggle="collapse" 
                                     data-bs-target="#flush-collapseTags" aria-expanded="false">
                                    Tags
                                </div>
                            </h2>
                            <div id="flush-collapseTags" class="accordion-collapse collapse" aria-labelledby="flush-headingTags">
                                <div class="accordion-body"><div href="javascript: void(0)" onclick="add_tag_modal()" role="button" class="text-gray-400 text-hover-primary fs-14 fw-bold mb-2"><span>Add New Tag</span></div>
								<div href="javascript: void(0)" class="text-gray-400 text-hover-primary fs-14 fw-bold mb-2" role="button" onclick="clear_tag_selected()"><span>Clear Selected</span></div>
									<ul class="list-unstyled symbol py-1">
										<li class="py-1 d-flex">
										  <input type="checkbox" name="tags[]" value="0" class="me-2">
										  <label class="form-check-label" for="Inbox">Untag items</label>
										</li>
                                    @if(!empty($tags_list))
                                        <ul class="list-unstyled symbol py-1 load-tag-list">
                                            @foreach($tags_list as $tag)
                                                <li class="py-1 d-flex">
                                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="me-2">
                                                    <label class="form-check-label">{{ $tag->tag_name }}</label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
									</ul>
                                </div>
                            </div>
                        </div>

                    </div>
				<div class="form-check form-switch fw-7 fs-12 m-b-10">
				  <input class="form-check-input" name="itemFilter" value="Inbox" type="radio" id="showAllChecked">
				  <label class="form-check-label" for="showAllChecked">Show all</label>
				</div>
				<div class="form-check form-switch fw-7 fs-12 m-b-10">
				  <input class="form-check-input" name="itemFilter" value="Pending" type="radio" id="showPending" checked>
				  <label class="form-check-label" for="showPending">Show pending messages</label>
				</div>
				<div class="form-check form-switch fw-7 fs-12 m-b-10">
				  <input class="form-check-input" name="itemFilter" value="Completed" type="radio" id="showCompleted">
				  <label class="form-check-label" for="showCompleted">Show completed messages</label>
				</div> 

                    <div class="d-flex align-item-center justify-content-center flex-wrap border-0 mt-4">					
                        <button type="button" class="btn btn-primary me-2 mb-1 w-110" onclick="applyFilter()">
                            Filter
                        </button>
                        <button type="button" class="btn btn-secondary mb-1 w-110" onclick="resetFilter()">
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-9">
			<h3 class="fw-7 mb-4 fs-22"><span class="d-inline-block w-22">{!! file_get_contents(public_path('img/inbox.svg')) !!}</span> Inbox</h3>
			<div class="border b-r-30 p-3 fs-14 overflow-hidden">

				<div class="row h-100">
					<!-- Inbox List -->
					<div class="col-md-6 h-100">
									<div id="fav-feed-header" class="mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div class="datarangeinbox"></div>
        
        <div class="dropdown">
            <button class="icon-with-circle fs-20 p-0" 
                    type="button" 
                    id="dropdownMenuButton" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false"
                    style="width: 30px; height: 30px;">
                <i class="fa fa-ellipsis-v"></i>
            </button>
        
            <ul class="dropdown-menu dropdown-menu-end" 
                aria-labelledby="dropdownMenuButton" 
                style="min-width: 240px;">
                
                <li>
                    <a class="dropdown-item align-items-center py-3 px-3 complete_selected_items" 
                       href="javascript:void(0)" 
                       onclick="complete_selected_items()">
                        <div class="icon-container me-2 d-inline-block">
                            <i class="fal fa-check-circle text-muted"></i>
                        </div>
                        <span class="text-dark">{{ __('Complete selected items') }}</span>
                    </a>
                </li>
                
                <li>
                    <a class="dropdown-item align-items-center py-3 px-3 complete_all_items" 
                       href="javascript:void(0)" 
                       onclick="complete_all_items()">
                        <div class="icon-container me-2 d-inline-block">
                            <i class="fal fa-check-circle text-muted"></i>
                        </div>
                        <span class="text-dark">{{ __('Complete all items') }}</span>
                    </a>
                </li>
                
                <li>
                    <a class="dropdown-item align-items-center py-3 px-3 incomplete_selected_items" 
                       href="javascript:void(0)" 
                       onclick="incomplete_selected_items()"
                       style="display:none">
                        <div class="icon-container me-2 d-inline-block">
                            <i class="fal fa-check-circle text-muted"></i>
                        </div>
                        <span class="text-dark">{{ __('Incomplete selected items') }}</span>
                    </a>
                </li>
                
                <li>
                    <a class="dropdown-item align-items-center py-3 px-3 incomplete_all_items" 
                       href="javascript:void(0)" 
                       onclick="incomplete_all_items()"
                       style="display:none">
                        <div class="icon-container me-2 d-inline-block">
                            <i class="fal fa-check-circle text-muted"></i>
                        </div>
                        <span class="text-dark">{{ __('Incomplete all items') }}</span>
                    </a>
                </li>
                
                <li>
                    <a class="dropdown-item align-items-center py-3 px-3" 
                       href="javascript:void(0)" 
                       onclick="delete_selected_items()"
                       data-confirm="{{ __('Are you sure to delete selected items?') }}">
                        <div class="icon-container me-2 d-inline-block">
                            <i class="fal fa-trash text-muted"></i>
                        </div>
                        <span class="text-dark">{{ __('Delete selected items') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
						<div class="bg-white border b-r-30 p-3 inbox-msg overflow-auto scroll-bar">
							
							<div id="inbox-list-container">
								<!-- Inbox list will be loaded here via AJAX -->
								<div class="text-center py-5">
									<i class="fa-light fa-spinner fa-spin fa-3x"></i>
									<p class="mt-3">Loading inbox...</p>
								</div>
							</div>
						</div>
					</div>
					<!-- Detail View -->
					<div class="col-md-6 h-100">
						<div class="bg-grey border b-r-30 px-3 pb-3 h-100">
							<div id="inbox-detail-container" class="h-100">
								<!-- Detail view will be loaded here via AJAX -->
								<div class="text-center py-5 text-muted">
									<i class="fa-light fa-inbox fa-3x"></i>
									<p class="mt-3">Select a conversation to view details</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
        </div>
		
		
		
				
    </div>
</div>

<div id="addtagmodal" class="modal fade">
	<div class="modal-dialog modal-dialog-centered modal-md">
		<div class="modal-content">
			<div class="modal-header bg-solid-warning">
				<h5 class="modal-title">Add New Tag</h5>
				<a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-sm btn-light-danger w-35 h-35 b-r-40 d-flex justify-content-center align-items-center"><i class="fad fa-times pe-0"></i></a>
			</div>
			<div class="modal-body shadow-none">
				<div class="">
				<p>Use tags to create category or assign inbox items to a user. Once you tag an item, you can quickly find it with Tags Filter.</p>
				<input type="text" placeholder="Enter a new tag (40 characters max)" class="fs-12 form-control" id="tag_name_add" value="" maxlength="40">
				</div>
			</div>
			<div class="modal-footer">
				<a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-secondary">Cancel</a>
				<a href="javascript:void(0);" class="btn btn-primary" onclick="add_tag()">Save</a>
			</div>
		</div>
	</div>
</div>


<div id="listtagmodal" class="modal fade">
	<div class="modal-dialog modal-dialog-centered modal-md">
		<div class="modal-content">
			<div class="modal-header bg-solid-warning">
				<h5 class="modal-title">Tags</h5>
				<a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-sm btn-light-danger w-35 h-35 b-r-40 d-flex justify-content-center align-items-center"><i class="fad fa-times pe-0"></i></a>
			</div>
			<div class="modal-body shadow-none">
				<div class="selected_inbox" data-id="">
				 <ul class="list-unstyled symbol py-1">
					  <?php if(!empty($tags_list)){ ?>
						  <?php foreach($tags_list as $tag){ ?>
						  <li class="py-1 d-flex align-items-center">
							  <input type="checkbox" name="select_tags[]" value="<?php echo $tag->id; ?>" class="me-2">
							  <label class="form-check-label" for="Inbox"><?php echo $tag->tag_name; ?></label>
						  </li>							  
						<?php  }
					  } ?>
					  </ul>
				</div>
			</div>
			<div class="modal-footer">
				<a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-secondary">Cancel</a>
				<a href="javascript:void(0);" class="btn btn-primary" onclick="assign_tag()">Ok</a>
			</div>
		</div>
	</div>
</div>


<div id="listusermodal" class="modal fade">
	<div class="modal-dialog modal-dialog-centered modal-md">
		<div class="modal-content">
			<div class="modal-header bg-solid-warning">
				<h5 class="modal-title">Assign to Users</h5>
				<a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-sm btn-light-danger w-35 h-35 b-r-40 d-flex justify-content-center align-items-center"><i class="fad fa-times pe-0"></i></a>
			</div>
			<div class="modal-body shadow-none">
				<div class="selected_inbox" data-id="">
				 <ul class="list-unstyled symbol py-1 d-flex flex-wrap">
					  <?php if(!empty($users_list)){ ?>
						  <?php foreach($users_list as $user){ ?>
						  <li class="py-1 d-flex align-items-center col-6">
							  <input type="checkbox" name="select_users[]" value="<?php echo $user->id; ?>" class="me-2">
							  <label class="form-check-label" for="Inbox"><?php echo $user->fullname; ?></label>
						  </li>							  
						<?php  }
					  } ?>
					  </ul>
				</div>
			</div>
			<div class="modal-footer">
				<a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-secondary">Cancel</a>
				<a href="javascript:void(0);" class="btn btn-primary" onclick="assign_user()">Ok</a>
			</div>
		</div>
	</div>
</div>

<script>
window.routes = {
inboxAjax: @json(route('inbox.ajax_list')),
addTag: @json(route('inbox.add_tag')),
inboxDetail: @json(route('inbox.ajax_list_detail')),
};
function clear_form(){
	$('#filter_form')[0].reset();
}
</script>
@endsection
