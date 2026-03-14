<div class="h-100">
    @if(!empty($result) && $result->count() > 0)
    
        <div class="events-date mb-3 fw-7 mx-4">
            <span>
                <i class="fad fa-list text-primary text-uppercase"></i> 
                {{ date("M d", strtotime($date)) }} : Day Preview ({{ $result->count() > 1 ? $result->count() . ' Posts)' : $result->count() . ' Post)' }})
            </span>
            <a class="btn btn-secondary btn-sm b-r-30 open-schedule-calendar d-lg-none d-md-none d-sm-block d-xs-block d-block text-uppercase" href="javascript:void(0);">
                <i class="fad fa-chevron-left"></i> {{ __("Back") }}
            </a>
        </div>
        
        <div class="schedules-detail h-100 overflow-auto">

            @foreach($result as $key => $post)
                @php
                    $data = json_decode($post->data);
                @endphp
                
                <div class="card item overflow-hidden border-bottom-0">

                    
                    
                    <div class="card-header p-3 border-0">
                        
                        <div class="card-title fw-normal fs-12 d-flex">
                            @php
                                $avatars = explode(',', $post->avatars);
                                $urls = explode(',', $post->urls);
                                $socialNetworks = explode(',', $post->social_networks);
                            @endphp
                            
                            @if(!empty($post->avatars))
                                @foreach($avatars as $kj => $ava)
                                    @php
                                        $config = \Module::find(trim($socialNetworks[$kj]) . "_post");
                                        $menu = $config ? $config->get('menu') : null;
                                    @endphp
                                    
                                    <div class="d-flex flex-stack">
                                        <div class="size-30 me-2 position-relative">
                                            <img src="{{ Media::url($ava) }}" class="align-self-center rounded-circle border w-100" alt="">
                                            <a href="{{ $urls[$kj] ?? '#' }}" target="_blank" class="position-absolute b-0 r-0">
                                                {{ get_social_media_icon_large(trim($socialNetworks[$kj])) }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="card-toolbar d-flex gap-6">
                            <a href="{{ url_app('publishing/composer?id=' . $post->id_secure) }}" class="icon-with-circle">
                                {!! file_get_contents(public_path('img/post.svg')) !!}
                            </a>
                            <a href="{{ url_app('post?type=duplicate&post_id=' . $post->ids) }}" class="icon-with-circle" title="{{ __('Duplicate Post') }}" data-toggle="tooltip" data-placement="top">
                                {!! file_get_contents(public_path('img/duplicate.svg')) !!}
                            </a>
                            <a href="{{ module_url('delete') }}" class="icon-with-circle delete-btn actionItem" data-remove="item" data-id="{{ $post->grouping_data }}" data-confirm="{{ __('Are you sure to delete this items?') }}" data-call-success="location.reload();">
                                {!! file_get_contents(public_path('img/delete.svg')) !!}
                            </a>
                            
                            @if($post->status == 4)
                                <div class="ml-auto ms-2">
                                    <div class="sp-menu-dropdown dropdown dropdown-hide-arrow" data-dropdown-spacing="0">
                                        <a class="dropdown-toggle text-gray-500" style="font-size:20px;" href="javascript:void(0);" data-bs-toggle="dropdown">
                                            <i class="fal fa-ellipsis-v"></i>
                                        </a>
                                        <ul class="dropdown-menu py-2" data-dropdown-spacing="0">
                                            @php
                                                // The query uses GROUP_CONCAT(posts.result SEPARATOR ",") as results
                                                // Each result is a JSON object e.g. {"id":"...","url":"...","message":"..."}
                                                // We cannot simply explode by "," because JSON values contain commas.
                                                // Use regex to extract each {...} object separately.
                                                $socialNetworks = explode(',', $post->social_networks);
                                                $rawResults = $post->results ?? '';
                                                // Match all top-level JSON objects
                                                preg_match_all('/\{(?:[^{}]|(?:\{[^{}]*\}))*\}/', $rawResults, $matches);
                                                $parsedResults = array_map(fn($j) => json_decode($j, true) ?? [], $matches[0] ?? []);
                                            @endphp
                                            @foreach($socialNetworks as $kjs => $snt)
                                                @php
                                                    $resData = $parsedResults[$kjs] ?? $parsedResults[0] ?? [];
                                                    $surl    = $resData['url'] ?? null;
                                                @endphp
                                                <li>
                                                    <a class="dropdown-item py-2" target="_blank"
                                                       href="{{ $surl ?? '#' }}"
                                                       @if(!$surl) style="pointer-events:none;opacity:0.5;" @endif>
                                                        {{ get_social_media_icon_large(trim($snt)) }}
                                                        Show in {{ trim($snt) }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>

                    <div class="card-body p-3 pt-0">
                        
                        <div class="d-flex align-items-center gap-2">
                        
                            <div class="symbol symbol-40px me-1 flex-shrink-0">
                                <input type="checkbox" value="{{ $post->id }}" name="approval_post_ids[]"/>
                            </div>

                            <div class="d-flex align-items-center gap-3 w-100">
                            
                                <div class="post-media-thumb border-2 flex-shrink-0 me-2">

									@if($post->type == "media")
										@if(!empty($data->medias))
											@if(count($data->medias) > 1)
												<div class="owl-carousel owl-theme">
													@foreach($data->medias as $index => $media)
														@if(is_image($media))
															<div class="item al-media-click cursor-pointer" data-type="image" data-src="{{ Media::url($media) }}" style="background-image: url('{{ Media::url($media) }}');"></div>
														@else
															<div class="item al-media-click" data-type="video" data-src="{{ Media::url($media) }}">
																<video class="videotag" width="100%" height="100%" style="object-fit:cover;">
																	<source src="{{ Media::url($media) }}" type="video/mp4">
																</video>
																<span class="al-play-center"><i class="fa fa-play"></i></span>
															</div>
														@endif
													@endforeach
												</div>
											@else
												@if(is_image($data->medias[0]))
													<div class="single-img al-media-click cursor-pointer" data-type="image" data-src="{{ Media::url($data->medias[0]) }}" style="background-image: url('{{ Media::url($data->medias[0]) }}');"></div>
												@else
													<div class="single-img al-media-click" data-type="video" data-src="{{ Media::url($data->medias[0]) }}">
														<video class="videotag" width="100%" height="100%" style="object-fit:cover;border-radius:10px;">
															<source src="{{ Media::url($data->medias[0]) }}" type="video/mp4">
														</video>
														<span class="al-play-center"><i class="fa fa-play"></i></span>
													</div>
												@endif
											@endif
										@else
											<div class="owl-carousel owl-theme">
												@if($post->link_icon)
													<div class="item w-80 h-80 b-r-10" style="background-image: url('{{ $post->link_icon }}');"></div>
												@endif
											</div>
										@endif
									@elseif($post->type == "link")
										<a href="{{ $data->link ?? '#' }}" target="_blank" class="d-flex align-items-center justify-content-center w-99 h-99 fs-30 bg-light-primary">
											<i class="fal fa-link"></i>
										</a>
									@else
										<div class="d-flex align-items-center justify-content-center w-80 h-80 fs-30 text-primary bg-light-primary">
											<i class="fal fa-align-center"></i>
										</div>
									@endif

								</div>

                                <div class="flex-grow-1 min-w-0">
                                    <p class="fs-13 mb-2">
                                        {!! nl2br(e($data->caption ?? '')) !!}
                                    </p>
                                    <span class="text-gray-500 fw-semibold d-block fs-13">
                                        <i class="fal fa-calendar-alt"></i> {{ date("h:i A", $post->time_post) }}
                                    </span>
                                </div>

                            </div>{{-- end media+caption flex --}}
                        </div>{{-- end outer d-flex --}}

                    </div>

					@if($post->status == 1)
                        @php
                            $resultData = json_decode($post->result);
                        @endphp
                        <div class="card-footer bg-light-warning text-warning py-2 px-3 d-flex justify-content-between">
							<div class="ribbon ribbon-triangle ribbon-top-start border-warning rounded">
								<div class="ribbon-icon mn-t-22 mn-l-22">
									<i class="fs-20 fad fa-exclamation-circle fs-2 text-white"></i>
								</div>
							</div>
                            <span class="me-2 fs-12">{{ __("Draft") }}</span>
                        </div>
                    @endif
					
					@if($post->status == 2)
                        @php
                            $resultData = json_decode($post->result);
                        @endphp
                        <div class="card-footer bg-light-warning text-warning py-2 px-3 d-flex justify-content-between">
							<div class="ribbon ribbon-triangle ribbon-top-start border-warning rounded">
								<div class="ribbon-icon mn-t-22 mn-l-22">
									<i class="fs-20 fad fa-exclamation-circle fs-2 text-white"></i>
								</div>
							</div>
                            <span class="me-2 fs-12">{{ __("Waiting for Approval") }}</span>
                        </div>
                    @endif
					
					@if($post->status == 3)
                        @php
                            $resultData = json_decode($post->result);
                        @endphp
                        <div class="card-footer bg-light-warning text-warning py-2 px-3 d-flex justify-content-between">
							<div class="ribbon ribbon-triangle ribbon-top-start border-primary rounded">
								<div class="ribbon-icon mn-t-22 mn-l-22">
									<i class="fs-20 fas fa-circle-notch fa-spin fs-2 text-white"></i>
								</div>
							</div>
                            <span class="me-2 fs-12">{{ __("Processing") }}</span>
                        </div>
                    @endif
					
                    @php
                        // Parse all per-platform results once for both status 4 and 5
                        preg_match_all('/\{(?:[^{}]|(?:\{[^{}]*\}))*\}/', $post->results ?? '', $_rm);
                        $_allParsed    = array_map(fn($j) => json_decode($j, true) ?? [], $_rm[0] ?? []);
                        $_successCount = count(array_filter($_allParsed, fn($r) => isset($r['url'])));
                        $_failMsgs     = array_values(array_filter(array_map(function($r) {
                            $msg = $r['message'] ?? null;
                            return ($msg && str_contains($msg, '--')) ? $msg : null;
                        }, $_allParsed)));
                        $_failCount    = count($_failMsgs);
                        // Clean platform name helper
                        $_cleanPlatform = fn($p) => trim(ucfirst(strtolower(preg_replace(
                            ['/([A-Z])/', '/\s+/'],
                            [' $1', ' '],
                            str_replace(['AppChannel','Profiles','Pages','Boards','Board','Official','Unofficial'], '', trim($p))
                        ))));
                    @endphp

                    @if($post->status == 4)
                        <div class="card-footer p-0 overflow-hidden pando-footer-success">
                            <div class="pando-footer-row pando-footer-row--success w-100">
                                <span class="pando-footer-icon pando-footer-icon--success">
                                    <i class="fad fa-check-double"></i>
                                </span>
                                <span class="pando-footer-text text-break">
                                    {{ __("Post Published") }}
                                    @if($_successCount > 0)
                                        <span class="pando-footer-badge pando-footer-badge--success">{{ $_successCount }} {{ $_successCount == 1 ? __('platform') : __('platforms') }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endif

                    @if($post->status == 5)
                        <div class="card-footer p-0 overflow-hidden pando-footer-partial flex-column">
                            @if($_successCount > 0)
                                <div class="pando-footer-row pando-footer-row--success w-100 align-items-center">
                                    <span class="pando-footer-icon pando-footer-icon--success">
                                        <i class="fad fa-check-double"></i>
                                    </span>
                                    <span class="pando-footer-text text-break">
                                        <strong>{{ $_successCount }} {{ $_successCount == 1 ? __('platform') : __('platforms') }}</strong> {{ __('published successfully') }}
                                    </span>
                                </div>
                            @endif
                            @if($_failCount > 0)
                                @foreach($_failMsgs as $_fm)
                                    @php [$_plat, $_rsn] = array_pad(explode('--', $_fm, 2), 2, 'Unknown error'); @endphp
                                    <div class="pando-footer-row pando-footer-row--danger w-100 align-items-center {{ !$loop->first ? 'pando-footer-row--border-top' : '' }}">
                                        <span class="pando-footer-icon pando-footer-icon--danger">
                                            <i class="fad fa-exclamation-circle"></i>
                                        </span>
                                        <span class="pando-footer-text text-break">
                                            <strong>{{ $_cleanPlatform($_plat) }}:</strong> {{ trim($_rsn) }}
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <div class="pando-footer-row pando-footer-row--danger w-100 align-items-center">
                                    <span class="pando-footer-icon pando-footer-icon--danger">
                                        <i class="fad fa-exclamation-circle"></i>
                                    </span>
                                    <span class="pando-footer-text text-break">{{ __('Publishing failed') }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

            @endforeach

        </div>
        
    @else
        
        <div class="text-center p-b-20">No post(s).</div>

    @endif
</div>

<style>
.cursor-pointer { cursor: pointer; }

.post-media-thumb {
    width: 90px; min-width: 90px; height: 90px;
    border-radius: 10px; overflow: hidden;
    flex-shrink: 0; position: relative;
    background: #f5f5f5; border: 1px solid #e8e8e8;
}
.post-media-thumb .single-img {
    width: 100%; height: 100%;
    background-size: cover; background-position: center; background-repeat: no-repeat;
    border-radius: 8px; cursor: pointer; display: block;
}
.post-media-thumb .owl-carousel,
.post-media-thumb .owl-stage-outer,
.post-media-thumb .owl-stage,
.post-media-thumb .owl-item { height: 90px !important; }
.post-media-thumb .owl-carousel { border-radius: 8px; overflow: hidden; }
.post-media-thumb .item {
    width: 90px; height: 90px;
    background-size: cover; background-position: center; background-repeat: no-repeat;
}
.post-media-thumb .owl-nav button {
    position: absolute !important; top: 50% !important;
    transform: translateY(-50%) !important; margin: 0 !important;
    width: 20px !important; height: 20px !important;
    background: rgba(0,0,0,0.45) !important; border-radius: 50% !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
}
.post-media-thumb .owl-nav button span { font-size: 13px !important; color: #fff !important; line-height: 1 !important; margin-top: -2px !important; }
.post-media-thumb .owl-nav .owl-prev { left: 2px !important; }
.post-media-thumb .owl-nav .owl-next { right: 2px !important; }
.post-media-thumb .owl-dots { position: absolute !important; bottom: 4px !important; left: 0 !important; right: 0 !important; text-align: center !important; margin: 0 !important; }
.post-media-thumb .owl-dot span { width: 5px !important; height: 5px !important; margin: 0 2px !important; background: rgba(255,255,255,0.6) !important; }
.post-media-thumb .owl-dot.active span { background: #fff !important; }


    /* ── Pando Post Footer ─────────────────────────────── */
    .pando-footer-row {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 7px 14px;
        font-size: 11.5px;
        line-height: 1.45;
        letter-spacing: 0.01em;
    }
    .pando-footer-row--border-top {
        border-top: 1px solid rgba(0,0,0,0.06);
    }
    .pando-footer-row--success {
        background: #f0fdf4;
        color: #166534;
    }
    .pando-footer-row--danger {
        background: #fff5f5;
        color: #9b1c1c;
    }
    .pando-footer-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        font-size: 10px;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .pando-footer-icon--success {
        background: #dcfce7;
        color: #16a34a;
    }
    .pando-footer-icon--danger {
        background: #fee2e2;
        color: #dc2626;
    }
    .pando-footer-text {
        flex: 1;
    }
    .pando-footer-text strong {
        font-weight: 600;
    }
    .pando-footer-badge--success {
        display: inline-block;
        background: #16a34a;
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 7px;
        border-radius: 20px;
        margin-left: 4px;
        vertical-align: middle;
    }
    
    /* ── alllist layout fix ──────────────────────────────── */
    .post-media-thumb {
        flex-shrink: 0;
    }

    /* ── alllist centered play badge ────────────────────── */
    .al-media-click {
        position: relative;
        cursor: pointer;
        display: block;
        width: 100%;
        height: 100%;
    }
    .al-play-center {
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
        pointer-events: none;
        z-index: 6;
        transition: transform 0.15s ease;
    }
    .al-media-click:hover .al-play-center {
        transform: translate(-50%, -50%) scale(1.12);
    }

    /* ── alllist lightbox ────────────────────────────────── */
    #al-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.88);
        z-index: 99999;
        align-items: center;
        justify-content: center;
    }
    #al-lightbox.active { display: flex; }
    #al-lightbox-media {
        max-width: 90vw;
        max-height: 85vh;
        border-radius: 10px;
        object-fit: contain;
        box-shadow: 0 8px 40px rgba(0,0,0,0.5);
    }
    #al-lightbox-close {
        position: absolute;
        top: 18px;
        right: 24px;
        color: #fff;
        font-size: 36px;
        cursor: pointer;
        line-height: 1;
        background: none;
        border: none;
        padding: 0;
        opacity: 0.85;
        z-index: 100000;
    }
    #al-lightbox-close:hover { opacity: 1; }

    </style>

<script>
function prepHrefzoom1(_this) {
    const $trigger = $(_this);
    const $wrap = $trigger;
    const url = $(_this).attr('data-src');
    const typem = $(_this).attr('data-type');
    const downloadSrc = url;
    const itemButtons = (typem == 'video')
        ? ['download', 'close']
        : ['zoom', 'slideShow', 'thumbs', 'download', 'close'];
    
    if (!url) return;
    
    $.fancybox.open([{
        src: url,
        type: typem,
        opts: {
            caption: $trigger.data('caption') || '',
            thumb: url,
            downloadSrc: url
        }
    }], {
        toolbar: true,
        buttons: ['zoom', 'slideShow', 'thumbs', 'download', 'close'],
        btnTpl: {
            download:
                '<a data-fancybox-download class="fancybox-button fancybox-button--download" ' +
                'title="Download" download target="_blank" href="">' +
                '<i class="fa fa-download"></i>' +
                '</a>'
        },
        afterShow: function (instance, current) {
            const $btn = instance.$refs?.toolbar?.find('[data-fancybox-download]');
            if (!$btn || !$btn.length) return;

            let src = current.opts.downloadSrc || '';

            if (!src) {
                if (current.type === 'image') {
                    src = (current.$image && current.$image[0]?.src) || current.src || '';
                } else if (current.type === 'video') {
                    const $v = current.$content && current.$content.find('video');
                    if ($v && $v.length) {
                        src = $v.find('source[src]').attr('src') || $v.attr('src') || '';
                    }
                } else if (current.type === 'iframe') {
                    src = '';
                }
            }

            if (!src) {
                $btn.hide();
                return;
            }

            const clean = s => String(s || '').replace(/<[^>]+>/g, '').trim().replace(/[^\w.-]+/g, '_');
            const name = clean(current.opts.caption) || 'download';
            const ext = ((src.match(/\.(mp4|webm|ogg|jpe?g|png|webp|gif|svg)(?=($|\?))/i) || [, 'bin'])[1]).toLowerCase();

            $btn.attr('href', src).attr('download', `${name}.${ext}`).show();
        }
    });
}
</script>

<!-- alllist lightbox -->
<div id="al-lightbox">
    <button id="al-lightbox-close">&times;</button>
</div>
<script>
(function() {
    // Move lightbox to <body> so position:fixed isn't trapped by overflow:auto ancestors
    $('body').append($('#al-lightbox').detach());
    var $lb    = $('#al-lightbox');
    var $close = $('#al-lightbox-close');

    function openLightbox(type, src) {
        $lb.find('#al-lightbox-media').remove();
        var $m = type === 'video'
            ? $('<video>', { id: 'al-lightbox-media', controls: true, autoplay: true, playsinline: true })
                  .append($('<source>', { src: src, type: 'video/mp4' }))
            : $('<img>', { id: 'al-lightbox-media', src: src });
        $lb.append($m).addClass('active');
        $('body').css('overflow', 'hidden');
    }

    function closeLightbox() {
        var v = document.getElementById('al-lightbox-media');
        if (v && v.tagName === 'VIDEO') v.pause();
        $lb.removeClass('active').find('#al-lightbox-media').remove();
        $('body').css('overflow', '');
    }

    $close.on('click', closeLightbox);
    $lb.on('click', function(e) { if ($(e.target).is('#al-lightbox')) closeLightbox(); });
    $(document).on('keydown.al', function(e) { if (e.key === 'Escape') closeLightbox(); });

    $(document).on('click', '.al-media-click', function(e) {
        e.stopPropagation();
        openLightbox($(this).data('type'), $(this).data('src'));
    });
})();
</script>