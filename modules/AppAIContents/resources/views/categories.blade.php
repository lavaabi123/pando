@if($categories)
    <div class="row">
    @foreach($categories as $value)
        <div class="col-12 mb-3">
            <a class="card shadow-none border-2 actionItem" href="{{ module_url("templates") }}" data-content="ai-template-data" data-id="{{ $value->id_secure }}" >
                <div class="card-body d-flex justify-content-between align-items-center px-3 py-2 gap-16">
                    <div class="fs-14 fw-5 text-truncate-2">
                        <!--<div class="size-30 d-flex align-items-center justify-content-between fs-20">
                            <i class="{{ $value->icon }} text-{{ $value->color }}-500"></i>
                        </div>-->
                        <div class="text-truncate-2">
                            {{ $value->name }}
                        </div>
                    </div>
					<i class="fa fa-arrow-right"></i>
                </div>
            </a>
        </div>
    @endforeach
    </div>
@endif