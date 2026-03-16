@foreach($schedules as $value)
    @php
        $data = is_string($value->data) ? json_decode($value->data, true) : (is_array($value->data) ? $value->data : []);
        $caption = $data['caption'] ?? '';
        $link = $data['link'] ?? '';
        $medias = $data['medias'] ?? [];
        $img = is_array($medias) && isset($medias[0]) && $medias[0] ? $medias[0] : 'https://placehold.co/80x80';
        
        $network = $value->social_network ?? 'N/A';
        $type = $value->type ?? 'N/A';
        $status = ($value->result == 1) ? 'Ready' : 'Approval';
        
        // Get comments
        $comments = $value->comments ?? collect();
        $commentCount = $comments->count();
    @endphp

    <div class="{{ (!empty($from) ? 'col-md-12 col-lg-12' : 'col-md-6 col-lg-4') }}  mb-4">
        <div class="card h-100 approval-card">
            <div class="card-header px-3">
                <input class="form-check-input checkbox-item me-2" type="checkbox" name="id[]" data-id="{{ $value->id }}" value="{{ $value->id }}">
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
                    <a href="{{ url_app("publishing/preview") }}" class="icon-with-circle actionItem" data-popup="pubishingPreviewModal" data-id="{{ $value->grouping_data }}" data-call-success="AppPubishing.closePopoverCalendar();" data-bs-title="Preview" data-bs-toggle="tooltip" data-bs-placement="top">{!! file_get_contents(public_path('img/search.svg')) !!}</a>
                    <a href="{{ module_url("update") }}" class="icon-with-circle actionItem" data-id="{{ $value->id_secure }}" data-popup="groupModal" data-call-success="" data-bs-title="Duplicate Post" data-bs-toggle="tooltip" data-bs-placement="top">
                        {!! file_get_contents(public_path('img/duplicate.svg')) !!}
                    </a>
                    <a href="{{ url_app('publishing/composer?id=' . $value->id_secure) }}" class="icon-with-circle" data-id="{{ $value->id_secure }}" data-append-content="composer-scheduling" data-call-success="AppPubishing.openCompose(); AppPubishing.closePopoverCalendar();" data-bs-title="Edit" data-bs-toggle="tooltip" data-bs-placement="top">
                        {!! file_get_contents(public_path('img/post.svg')) !!}
                    </a>
                    <a href="{{ url_app("publishing/comments") }}" class="icon-with-circle actionItem open-comment-modal" data-id="{{ $value->grouping_data }}" data-popup="commentModal" data-bs-title="Comment" data-bs-toggle="tooltip" data-bs-placement="top">
                        {!! file_get_contents(public_path('img/msg.svg')) !!}
                    </a>
                    <a href="{{ url_app("publishing/destroy") }}" class="icon-with-circle actionItem" data-id="{{ $value->grouping_data }}" data-call-success="location.reload();" data-bs-title="Delete Post" data-bs-toggle="tooltip" data-bs-placement="top" data-confirm="{{ __('Are you sure to delete this post?') }}">
                        {!! file_get_contents(public_path('img/delete.svg')) !!}
                    </a>
                </div>
            </div>
            <div class="card-body p-3 pt-2">
                <div class="card-content">
                    <p class="lastEdit text-primary fs-12 mb-0">Last Edited: {{ $value->updated_at ? \Carbon\Carbon::parse($value->updated_at)->format('M d, Y h:i A') : 'N/A' }}</p>
                    <div class="d-flex mt-3">
                        <div class="flex-grow-1">
                            <p class="card-text mb-3 fs-13">{{ $caption ?: 'No caption.' }}</p>
                            <div class="d-flex gap-2 flex-wrap">
                            </div>
                        </div>
                        @php
                            $_isVideo = fn($f) => preg_match('/\.(mp4|mov|webm|avi|mkv)$/i', $f ?? '');
                            $_total   = count($medias);
                        @endphp

                        @if($type === 'media' && $_total > 0)
                            {{-- ── Media thumbnail: single or carousel ── --}}
                            <div class="apm-wrap ms-3" style="width:80px;height:80px;flex-shrink:0;">
                                @if($_total === 1)
                                    @if($_isVideo($medias[0]))
                                        <div class="apm-media-click" data-type="video" data-src="{{ Media::url($medias[0]) }}">
                                            <video class="apm-media" muted playsinline preload="metadata">
                                                <source src="{{ Media::url($medias[0]) }}" type="video/mp4">
                                            </video>
                                            <span class="apm-badge apm-badge--play-center"><i class="fa fa-play"></i></span>
                                        </div>
                                    @else
                                        <div class="apm-media-click" data-type="image" data-src="{{ Media::url($medias[0]) }}">
                                            <img class="apm-media" src="{{ Media::url($medias[0]) }}" alt="">
                                        </div>
                                    @endif
                                @else
                                    {{-- Multiple media — owl carousel --}}
                                    <div class="owl-carousel apm-owl" data-total="{{ $_total }}">
                                        @foreach($medias as $_file)
                                            <div class="apm-slide">
                                                @if($_isVideo($_file))
                                                    <div class="apm-media-click" data-type="video" data-src="{{ Media::url($_file) }}">
                                                        <video class="apm-media" muted playsinline preload="metadata">
                                                            <source src="{{ Media::url($_file) }}" type="video/mp4">
                                                        </video>
                                                        <span class="apm-badge apm-badge--play-center"><i class="fa fa-play"></i></span>
                                                    </div>
                                                @else
                                                    <div class="apm-media-click" data-type="image" data-src="{{ Media::url($_file) }}">
                                                        <img class="apm-media" src="{{ Media::url($_file) }}" alt="">
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    {{-- count badge removed --}}
                                @endif
                            </div>
                        @elseif($type === 'link')
                            <div class="apm-wrap ms-3 d-flex align-items-center justify-content-center fs-30 text-primary" style="width:80px;height:80px;flex-shrink:0;">
                                <a href="{{ $link }}" target="_blank"><i class="fa-light fa-link"></i></a>
                            </div>
                        @else
                            <div class="apm-wrap ms-3 d-flex align-items-center justify-content-center fs-30 text-primary" style="width:80px;height:80px;flex-shrink:0;">
                                <i class="fa-light fa-align-center"></i>
                            </div>
                        @endif
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
                        <div class="text-primary">{!! file_get_contents(public_path('img/account.svg')) !!}</div>
                        <a href="#" class="btn btn-secondary btn-sm">Created by</a>
                        <p class="assign_name mb-0 fw-6 text-gray-500 fs-12">{{ $value->creator_name ?? 'N/A' }}</p>
                    </div>
                    <div class="d-flex align-items-center gap-8">
                        <div class="text-primary">{!! file_get_contents(public_path('img/msg.svg')) !!}</div>
                        <a href="{{ url_app('publishing/comments') }}" class="btn btn-secondary btn-sm actionItem" data-id="{{ $value->grouping_data }}" data-popup="commentModal">
                            Comment @if($commentCount > 0)<span class="badge bg-primary ms-1">{{ $commentCount }}</span>@endif
                        </a>
                        @if($commentCount > 0)
                            @php $lastComment = $comments->last(); @endphp
                            <p class="s_comment mb-0 fw-6 text-gray-500 fs-12">
                                {{ $lastComment->user_name ?? 'User' }} - {{ Str::limit($lastComment->comment, 30) }} ({{ $lastComment->created_at ? \Carbon\Carbon::parse($lastComment->created_at)->format('M d, Y h:i A') : '' }})
                            </p>
                        @else
                            <p class="s_comment mb-0 fw-6 text-gray-500 fs-12">No comments yet</p>
                        @endif
                    </div>
                </div>
               
                <div class="w-100 text-end mb-1">
                    <a href="{{ url_app("publishing/move_to_queue") }}" 
                       class="btn btn-primary btn-sm w-110 px-4" 
                       data-call-success="Main.ajaxScroll(true)"
                       data-id="{{ $value->grouping_data }}" 
                       data-confirm="{{ __('Are you sure to move this item to queue?') }}" 					   
                       title="{{ __('Move to Queue') }}" 
                       data-toggle="tooltip" 
                       data-placement="top">
                        <span>{{ __('Approve') }}</span>
                    </a>
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
        {{ __('No approvals yet') }}
    </div>
    <div class="text-body-secondary mb-4">
        {{ __('Start by creating a new approval to save your ideas and prepare content before publishing.') }}
    </div>
