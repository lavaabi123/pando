@if( $captions->Total() > 0 )

	@foreach( $captions as $key => $value )
	<div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4 col-xxl-3 mb-4">
	    <label class="card border-gray-300 shadow-none" for="group_{{ $value->id_secure }}">
	        <div class="card-body px-3">
	            <div class="d-flex justify-content-end gap-8">
                    <input class="form-check-input checkbox-item" type="checkbox" name="id[]" value="{{ $value->id_secure }}" id="group_{{ $value->id_secure }}">
	    		</div>
	        	<div class="d-flex flex-column align-items-center w-100">
	        		
	        		<div class="d-flex justify-content-center align-items-center size-45  b-r-100  mb-2">
	        			<img class="h-auto"
                         src="{{ !empty($value->image) ? Media::url($value->image) : module_folder_url("/assets/img/mark.png") }}"
                         style="width:100%"
                    >
	        		</div>
	        		<div class="fs-13 fw-5">{{ __($value->name) }}</div>
	        		
	        	</div>
	        </div>
	        <div class="card-footer fs-12 d-flex justify-content-center gap-8">
	            <a href="{{ module_url("update") }}" class="d-flex flex-fill gap-8 align-items-center justify-content-center text-gray-900 text-hover-primary fw-5 actionItem" data-id="{{ $value->id_secure }}" data-popup="groupModal" data-call-success="">
	                <i class="fa-light fa-pen-to-square"></i> 
	                <span>{{ __("Edit") }}</span>
	            </a>
	            <div class="text-gray-400 h-20 w-1 bg-gray-200 "></div>
				<a href="{{ module_url("destroy") }}" class="d-flex flex-fill gap-8 align-items-center justify-content-center text-gray-900 text-hover-primary fw-5 actionItem" data-id="{{ $value->id_secure }}" data-confirm="Are you sure?" data-call-success="Main.ajaxScroll(true);" >
	                <i class="fa-light fa-trash-can"></i>
	                <span>{{ __("Delete") }}</span>
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