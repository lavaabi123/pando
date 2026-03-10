/**
 * Pando Composer – Custom Video Thumbnail
 * Reads thumbnails from data-thumbnails attribute on file items (no polling).
 */

"use strict";

window.ComposerThumbnail = window.ComposerThumbnail || {};

var ComposerThumbnail = new (function () {

    var CT = this;

    // ── Public API ────────────────────────────────────────────────────────────

    CT.init = function () {
        CT._injectPanel();
        CT._watchSelectedFiles();
        CT._watchThumbnailPick();
        CT._watchFromMediaBtn();
        CT._watchRemoveThumbnail();
    };

    // ── Panel injection ───────────────────────────────────────────────────────

    CT._injectPanel = function () {
        if ($('#composer-thumbnail-panel').length) return;

        var html = [
            '<div id="composer-thumbnail-panel" class="d-none mt-3">',
            '  <div class="d-flex align-items-center justify-content-between mb-2">',
            '    <span class="fs-13 fw-6 text-gray-800">',
            '      <i class="fa-light fa-image me-1"></i> Custom Thumbnail',
            '    </span>',
            '    <button type="button" id="ct-remove-btn" class="btn btn-sm btn-outline-danger d-none" style="padding:2px 10px; font-size:11px;">',
            '      <i class="fa-light fa-xmark me-1"></i>Remove',
            '    </button>',
            '  </div>',

            '  <div id="ct-selected-wrap" class="d-none mb-2 position-relative" style="width:80px; height:50px;">',
            '    <img id="ct-selected-img" src="" alt="Thumbnail" class="w-100 h-100 object-cover b-r-6 border">',
            '    <span class="position-absolute bottom-0 end-0 bg-success text-white" style="font-size:9px; padding:1px 4px; border-radius:3px;">✓</span>',
            '  </div>',

            '  <div id="ct-thumbs-grid" class="d-flex flex-wrap gap-6"></div>',

            '  <div id="ct-no-thumbs" class="d-none text-gray-500 fs-12 py-1">',
            '    No thumbnails available for this video.',
            '  </div>',

            '  <button type="button" id="ct-from-media-btn" class="btn btn-xs btn-secondary mt-2" style="font-size:11px;">',
            '    <i class="fa-light fa-photo-film me-1"></i> CHOOSE FROM MEDIA',
            '  </button>',

            '  <input type="hidden" name="custom_thumbnail" id="ct-hidden-input" value="">',
            '  <input type="hidden" name="custom_thumbnail_index" id="ct-hidden-index" value="-1">',
            '</div>',
        ].join('\n');

        var $anchor = $('.compose-icons').first();
        if ($anchor.length) {
            $anchor.after(html);
        } else {
            $('.compose-editor').first().append(html);
        }
    };

    // ── Watch file selection changes ──────────────────────────────────────────

    CT._watchSelectedFiles = function () {
        var target = document.querySelector('.file-selected-media .items');
        if (!target) {
            setTimeout(CT._watchSelectedFiles, 800);
            return;
        }

        var observer = new MutationObserver(function () {
            CT._onSelectionChanged();
        });
        observer.observe(target, { childList: true });

        CT._onSelectionChanged();
    };

    CT._onSelectionChanged = function () {
        var videoItems = CT._getSelectedVideoItems();

        if (videoItems.length === 0) {
            CT._hidePanel();
            return;
        }

        CT._showPanel();
        CT._clearSelection();

        // Use the first video's thumbnails
        var $firstVideo  = $(videoItems[0]);
        var thumbsData   = ($firstVideo.data('thumbnails') || '').toString().trim();
        var thumbUrls    = thumbsData ? thumbsData.split(',').filter(Boolean) : [];

        $('#ct-thumbs-grid').empty();
        $('#ct-no-thumbs').addClass('d-none');

        if (thumbUrls.length > 0) {
            CT._renderThumbs(thumbUrls);
        } else {
            $('#ct-no-thumbs').removeClass('d-none');
        }
    };

    CT._getSelectedVideoItems = function () {
        return $('.file-selected-media .items .file-item[data-type="video"]').toArray();
    };

    // ── Panel show/hide ───────────────────────────────────────────────────────

    CT._showPanel = function () {
        $('#composer-thumbnail-panel').removeClass('d-none');
    };

    CT._hidePanel = function () {
        $('#composer-thumbnail-panel').addClass('d-none');
        CT._clearSelection();
    };

    // ── Thumbnail grid rendering ──────────────────────────────────────────────

    CT._renderThumbs = function (urls) {
        var $grid = $('#ct-thumbs-grid').empty();

        $.each(urls, function (i, url) {
            if (!url) return;
            var $thumb = $('<div>', {
                'class'      : 'ct-thumb-item border b-r-6 pointer overflow-hidden',
                'style'      : 'width:72px; height:46px; flex-shrink:0; cursor:pointer; position:relative;',
                'data-url'   : url,
                'data-index' : i
            });
            $('<img>', {
                src   : url,
                alt   : 'Thumb ' + (i + 1),
                style : 'width:100%; height:100%; object-fit:cover; display:block;'
            }).appendTo($thumb);

            $grid.append($thumb);
        });
    };

    // ── Event: click a generated thumbnail ───────────────────────────────────

    CT._watchThumbnailPick = function () {
        $(document).on('click', '.ct-thumb-item', function () {
            var url   = $(this).data('url');
            var index = $(this).data('index');
            CT._selectThumbnail(url, index);
            $('.ct-thumb-item').css('outline', '');
            $(this).css('outline', '2px solid #22c55e');
        });
    };

    // ── Event: "Choose from Media" button ────────────────────────────────────

    CT._watchFromMediaBtn = function () {
        $(document).on('click', '#ct-from-media-btn', function () {
            CT._openMediaPicker();
        });
    };

    CT._openMediaPicker = function () {
        var modalId = 'ctMediaPickerModal';
        $('#' + modalId).remove();

        var $modal = $([
            '<div class="modal fade" id="' + modalId + '" tabindex="-1">',
            '  <div class="modal-dialog modal-lg">',
            '    <div class="modal-content">',
            '      <div class="modal-header py-2 px-3">',
            '        <h6 class="modal-title mb-0">Choose Thumbnail from Media</h6>',
            '        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>',
            '      </div>',
            '      <div class="modal-body p-0" style="max-height:420px; overflow-y:auto;">',
            '        <div class="row px-3 pt-3" id="ct-picker-list"></div>',
            '      </div>',
            '      <div class="modal-footer py-2">',
            '        <button type="button" class="btn btn-primary btn-sm" id="ct-picker-confirm" disabled>Use as Thumbnail</button>',
            '        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>',
            '      </div>',
            '    </div>',
            '  </div>',
            '</div>',
        ].join('')).appendTo('body');

        var selectedPickerUrl = null;

        $.post((VARIABLES.url || '') + 'app/files/mini_list', {
            file_type : 'image',
            team_id   : VARIABLES.team_id || 0,
            page      : 0,
            _token    : VARIABLES.csrf
        }, function (res) {
            if (res.status && res.data) {
                var $parsed = $(res.data);
                $parsed.find('.file-item').each(function () {
                    var $item   = $(this);
                    var fileUrl = $item.data('file');
                    if (!fileUrl) return;
                    $item.css('cursor', 'pointer').on('click', function () {
                        $parsed.find('.file-item').css('outline', '');
                        $item.css('outline', '2px solid #22c55e');
                        selectedPickerUrl = fileUrl;
                        $('#ct-picker-confirm').prop('disabled', false);
                    });
                });
                $('#ct-picker-list').html($parsed);
            }
        }, 'json');

        $(document).on('click', '#ct-picker-confirm', function () {
            if (selectedPickerUrl) {
                CT._selectThumbnail(selectedPickerUrl, -1);
                $('.ct-thumb-item').css('outline', '');
            }
            var modal = bootstrap.Modal.getInstance($modal[0]);
            if (modal) modal.hide();
        });

        $modal.on('hidden.bs.modal', function () {
            $modal.remove();
        });

        new bootstrap.Modal($modal[0]).show();
    };

    // ── Select / clear thumbnail ──────────────────────────────────────────────

    CT._selectThumbnail = function (url, index) {
        $('#ct-hidden-input').val(url);
        $('#ct-hidden-index').val(index !== undefined ? index : -1);
        $('#ct-selected-img').attr('src', url);
        $('#ct-selected-wrap').removeClass('d-none');
        $('#ct-remove-btn').removeClass('d-none');
    };

    CT._clearSelection = function () {
        $('#ct-hidden-input').val('');
        $('#ct-hidden-index').val('-1');
        $('#ct-selected-img').attr('src', '');
        $('#ct-selected-wrap').addClass('d-none');
        $('#ct-remove-btn').addClass('d-none');
        $('.ct-thumb-item').css('outline', '');
    };

    // ── Event: remove button ──────────────────────────────────────────────────

    CT._watchRemoveThumbnail = function () {
        $(document).on('click', '#ct-remove-btn', function () {
            CT._clearSelection();
        });
    };

})();

$(function () {
    setTimeout(function () {
        ComposerThumbnail.init();
    }, 300);
});