</div>
@endif

<style>
.comment-item {
    transition: all 0.3s ease;
}

.comment-item:hover {
    background-color: #f8f9fa;
}

.comments-container {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.comments-container::-webkit-scrollbar {
    width: 6px;
}

.comments-container::-webkit-scrollbar-track {
    background: #f7fafc;
}

.comments-container::-webkit-scrollbar-thumb {
    background-color: #cbd5e0;
    border-radius: 3px;
}

.icon-with-circle {
    position: relative;
}

/* ── Approval Post Media (apm) ─────────────────────────── */
.apm-wrap {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
}
.apm-media {
    width: 80px;
    height: 80px;
    object-fit: cover;
    display: block;
}
/* Carousel */
.apm-wrap .owl-carousel,
.apm-wrap .owl-stage-outer,
.apm-wrap .owl-stage,
.apm-wrap .owl-item,
.apm-wrap .apm-slide {
    height: 80px !important;
}
.apm-wrap .owl-prev,
.apm-wrap .owl-next {
    position: absolute !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    width: 18px !important;
    height: 18px !important;
    border-radius: 50% !important;
    background: rgba(0,0,0,0.55) !important;
    color: #fff !important;
    font-size: 8px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 0 !important;
    z-index: 10 !important;
    line-height: 1 !important;
}
.apm-wrap .owl-prev { left: 2px !important; }
.apm-wrap .owl-next { right: 2px !important; }
.apm-wrap .owl-prev span,
.apm-wrap .owl-next span { font-size: 14px; line-height: 1; }
.apm-wrap .owl-dots {
    position: absolute !important;
    bottom: 3px !important;
    width: 100% !important;
    text-align: center !important;
    margin: 0 !important;
}
.apm-wrap .owl-dot span {
    width: 4px !important;
    height: 4px !important;
    background: rgba(255,255,255,0.6) !important;
    margin: 0 2px !important;
}
.apm-wrap .owl-dot.active span { background: #fff !important; }
/* Badges */
.apm-badge {
    position: absolute;
    z-index: 5;
    pointer-events: none;
    line-height: 1;
}
.apm-media-click {
    position: relative;
    width: 100%;
    height: 100%;
    cursor: pointer;
    display: block;
}
.apm-media-click:hover .apm-badge--play-center {
    transform: translate(-50%, -50%) scale(1.12);
}
.apm-badge--play-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.58);
    color: #fff;
    font-size: 11px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding-left: 2px;
    transition: transform 0.15s ease;
    pointer-events: none;
    z-index: 6;
}
/* Lightbox overlay */
#apm-lightbox {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.88);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}
#apm-lightbox.active { display: flex; }
#apm-lightbox-media {
    max-width: 90vw;
    max-height: 85vh;
    border-radius: 10px;
    object-fit: contain;
    box-shadow: 0 8px 40px rgba(0,0,0,0.5);
}
#apm-lightbox-close {
    position: absolute;
    top: 18px;
    right: 24px;
    color: #fff;
    font-size: 32px;
    cursor: pointer;
    line-height: 1;
    opacity: 0.85;
    background: none;
    border: none;
    padding: 0;
    z-index: 100000;
}
#apm-lightbox-close:hover { opacity: 1; }

