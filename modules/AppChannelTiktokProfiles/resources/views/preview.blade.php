<div class="tiktok-card border rounded bg-black text-white overflow-hidden position-relative">
    
    <!-- Video -->
    <div class="position-relative">
        <div class="tiktok-video-wrapper position-relative h-550">

            <!-- Play button center -->
            <button class="position-absolute top-50 start-50 translate-middle btn btn-light rounded-circle shadow size-60">
                <i class="fas fa-play fs-24"></i>
            </button>
        </div>
    </div>

    <!-- User info -->
    <div class="d-flex align-items-center p-3">
        <img src="{{ theme_public_asset('img/default.png') }}" class="rounded-circle me-2 cpv-avatar" width="32" height="32">
        <div>
            <div class="fw-bold fs-14 cpv-name">{{ __("Your name") }}</div>
            <div class="fs-12 text-white">{{ date("M j") }}</div>
        </div>
    </div>

    <!-- Caption -->
    <div class="px-3 px-1">
        <div class="cpv-text fs-14 mb-3 text-truncate-5"></div>
        <div class="fs-12 text-gray-600 mb-2">🎵 {{ __('Original sound - TikTok') }}</div>
    </div>

    <!-- Actions -->
    <div class="position-absolute top-50 end-0 translate-middle-y me-2">
        <div class="text-center mb-3">
            <div class="bg-dark bg-opacity-50 rounded-circle p-2 mb-1 size-40 "><i class="fal fa-heart fs-18"></i></div>
            <div class="fs-12">120</div>
        </div>
        <div class="text-center mb-3">
            <div class="bg-dark bg-opacity-50 rounded-circle p-2 mb-1 size-40 "><i class="fal fa-comment-dots fs-18"></i></div>
            <div class="fs-12">45</div>
        </div>
        <div class="text-center">
            <div class="bg-dark bg-opacity-50 rounded-circle p-2 mb-1 size-40 "><i class="fal fa-share fs-18"></i></div>
            <div class="fs-12">10</div>
        </div>
    </div>
</div>
<script>

function tiktok_renderItem(el) {
    var type   = el.getAttribute('data-type')   || 'image';
    var file   = el.getAttribute('data-file')   || el.getAttribute('src') || '';
    var poster = el.getAttribute('data-poster') || '';

    if (type === 'video') {
        if (poster) {
            return '<div class="img-wrap position-relative">'
                + '<img src="' + poster + '" style="width:100%;height:100%;object-fit:cover;display:block;">'
                + '<div class="cpv-play-overlay" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.35);color:#fff;font-size:1.6rem;pointer-events:none;">'
                + '<i class="fa-solid fa-circle-play"></i></div>'
                + '</div>';
        } else {
            return '<div class="img-wrap position-relative" style="background:#1a1a1a;">'
                + '<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#999;gap:8px;">'
                + '<i class="fa-solid fa-film" style="font-size:2rem;"></i>'
                + '<span style="font-size:11px;">Video</span>'
                + '</div></div>';
        }
    } else {
        return '<div class="img-wrap"><img src="' + file + '" style="width:100%;height:100%;object-fit:cover;display:block;"></div>';
    }
}
(function() {
    document.querySelectorAll('.tiktok-video-wrapper, .cpv-tiktok-img').forEach(function(wrapper) {
        var pane = wrapper.closest('.tab-pane') || document.body;

        function tiktok_render() {
            // Try cpv-staging first, fall back to preview-list-medias directly
            var staging = pane.querySelector('.cpv-tiktok-img img[data-file]')
                       || pane.querySelector('.preview-list-medias img[data-file]')
                       || document.querySelector('.preview-list-medias img[data-file]');
            if (!staging) return;

            var type   = staging.getAttribute('data-type')   || 'image';
            var file   = staging.getAttribute('data-file')   || '';
            var poster = staging.getAttribute('data-poster') || '';

            var view = pane.querySelector('.cpv-tiktok-img-view') || wrapper;
            var old  = view.querySelector('video, img.tiktok-media, div.tiktok-placeholder');
            if (old) old.remove();
            if (!file) return;

            var el = document.createElement('img');
            if (type === 'video' && poster) {
                el.src = poster;
            } else if (type !== 'video') {
                el.src = file;
            } else {
                var ph = document.createElement('div');
                ph.className = 'tiktok-placeholder';
                ph.style.cssText = 'position:absolute;inset:0;background:#111;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#999;gap:8px;';
                ph.innerHTML = '<i class="fa-solid fa-film" style="font-size:2.5rem;"></i><span style="font-size:12px;">Video</span>';
                view.insertBefore(ph, view.firstChild);
                return;
            }
            el.className = 'tiktok-media';
            el.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:inherit;';
            view.insertBefore(el, view.firstChild);

            // Play overlay for video
            if (type === 'video' && !view.querySelector('.tiktok-play-ov')) {
                var ov = document.createElement('div');
                ov.className = 'tiktok-play-ov';
                ov.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:2.5rem;pointer-events:none;';
                ov.innerHTML = '<i class="fa-solid fa-circle-play" style="filter:drop-shadow(0 2px 6px #0008)"></i>';
                view.appendChild(ov);
            }
        }

        // Watch both the cpv-img staging div AND preview-list-medias for changes
        ['.cpv-tiktok-img', '.preview-list-medias'].forEach(function(sel) {
            var el = pane.querySelector(sel) || document.querySelector(sel);
            if (el) new MutationObserver(tiktok_render).observe(el, {
                childList: true, subtree: true,
                attributes: true, attributeFilter: ['data-file','data-type','data-poster','src']
            });
        });

        tiktok_render();
    });
})();
</script>
