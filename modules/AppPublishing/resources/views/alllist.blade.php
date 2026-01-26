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
                
                <div class="card item overflow-hidden">

                    {{-- Status Ribbon --}}
                    @if($post->status == 1)
                        <div class="ribbon ribbon-triangle ribbon-top-start border-primary rounded">
                            <div class="ribbon-icon mn-t-22 mn-l-22">
                                <i class="fs-20 fas fa-circle-notch fa-spin fs-2 text-white"></i>
                            </div>
                        </div>
                        <div class="border-primary border-top-dashed border-1"></div>
                    @elseif($post->status == 3)
                        <div class="ribbon ribbon-triangle ribbon-top-start border-success rounded">
                            <div class="ribbon-icon mn-t-22 mn-l-22">
                                <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
                            </div>
                        </div>
                        <div class="border-success border-top-dashed border-1"></div>
                    @elseif($post->status == 4)
                        <div class="ribbon ribbon-triangle ribbon-top-start border-danger rounded">
                            <div class="ribbon-icon mn-t-22 mn-l-22">
                                <i class="fs-20 fad fa-exclamation-circle fs-2 text-white"></i>
                            </div>
                        </div>
                        <div class="border-danger border-top-dashed border-1"></div>
                    @endif
                    
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
                            <a href="{{ url_app('post?post_id=' . $post->ids) }}" class="icon-with-circle">
                                {!! file_get_contents(public_path('img/post.svg')) !!}
                            </a>
                            <a href="{{ url_app('post?type=duplicate&post_id=' . $post->ids) }}" class="icon-with-circle" title="{{ __('Duplicate Post') }}" data-toggle="tooltip" data-placement="top">
                                {!! file_get_contents(public_path('img/duplicate.svg')) !!}
                            </a>
                            <a href="{{ module_url('delete') }}" class="icon-with-circle actionItem" data-remove="item" data-id="{{ $post->grouping_data }}" data-confirm="{{ __('Are you sure to delete this items?') }}" data-call-success="location.reload();">
                                {!! file_get_contents(public_path('img/delete.svg')) !!}
                            </a>
                            
                            @if($post->status == 3)
                                <div class="ml-auto ms-3">
                                    <div class="sp-menu-dropdown dropdown dropdown-hide-arrow" data-dropdown-spacing="0">
                                        <a class="dropdown-toggle text-gray-800" style="font-size:25px;" href="javascript:void(0);" data-bs-toggle="dropdown">
                                            <i class="fal fa-ellipsis-v"></i>
                                        </a>
                                        <ul class="dropdown-menu py-2" data-dropdown-spacing="0">
                                            @php
                                                $socialNetworks = explode(',', $post->social_networks);
                                                $idAll = explode(',', $post->id_all);
                                                $results = explode(',', $post->results);
                                            @endphp
                                            
                                            @if(!empty($socialNetworks))
                                                @foreach($socialNetworks as $kjs => $snt)
                                                    @php
                                                        $config = \Module::find(trim($snt) . "_post");
                                                        $menu = $config ? $config->get('menu') : null;
                                                        $resultData = json_decode($results[$kjs] ?? '{}', true);
                                                        $surl = $resultData['url'] ?? '#';
                                                    @endphp
                                                    
                                                    <li>
                                                        <a class="dropdown-item py-2" target="_blank" href="{{ $surl }}">
                                                           {{ get_social_media_icon_large(trim($snt)) }}
                                                            Show in {{ trim($snt) ?? '' }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>

                    <div class="card-body p-3">
                        
                        <div class="d-flex align-items-center">
                        
                            <div class="symbol symbol-40px me-3">
                                <input type="checkbox" value="{{ $post->id }}" name="approval_post_ids[]"/>
                            </div>
							
							<div class="d-block w-100">
                            
								<div class="symbol symbol-100px me-3 mb-1 overflow-hidden w-80 border b-r-10 float-start">

									@if($post->type == "media")
										@if(!empty($data->medias))
											@if(count($data->medias) > 1)
												<div class="owl-carousel owl-theme">
													@foreach($data->medias as $index => $media)
														@if(is_image($media))
															<div class="item w-80 h-80 b-r-10 cursor-pointer" onclick="prepHrefzoom1(this)" data-src="{{ Media::url($media) }}" style="background-image: url('{{ Media::url($media) }}');"></div>
														@else
															<div class="item w-80 h-80 b-r-10">
																<div class="fm-list-media rounded d-flex flex-column align-items-center justify-content-center fs-40 text-xc cursor-pointer" onclick="prepHrefzoom1(this)" data-src="{{ Media::url($media) }}" style="height:100%">
																	<video class="videotag" width="100%" height="100%" poster="" style="background-color: #093f730d">
																		<source src="{{ Media::url($media) }}" type="video/mp4">
																		Your browser does not support the video tag.
																	</video>
																	<button type="button" class="text-white playbtn" href="javascript:void(0)" style="position: absolute;top: 5px;margin: auto;background: none;border: none;width: 33px;height: 23px;">
																		<svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="play-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-play-circle fa-w-16" style="width: 20px;margin-left: auto;margin-right: auto;">
																			<g class="fa-group">
																				<path fill="black" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm115.7 272l-176 101c-15.8 8.8-35.7-2.5-35.7-21V152c0-18.4 19.8-29.8 35.7-21l176 107c16.4 9.2 16.4 32.9 0 42z" opacity="0.5" class="fa-secondary"></path>
																				<path fill="white" d="M371.7 280l-176 101c-15.8 8.8-35.7-2.5-35.7-21V152c0-18.4 19.8-29.8 35.7-21l176 107c16.4 9.2 16.4 32.9 0 42z" class="fa-primary"></path>
																			</g>
																		</svg>
																	</button>
																</div>
															</div>
														@endif
													@endforeach
												</div>
											@else
												@if(is_image($data->medias[0]))
													<div class="item w-80 h-80 b-r-10 cursor-pointer" onclick="prepHrefzoom1(this)" data-src="{{ Media::url($data->medias[0]) }}" style="background-size: cover;background-image: url('{{ Media::url($data->medias[0]) }}');"></div>
												@else
													<div class="item w-80 h-80 b-r-10">
														<div class="fm-list-media rounded d-flex flex-column align-items-center justify-content-center fs-40 text-xc cursor-pointer" onclick="prepHrefzoom1(this)" data-src="{{ Media::url($data->medias[0]) }}" style="height:100%">
															<video class="videotag" width="100%" height="100%" poster="" style="background-color: #093f730d">
																<source src="{{ Media::url($data->medias[0]) }}" type="video/mp4">
																Your browser does not support the video tag.
															</video>
															<button type="button" class="text-white playbtn" href="javascript:void(0)" style="position: absolute;top: 5px;margin: auto;background: none;border: none;width: 33px;height: 23px;">
																<svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="play-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-play-circle fa-w-16" style="width: 20px;margin-left: auto;margin-right: auto;">
																	<g class="fa-group">
																		<path fill="black" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm115.7 272l-176 101c-15.8 8.8-35.7-2.5-35.7-21V152c0-18.4 19.8-29.8 35.7-21l176 107c16.4 9.2 16.4 32.9 0 42z" opacity="0.5" class="fa-secondary"></path>
																		<path fill="white" d="M371.7 280l-176 101c-15.8 8.8-35.7-2.5-35.7-21V152c0-18.4 19.8-29.8 35.7-21l176 107c16.4 9.2 16.4 32.9 0 42z" class="fa-primary"></path>
																	</g>
																</svg>
															</button>
														</div>
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
                            
                            
                                <div class="flex-grow-1">
                                    <p class="fs-13 mb-2">
                                        {!! nl2br(e($data->caption ?? '')) !!}
                                    </p>
                                    <span class="text-muted fw-semibold d-block fs-13">
                                        <i class="fal fa-calendar-alt"></i> {{ date("h:i A", $post->time_post) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>

                    @if($post->status == 3)
                        @php
                            $resultData = json_decode($post->result);
                        @endphp
                        <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
                            <span class="me-2">{{ __("Post Published") }}</span>
                        </div>
                    @endif
					
					@if($post->status == 2)
                        @php
                            $resultData = json_decode($post->result);
                        @endphp
                        <div class="card-footer bg-light-warning text-warning py-3 px-4 d-flex justify-content-between">
                            <span class="me-2">{{ __("Waiting for Approval") }}</span>
                        </div>
                    @endif

                    @if($post->status == 4)
                        @php
                            $error = json_decode($post->result);
                        @endphp
                        <div class="card-footer bg-light-danger text-danger py-3 px-4">
                            {{ $error->message ?? 'Error occurred' }}
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
.cursor-pointer {
    cursor: pointer;
}
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