</style>

<!-- Lightbox HTML (injected once) -->
<div id="apm-lightbox">
    <button id="apm-lightbox-close">&times;</button>
    <!-- swapped dynamically by JS -->
</div>

<script>
(function() {
    /* ── Owl Carousel init ────────────────────────── */
    function initApmCarousels() {
        if (typeof $.fn.owlCarousel !== 'function') return;
        $('.apm-owl:not(.owl-loaded)').each(function () {
            var count = parseInt($(this).data('total')) || 2;
            $(this).owlCarousel({
                items       : 1,
                loop        : count > 1,
                nav         : true,
                dots        : count > 1,
                navText     : ['<span>&#8249;</span>', '<span>&#8250;</span>'],
                mouseDrag   : count > 1,
                touchDrag   : count > 1,
                autoHeight  : false,
                margin      : 0,
                stagePadding: 0,
            });
        });
    }

    /* ── Lightbox ─────────────────────────────────── */
    var $lb      = $('#apm-lightbox');
    var $lbClose = $('#apm-lightbox-close');

    function openLightbox(type, src) {
        // Remove previous media
        $lb.find('#apm-lightbox-media').remove();

        var $media;
        if (type === 'video') {
            $media = $('<video>', {
                id       : 'apm-lightbox-media',
                controls : true,
                autoplay : true,
                playsinline: true,
            }).append($('<source>', { src: src, type: 'video/mp4' }));
        } else {
            $media = $('<img>', {
                id  : 'apm-lightbox-media',
                src : src,
            });
        }
        $lb.append($media).addClass('active');
        $('body').css('overflow', 'hidden');
    }

    function closeLightbox() {
        // Stop video if playing
        var vid = document.getElementById('apm-lightbox-media');
        if (vid && vid.tagName === 'VIDEO') { vid.pause(); }
        $lb.removeClass('active').find('#apm-lightbox-media').remove();
        $('body').css('overflow', '');
    }

    // Close on button / overlay click
    $lbClose.on('click', closeLightbox);
    $lb.on('click', function(e) {
        if ($(e.target).is('#apm-lightbox')) closeLightbox();
    });
    // Close on Escape
    $(document).on('keydown.apm', function(e) {
        if (e.key === 'Escape') closeLightbox();
    });

    // Delegate click on .apm-media-click (works for AJAX-loaded content too)
    $(document).on('click', '.apm-media-click', function(e) {
        e.stopPropagation();
        openLightbox($(this).data('type'), $(this).data('src'));
    });

    /* ── Init ─────────────────────────────────────── */
    $(document).ready(function() { setTimeout(initApmCarousels, 150); });
    $(document).on('ajaxSuccess', function() { setTimeout(initApmCarousels, 200); });
    window.initApmCarousels = initApmCarousels;
})();
</script>