<div class="border border-gray-400 rounded bg-white">
    
    <div class="d-flex pf-13">
        
        <div class="d-flex align-items-center gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex align-items-center justify-content-start">
                <div class="flex-grow-1 me-2 text-truncate">
                    <a href="javascript:void(0);" class="text-gray-800 text-hover-primary fs-14 fw-bold cpv-name">{{ __("Your name") }}</a>
                    <span class="text-gray-400 d-block fs-12">{{ date("M j") }}</span>
                </div>
            </div>
        </div>

    </div>

    <div class="mb-0">
        <div class="cpv-text fs-14 px-3 mb-3 text-truncate-5 mb-3"></div>

        <div class="cpv-media">
            <div class="cpv-img w-100 cpv-gmb-img d-none"></div>
            <div class="cpv-gmb-img-view w-100">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
            </div>
        </div>

        <div class="cpv-link d-none m-3 border b-r-10">
            <div class="cpv-link-img img-wrap-16x9 w-100 ratio ratio-4x3 border-end btl-r-10 btr-r-10">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
            </div>
            <div class="d-flex flex-column justify-content-center w-100 fs-12 pf-13">
                <div class="cpv-default">
                    <div class="h-12 bg-gray-300 mb-2"></div>
                    <div class="h-12 bg-gray-300 mb-2"></div>
                    <div class="h-12 bg-gray-300 mb-1"></div>
                    <div class="h-12 bg-gray-300 mb-1 wp-50"></div>
                </div>
                <div class="cpv-link-title fw-6 text-truncate-1"></div>
                <div class="cpv-link-desc text-gray-700 text-truncate-2"></div>
                <div class="cpv-link-web fs-12 fw-3 text-truncate-1"></div>
            </div>
        </div>

    </div>

    <div class="px-3 py-2 d-flex justify-content-end text-gray-600 align-items-center fs-16">
        <div class="d-flex justify-content-end gap-16">
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px">
                    <i class="fa-solid fa-share-nodes"></i>
                </div>
            </div>
        </div>
    </div>

</div>
<script>

function gmb_renderItem(el) {
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
function gmb_renderGrid(elements) {
    if (elements.length === 0) return '';
    var id = 'gmb-car-' + Math.random().toString(36).substr(2, 6);
    var items = elements.map(function(el, i) {
        return '<div class="carousel-item ' + (i===0?'active':'') + '">' + gmb_renderItem(el) + '</div>';
    }).join('');
    var ctrls = elements.length > 1
        ? '<button class="carousel-control-prev" type="button" data-bs-target="#'+id+'" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>'
          + '<button class="carousel-control-next" type="button" data-bs-target="#'+id+'" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>'
        : '';
    return '<div id="'+id+'" class="carousel slide" data-bs-ride="carousel"><div class="carousel-inner">'+items+'</div>'+ctrls+'</div>';
}

document.querySelectorAll('.cpv-gmb-img').forEach(function(container) {
    var pane = container.closest('.tab-pane') || container.parentElement;
    var view = pane ? pane.querySelector('.cpv-gmb-img-view') : document.querySelector('.cpv-gmb-img-view');
    if (!view) return;

    function gmb_getElements() {
        // Primary: cpv-img staging (written by onMediaItemsChange)
        var els = Array.from(container.querySelectorAll('img[data-file]'))
                      .filter(function(el) { return el.getAttribute('data-file'); });
        // Fallback: read directly from preview-list-medias (calendar preview modal on first load)
        if (els.length === 0) {
            var staging = pane ? pane.querySelector('.preview-list-medias') : null;
            if (staging) {
                els = Array.from(staging.querySelectorAll('img[data-file]'))
                          .filter(function(el) { return el.getAttribute('data-file'); });
            }
        }
        return els;
    }

    function gmb_update() {
        var els = gmb_getElements();
        if (els.length === 0) return;
        view.innerHTML = gmb_renderGrid(els);
    }

    // Watch the cpv-img staging div (written by onMediaItemsChange)
    new MutationObserver(gmb_update).observe(container, {
        childList: true, subtree: false,
        attributes: true, attributeFilter: ['src', 'data-type', 'data-file', 'data-poster']
    });

    // Also watch preview-list-medias directly for attribute changes
    // (covers the case where server sets data-poster after page load)
    var stagingList = pane ? pane.querySelector('.preview-list-medias') : null;
    if (stagingList) {
        new MutationObserver(gmb_update).observe(stagingList, {
            childList: true, subtree: true,
            attributes: true, attributeFilter: ['data-file', 'data-type', 'data-poster', 'src']
        });
    }

    gmb_update();
});
</script>
