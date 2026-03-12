{{-- YouTube Channel Preview --}}
@php
    $ytProfile   = null;
    $profileInput = null;
@endphp

<div class="border border-gray-400 rounded bg-white overflow-hidden">

    {{-- Channel header --}}
    <div class="d-flex align-items-center gap-8 px-3 py-2">
        <div class="size-40 size-child rounded-circle overflow-hidden bg-gray-200 flex-shrink-0 cpv-yt-avatar">
        </div>
        <div class="flex-grow-1">
            <div class="fw-6 fs-14 cpv-yt-name text-truncate-1" style="max-width:200px;"></div>
            <div class="text-gray-500 fs-11">YouTube &bull; <span class="cpv-yt-handle"></span></div>
        </div>
        <div>
            <span class="badge bg-danger text-white fs-11 px-3 py-1 rounded-pill">Subscribe</span>
        </div>
    </div>

    {{-- Video thumbnail area --}}
    <div class="cpv-media" style="background:#000;position:relative;">
        {{-- Hidden staging container (filled by onMediaItemsChange) --}}
        <div class="cpv-img cpv-youtube-img d-none"></div>
        {{-- Rendered output --}}
        <div class="cpv-youtube-img-view w-100" style="min-height:180px;background:#111;">
        </div>
    </div>

    {{-- Video info --}}
    <div class="px-3 pt-2 pb-1">
        <div class="cpv-text fw-6 fs-13 text-truncate-2 mb-1" style="line-height:1.3;"></div>
        <div class="text-gray-500 fs-11 mb-1">
            <span>123K views</span> &bull; <span>Just now</span>
        </div>
    </div>

    {{-- Action bar --}}
    <div class="border-top px-3 py-2 d-flex justify-content-around text-gray-500 fs-12">
        <span><i class="fal fa-thumbs-up me-1"></i>Like</span>
        <span><i class="fal fa-thumbs-down me-1"></i>Dislike</span>
        <span><i class="fal fa-share me-1"></i>Share</span>
        <span><i class="fal fa-download me-1"></i>Save</span>
    </div>
</div>

<script>
(function() {
    // Render a single media element (image or video poster)
    function yt_renderItem(el) {
        var type   = el.getAttribute('data-type')   || 'image';
        var file   = el.getAttribute('data-file')   || '';
        var poster = el.getAttribute('data-poster') || '';

        if (type === 'video') {
            if (poster) {
                return '<div style="position:relative;width:100%;padding-top:56.25%;background:#000;">'
                    + '<img src="' + poster + '" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">'
                    + '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">'
                    + '<div style="width:56px;height:56px;background:rgba(255,0,0,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center;">'
                    + '<i class="fa-solid fa-play" style="color:#fff;font-size:1.3rem;margin-left:4px;"></i>'
                    + '</div></div></div>';
            } else {
                return '<div style="position:relative;width:100%;padding-top:56.25%;background:#111;">'
                    + '<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#555;gap:8px;">'
                    + '<i class="fa-solid fa-film" style="font-size:2.5rem;"></i>'
                    + '<span style="font-size:12px;">Video</span>'
                    + '</div></div>';
            }
        } else {
            return '<div style="position:relative;width:100%;padding-top:56.25%;background:#000;">'
                + '<img src="' + file + '" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">'
                + '</div>';
        }
    }

    // YouTube only shows the first media (videos/images)
    function yt_renderGrid(elements) {
        return elements.length > 0 ? yt_renderItem(elements[0]) : '';
    }

    // Attach observer to every .cpv-youtube-img in the same tab pane
    document.querySelectorAll('.cpv-youtube-img').forEach(function(container) {
        var pane = container.closest('.tab-pane') || container.parentElement;
        var view = pane ? pane.querySelector('.cpv-youtube-img-view') : null;
        if (!view) return;

        // Populate profile header from hidden input
        var profileInput = pane.querySelector('.preview-profile');
        if (profileInput) {
            var avatar   = profileInput.getAttribute('data-avatar') || '';
            var name     = profileInput.getAttribute('data-name') || '';
            var username = profileInput.getAttribute('data-username') || '';
            var avatarEl = pane.querySelector('.cpv-yt-avatar');
            var nameEl   = pane.querySelector('.cpv-yt-name');
            var handleEl = pane.querySelector('.cpv-yt-handle');
            if (avatarEl && avatar) avatarEl.innerHTML = '<img src="' + avatar + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
            if (nameEl)   nameEl.textContent   = name;
            if (handleEl) handleEl.textContent = username ? '@' + username : '';
        }

        function yt_update() {
            var els = Array.from(container.querySelectorAll('img[data-file]'))
                          .filter(function(el) { return el.getAttribute('data-file'); });
            if (els.length === 0) return;
            view.innerHTML = yt_renderGrid(els);
        }

        new MutationObserver(yt_update).observe(container, {
            childList: true, subtree: false,
            attributes: true, attributeFilter: ['src', 'data-type', 'data-file', 'data-poster']
        });
        yt_update();
    });
})();
</script>
