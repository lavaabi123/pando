@if($folder)
<input type="radio" class="d-none form-check-input ajax-scroll-filter" name="folder_id" value="{{ $folder->id_secure }}" checked>
<nav aria-label="breadcrumb" class="mb-0">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
        <label class="fs-12 text-primary fw-5 text-hover-primary pointer" id="breadcrumb_folder_0">
            {{ __("Root Folder") }}
            <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="0" id="breadcrumb_folder_0">
        </label>
    </li>
    @foreach ($parent_folders as $parent)
        <li class="breadcrumb-item">
            <label class="fs-12 text-primary fw-5 text-hover-primary-900 pointer" id="breadcrumb_folder_{{ $parent->id_secure }}">
                {{ $parent->name }}
                <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="{{ $parent->id_secure }}" id="breadcrumb_folder_{{ $parent->id_secure }}">
            </label>
        </li>
    @endforeach
    <li class="breadcrumb-item fs-12 text-gray-400 active" aria-current="page">{{ $folder->name }}</li>
  </ol>
</nav>
@endif

@if($folders)
    @foreach($folders as $value)
    <div class="col-4 px-2">
        <div class="ratio ratio-1x1 mb-3">
            <label class="d-flex flex-column flex-fill w-100 bg-light border b-r-10 w-100 pointer mb-3" for="folder_{{ $value->id_secure }}">
                <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="{{ $value->id_secure }}" id="folder_{{ $value->id_secure }}">
                <div class="d-flex flex-fill align-items-center justify-content-center p-2 bg-warning-100">
                    <div class="fs-30 text-warning">
                        <i class="fa-light fa-folder-open"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-auto p-1 gap-8 position-relative zIndex-2 border-top">
                    <div class="text-truncate">
                        <div class="fs-9 text-gray-800 fw-5 lh-sm text-truncate">{{ $value->name }}</div>
                        <div class="fs-8 d-flex align-items-center gap-8 text-gray-600 lh-sm text-truncate">
                            <span>{{ sprintf("%d Files", $value->file_count) }} </span>
                            <span class="d-inline-block size-4 b-r-50 bg-gray-400"></span> 
                            <span>{{ sprintf("%d Folder", $value->folder_count) }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="btn-group position-static">
                            <div class="dropdown-toggle dropdown-arrow-hide text-gray-900 fs-12" data-bs-toggle="dropdown" aria-expanded="true">
                                <i class="fa-light fa-grid-2"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-1 w-100 max-w-180 min-w-120">
                                <li>
                                    <a class="dropdown-item px-2 p-t-4 p-b-4 rounded d-flex gap-8 fw-5 fs-13 actionItem" href="{{ url_app("files/destroy") }}" data-id="{{ $value->id_secure }}" data-call-success="Main.ajaxScroll(true)">
                                        <span class="size-16 me-0 text-center"><i class="fa-light fa-trash-can"></i></span>
                                        <span>{{ __('Delete') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </label>
        </div>
    </div>
    @endforeach
@endif

@if($files->isEmpty() && (!$folders || $folders->isEmpty()))
<div class="col-12 text-center text-gray-400 py-5">
    <i class="fa-light fa-photo-film fs-40 mb-2"></i>
    <div class="fs-13">{{ __('No files yet') }}</div>
</div>
@endif

@foreach($files as $value)
    @php
    $detectType  = Media::detectFileIcon($value->detect);
    $thumbsJson  = $value->thumbnails ?? '[]';
    $thumbsArr   = json_decode($thumbsJson, true) ?? [];
    @endphp

    <div class="col-4 px-2">
        {{-- 
            data-id-secure  → used by composer to fetch thumbnails
            data-thumbnails → pre-built comma-list of resolved thumb URLs (may be empty if still generating)
            data-type       → 'video' | 'image' | etc. (used by composer JS)
        --}}
        <div class="file-item w-100 ratio ratio-1x1 min-h-80 mb-3 border b-r-6"
             data-id="{{ 'file_' . $value->id_secure }}"
             data-name="medias"
             data-file="{{ Media::url($value->file) }}"
             data-type="{{ $value->detect }}"
             data-id-secure="{{ $value->id_secure }}"
             data-thumbnails="{{ implode(',', array_map(fn($t) => Media::url($t), $thumbsArr)) }}"
        >
            <label class="d-flex flex-column flex-fill" for="{{ $value->id_secure }}">
                <div class="position-absolute r--1 t--1 zIndex-3">
                    <div class="form-check form-check-sm">
                        <input class="form-check-input" name="id[]" type="checkbox" value="{{ $value->file }}" id="file_{{ $value->id_secure }}">
                    </div>
                </div>

                <div class="d-flex flex-fill align-items-center justify-content-center overflow-hidden position-relative 
                            btl-r-6 btr-r-6 file-item-media text-{{ $detectType['color'] }} bg-{{ $detectType['color'] }}-100"
                    @if($value->detect == "image")
                        style="background-image: url('{{ Media::url($value->file) }}'); background-size: cover; background-position: center;"
                    @endif
                >
                    @if($value->detect == "video")
                        {{-- Show first generated thumbnail as poster if available --}}
                        @if(!empty($thumbsArr))
                            <img src="{{ Media::url($thumbsArr[0]) }}" class="position-absolute top-0 start-0 w-100 h-100 object-cover" alt="">
                            <div class="position-absolute d-flex align-items-center justify-content-center w-100 h-100" style="background:rgba(0,0,0,0.25);">
                                <i class="fa-solid fa-circle-play fs-16 text-white"></i>
                            </div>
                        @else
                            <video class="position-absolute top-0 start-0 w-100 h-100 object-cover" muted playsinline preload="metadata">
                                <source src="{{ Media::url($value->file) }}#t=1" type="video/mp4">
                            </video>
                            <div class="position-absolute d-flex align-items-center justify-content-center w-100 h-100" style="background:rgba(0,0,0,0.25);">
                                <i class="fa-solid fa-circle-play fs-16 text-white"></i>
                            </div>
                        @endif
                    @elseif($value->detect != "image")
                        <div class="fs-30">
                            <i class="{{ $detectType['icon'] }}"></i>
                        </div>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center mt-auto p-1 gap-8 position-relative zIndex-4 file-info border-top">
                    <div class="text-truncate">
                        <div class="fs-9 text-gray-800 fw-5 lh-sm text-truncate">{{ $value->name }}</div>
                        <div class="fs-8 text-gray-600 lh-sm text-truncate">{{ Number::fileSize($value->size) }}</div>
                    </div>

                    <div>
                        <div class="btn-group position-static">
                            <div class="dropdown-toggle dropdown-arrow-hide text-gray-900 fs-12" data-bs-toggle="dropdown" aria-expanded="true">
                                <i class="fa-light fa-grid-2"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-1 w-100 max-w-180 min-w-120">
                                @can('appfiles.image_editor')
                                    @if($value->detect == "image")
                                    <li>
                                        <button type="button" class="dropdown-item px-2 p-t-4 p-b-4 rounded d-flex gap-8 fw-5 fs-13 editImage" data-file="{{ Media::url($value->file) }}" data-id="{{ $value->id_secure }}">
                                            <span class="size-16 me-0 text-center"><i class="fa-light fa-edit"></i></span>
                                            <span>{{ __('Edit Image') }}</span>
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    @endif
                                @endcan
                                <li>
                                    <a class="dropdown-item px-2 p-t-4 p-b-4 rounded d-flex gap-8 fw-5 fs-13 actionItem" href="{{ url_app("files/destroy") }}" data-id="{{ $value->file }}" data-call-success="Main.ajaxScroll(true)">
                                        <span class="size-16 me-0 text-center"><i class="fa-light fa-trash-can"></i></span>
                                        <span>{{ __('Delete') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </label>
        </div>
    </div>
@endforeach
