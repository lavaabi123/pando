@if( $captions->Total() > 0 )

	@foreach( $captions as $key => $value )
	<div class="col-12 mb-2">
	    <label class="card d-flex rounded-5 flex-row border-gray-300 shadow-none" for="group_{{ $value->id_secure }}">
	        <div class="card-body d-flex p-2">
	            <div class="d-flex gap-8 ps-1">
                    <input class="form-check-input checkbox-item" type="checkbox" name="id[]" value="{{ $value->id_secure }}" id="group_{{ $value->id_secure }}">
	    		</div>
	        	<div class="d-flex align-items-center w-100">
	        		
	        		<div class="d-flex justify-content-center align-items-center size-35  b-r-100  mx-2">
	        			@if($value->image)
								<img src="{{ Media::url($value->image) }}" class="brand-avatar" alt="{{ $value->name }}">
							@else
								<div class="brand-avatar-placeholder">{{ strtoupper(substr($value->name, 0, 1)) }}</div>
							@endif
	        		</div>
	        		<div class="fs-14 fw-7">{{ __($value->name) }}</div>
	        		
	        	</div>
	        </div>
	        <div class="card-footer px-2 me-1 d-flex justify-content-center border-none gap-8">
	            <a href="{{ module_url("update") }}" class="icon-with-circle actionItem" data-id="{{ $value->id_secure }}" data-popup="groupModal" data-call-success="">
	                {!! file_get_contents(public_path('img/post.svg')) !!}
	                <!--<span>{{ __("Edit") }}</span>-->
	            </a>
	            <!--<div class="text-gray-400 h-20 w-1 bg-gray-200 "></div>-->
				<a href="{{ module_url("destroy") }}" class="icon-with-circle actionItem" data-id="{{ $value->id_secure }}" data-confirm="Are you sure?" data-call-success="Main.ajaxScroll(true);" >
	                {!! file_get_contents(public_path('img/delete.svg')) !!}
	                <!--<span>{{ __("Delete") }}</span>-->
	            </a>
		            
	        </div>
	    </label>
	</div>
	@endforeach
@else
	<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
	    <span class="fs-70 mb-3 text-primary">
	        <i class="fa-light fa-users-medical"></i>
	    </span>
	    <div class="fw-semibold fs-5 mb-2 text-gray-900">
	        {{ __('No Brands Yet') }}
	    </div>
	    <div class="text-body-secondary mb-4 text-center max-w-500">
	        {{ __('Create brands to easily organize and manage your channels for better workflow efficiency.') }}
	    </div>
	    <a class="btn btn-dark actionItem" href="{{ module_url("update") }}" data-popup="groupModal" data-call-success="Main.ajaxScroll(true);">
	        <i class="fa-light fa-plus me-1"></i> {{ __('Add new brand') }}
	    </a>
	</div>
@endif