<div class="modal fade" id="pubishingPreviewModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content actionForm" action="{{ module_url('save') }}" data-call-success="Main.closeModal('pubishingPreviewModal'); Main.ajaxScroll(true);">
            <input type="text" class="d-none" name="type" value="0">
            <div class="modal-header">
                <h1 class="modal-title fs-16">{{ __("Preview") }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(isset($frame_posts) && $frame_posts->count() > 0)
                    <div class="mt-1">
                        {{-- Navigation Pills --}}
                        <ul class="nav nav-pills" style="float:left" role="tablist">
                            @foreach($frame_posts as $key => $value)                                
                                <li class="nav-item">
                                    <a class="btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-0 p-l-14 p-r-14 p-t-14 p-b-14 text-center nav-link {{ $key == 0 ? 'active' : '' }}" 
                                       data-bs-toggle="pill" 
                                       data-bs-target="#pills-{{ $value->id }}" 
                                       role="tab">
                                        {{ get_social_media_icon_large($value->social_network) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Dropdown Menu --}}
                        @if(empty(request()->input('from')))
                            <div class="col-1" style="float: left;">
                                <div class="sp-menu-dropdown dropdown dropdown-hide-arrow" data-dropdown-spacing="0">
                                    <a class="dropdown-toggle text-gray-800" style="font-size:25px;float: right;" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fal fa-ellipsis-v" style="vertical-align: bottom;"></i>
                                    </a>
                                    <ul class="dropdown-menu" data-dropdown-spacing="0">
                                        @foreach($frame_posts as $key => $value)
                                            @php
                                                $module = $value->module ?? '';
                                                $resApi = json_decode($value->result, true) ?? [];
                                                $displayName = $module == 'twitter' ? 'X' : ucfirst($module);
                                            @endphp
                                            
                                            <li class="p-3">
                                                @if(!empty($resApi['url']))
                                                    <a class="dropdown-item" target="_blank" href="{{ $resApi['url'] }}">
                                                        {{ get_social_media_icon_large($value->social_network) }}
                                                        Show in {{ $displayName }}
                                                    </a>
                                                @else
                                                    <a class="dropdown-item disabled" href="javascript:void(0);">
                                                        {{ get_social_media_icon_large($value->social_network) }}
                                                        Show in {{ $displayName }}
                                                    </a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Tab Content --}}
                    <div class="tab-content" style="clear: both;">
                        @foreach($frame_posts as $key => $value)
                            
                            <div class="tab-pane p-3 {{ $key == 0 ? 'active' : '' }}" id="pills-{{ $value->id }}" role="tabpanel">
                                @if($value)
                                     @php
										$postType = 'media';
										$caption = '';
										$medias = [];
										$link = '';

										if ($value) {
											$postType = $value->type ?? 'media';
											$postData = json_decode($value->data, false);
											$caption = $postData->caption ?? '';
											$medias = $postData->medias ?? [];
											$link = $postData->link ?? '';
										}
									@endphp

									<div class="d-none">
										<input type="hidden" class="preview-post-type" value="{{ $postType }}">

										@if ($value && $value->account)
											<input type="hidden" class="preview-profile"
												data-social-network="{{ $value->account->social_network ?? '' }}"
												data-avatar="{{ $value->account->avatar ? Media::url($value->account->avatar) : '' }}"
												data-name="{{ $value->account->name ?? '' }}"
												data-username="{{ $value->account->username ?? '' }}"
												data-link="{{ $value->account->link ?? '' }}">
										@endif

										<div class="preview-list-medias">
											@foreach ($medias as $media)
												<img src="{{ Media::url($media) }}">
											@endforeach
										</div>

										<textarea class="form-control input-emoji fw-4 border" name="caption" placeholder="{{ __("Enter caption") }}">{{ $caption }}</textarea>
									</div>

									@php
										$module = strtolower($value->module ?? '');
										$view = $module ? $module.'::preview' : null;
									@endphp

									@if($view && view()->exists($view))
										<div class="cpvx" data-social-network="{{ $value->social_network ?? '' }}">
											@include($view)
										</div>
									@endif
                                @else
                                    <div class="alert alert-warning">
                                        Preview not available for {{ ucfirst($module) }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        {{ __('No data found!') }}
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    Main.init(false);
    AppPubishing.init(false);
    Files.init(false);
</script>