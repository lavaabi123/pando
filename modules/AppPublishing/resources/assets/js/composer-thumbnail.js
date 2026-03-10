/**
 * Pando Composer – Custom Video Thumbnail
 *
 * Drop this script into AppPublishing/resources/assets/js/
 * and include it in the publishing composer blade view.
 *
 * Dependencies: jQuery, VARIABLES.url, VARIABLES.csrf
 *
 * What it does:
 *  1. Watches when files are added to the composer's "file-selected-media" area.
 *  2. If any selected file is a video, shows the "Custom Thumbnail" panel.
 *  3. Loads generated thumbnails from the server (polling if still pending).
 *  4. Allows picking a thumbnail from generated thumbs OR from Media library.
 *  5. Stores the chosen thumbnail URL in a hidden input <input name="custom_thumbnail">.
 *  6. Sends the thumbnail to every social network's POST call via PublishingService.
 */

"use strict";

window.ComposerThumbnail = window.ComposerThumbnail || {};

var ComposerThumbnail = new (function () {

    var CT = this;
    var filesApiUrl  = (VARIABLES.url || '') + 'app/files/';
    var pollTimers   = {};   // keyed by id_secure
    var pollMaxTries = 15;   // max ~45s at 3s intervals

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

            '  <!-- Selected thumbnail preview -->',
            '  <div id="ct-selected-wrap" class="d-none mb-2 position-relative" style="width:80px; height:50px;">',
            '    <img id="ct-selected-img" src="" alt="Thumbnail" class="w-100 h-100 object-cover b-r-6 border">',
            '    <span class="position-absolute bottom-0 end-0 bg-success text-white" style="font-size:9px; padding:1px 4px; border-radius:3px;">✓</span>',
            '  </div>',

            '  <!-- Thumbnail grid (generated from video) -->',
            '  <div id="ct-thumbs-wrap" class="position-relative">',
            '    <div id="ct-thumbs-loading" class="text-gray-500 fs-12 py-2 text-center d-none">',
            '      <span class="spinner-border spinner-border-sm me-1"></span> Generating thumbnails…',
            '    </div>',
            '    <div id="ct-thumbs-grid" class="d-flex flex-wrap gap-6"></div>',
            '  </div>',

            '  <!-- From media library -->',
            '  <button type="button" id="ct-from-media-btn" class="btn btn-xs btn-secondary mt-2" style="font-size:11px;">',
            '    <i class="fa-light fa-photo-film me-1"></i>Choose from Media',
            '  </button>',

            '  <!-- Hidden inputs sent with form -->',
            '  <input type="hidden" name="custom_thumbnail" id="ct-hidden-input" value="">',
            '  <!-- -1 = from media library (no frame index), 0-9 = generated frame index -->',
            '  <input type="hidden" name="custom_thumbnail_index" id="ct-hidden-index" value="-1">',
            '</div>',
        ].join('\n');

        // Insert after the "file-selected-media" area inside compose-editor
        var $anchor = $('.compose-icons').first();
        if ($anchor.length) {
            $anchor.after(html);
        } else {
            // Fallback: append before POST NOW button area
            $('.compose-editor .d-flex.flex-column.flex-column-fluid').first().append(html);
        }
    };

    // ── Watch file selection changes ──────────────────────────────────────────

    CT._watchSelectedFiles = function () {
        // MutationObserver on the selected items container
        var target = document.querySelector('.file-selected-media .items');
        if (!target) {
            // Retry once DOM is ready
            setTimeout(CT._watchSelectedFiles, 800);
            return;
        }

        var observer = new MutationObserver(function () {
            CT._onSelectionChanged();
        });
        observer.observe(target, { childList: true });

        // Also trigger on initial load (edit mode)
        CT._onSelectionChanged();
    };

    CT._onSelectionChanged = function () {
        var videoItems = CT._getSelectedVideoItems();

        if (videoItems.length === 0) {
            CT._hidePanel();
            return;
        }

        CT._showPanel();

        // Use the first video's thumbnails
        var $firstVideo = $(videoItems[0]);
        var idSecure    = $firstVideo.data('id-secure') || '';
        var thumbsData  = ($firstVideo.data('thumbnails') || '').trim();

        // Clear previous grid
        $('#ct-thumbs-grid').empty();
        CT._stopAllPolls();

        if (thumbsData) {
            // Already generated — render immediately
            var thumbUrls = thumbsData.split(',').filter(Boolean);
            CT._renderThumbs(thumbUrls);
        } else if (idSecure) {
            // Not ready yet — poll
            CT._pollThumbnails(idSecure);
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
        CT._stopAllPolls();
    };

    // ── Thumbnail fetching + polling ──────────────────────────────────────────

    CT._pollThumbnails = function (idSecure) {
        var tries = 0;

        $('#ct-thumbs-loading').removeClass('d-none');

        function poll() {
            if (tries >= pollMaxTries) {
                $('#ct-thumbs-loading').addClass('d-none').text('');
                return;
            }

            $.post(filesApiUrl + 'get_thumbnails', {
                id_secure : idSecure,
                team_id   : VARIABLES.team_id || 0,
                _token    : VARIABLES.csrf
            })
            .done(function (res) {
                if (res.status === 1 && res.thumbnails && res.thumbnails.length > 0) {
                    $('#ct-thumbs-loading').addClass('d-none');
                    CT._renderThumbs(res.thumbnails);
                    // Also update the DOM element so future selections are instant
                    var $item = $('.file-selected-media .items .file-item[data-id-secure="' + idSecure + '"]');
                    $item.data('thumbnails', res.thumbnails.join(','));
                } else if (res.status === 2) {
                    // Still pending
                    tries++;
                    pollTimers[idSecure] = setTimeout(poll, 3000);
                } else {
                    $('#ct-thumbs-loading').addClass('d-none');
                }
            })
            .fail(function () {
                tries++;
                pollTimers[idSecure] = setTimeout(poll, 5000);
            });
        }

        poll();
    };

    CT._stopAllPolls = function () {
        $.each(pollTimers, function (key, timer) {
            clearTimeout(timer);
        });
        pollTimers = {};
        $('#ct-thumbs-loading').addClass('d-none');
    };

    // ── Thumbnail grid rendering ──────────────────────────────────────────────

    CT._renderThumbs = function (urls) {
        var $grid = $('#ct-thumbs-grid').empty();

        $.each(urls, function (i, url) {
            if (!url) return;
            var $thumb = $('<div>', {
                class : 'ct-thumb-item border b-r-6 pointer overflow-hidden',
                style : 'width:72px; height:46px; flex-shrink:0; cursor:pointer; position:relative;',
                'data-url'   : url,
                'data-index' : i      // frame index (0–9) for TikTok cover timestamp
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
            var index = $(this).data('index');          // 0–9 frame index
            CT._selectThumbnail(url, index);

            // Highlight selected
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

    /**
     * Open the mini media popup in "single select / image only" mode.
     * When an image is checked and user confirms, set as thumbnail.
     */
    CT._openMediaPicker = function () {
        // Build a temporary popup UI using the existing mini_list endpoint
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

        // Load image files from mini_list
        $.post((VARIABLES.url || '') + 'app/files/mini_list', {
            file_type : 'image',
            team_id   : VARIABLES.team_id || 0,
            page      : 0,
            _token    : VARIABLES.csrf
        }, function (res) {
            if (res.status && res.data) {
                var $parsed = $(res.data);
                // Make file items selectable (single select)
                $parsed.find('.file-item').each(function () {
                    var $item = $(this);
                    var fileUrl = $item.data('file');
                    if (!fileUrl) return;

                    // Remove drag/checkbox behaviours, make clickable
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
                CT._selectThumbnail(selectedPickerUrl, -1);  // -1 = from media library
                $('.ct-thumb-item').css('outline', '');
            }
            var modal = bootstrap.Modal.getInstance($modal[0]);
            if (modal) modal.hide();
        });

        $modal.on('hidden.bs.modal', function () {
            $modal.remove();
        });

        var bsModal = new bootstrap.Modal($modal[0]);
        bsModal.show();
    };

    // ── Select / deselect a thumbnail ────────────────────────────────────────

    CT._selectThumbnail = function (url, index) {
        $('#ct-hidden-input').val(url);
        // Store frame index; default to -1 (media library / no frame mapping)
        $('#ct-hidden-index').val(typeof index !== 'undefined' ? index : -1);
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

// Auto-init when DOM is ready
$(function () {
    // Small delay to ensure composer DOM is fully rendered
    setTimeout(function () {
        ComposerThumbnail.init();
    }, 300);
});
