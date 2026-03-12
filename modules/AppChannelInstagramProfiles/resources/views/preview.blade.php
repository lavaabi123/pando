<div class="instagram-preview instagram-post border border-gray-400 rounded bg-white">
    
    <div class="d-flex pf-13">
        
        <div class="d-flex align-items-center gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                <div class="flex-grow-1 me-2 text-truncate">
                    <a href="javascript:void(0);" class="text-gray-800 text-hover-primary fs-14 fw-bold cpv-name">{{ __("Your name") }}</a>
                    <span class="text-gray-400 d-block fs-12">{{ date("M j") }}</span>
                </div>
            </div>
        </div>

    </div>

    <div class="mb-0">
        <div class="cpv-media mb-3">
            <div class="cpv-img wp-100 cpv-instagram-img d-none"></div>
            <div class="cpv-instagram-img-view wp-100">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="wp-100">
            </div>
        </div>

        <div class="cpv-text fs-14 px-3 mb-3 text-truncate-5"></div>
    </div>

    <div class="px-3 py-2 d-flex justify-content-between text-gray-800 align-items-center fs-22">
        <div class="d-flex justify-content-between gap-16">
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px me-2">
                    <i class="fa-regular fa-comment"></i>
                </div>
            </div>
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px me-2">
                    <i class="fa-regular fa-heart"></i>
                </div>
            </div>
            <div class="d-flex flex-stack">
                <div class="symbol symbol-45px me-2">
                    <i class="fa-regular fa-share"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="instagram-preview instagram-stories d-none">
    <div class="cpv-media mb-3 bg-gray-900 h-800">
        <div class="cpv-instagram-stories-img-view img-wrap wp-100 hp-100">
            <img src="{{ theme_public_asset( "img/default.png" ) }}" class="wp-100">
        </div>
    </div>
</div>
<script>

function ig_renderItem(el) {
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
function ig_renderGrid(elements) {
    if (elements.length === 0) return '';
    var id = 'ig-car-' + Math.random().toString(36).substr(2, 6);
    var items = elements.map(function(el, i) {
        return '<div class="carousel-item ' + (i===0?'active':'') + '">' + ig_renderItem(el) + '</div>';
    }).join('');
    var ctrls = elements.length > 1
        ? '<button class="carousel-control-prev" type="button" data-bs-target="#'+id+'" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>'
          + '<button class="carousel-control-next" type="button" data-bs-target="#'+id+'" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>'
        : '';
    return '<div id="'+id+'" class="carousel slide" data-bs-ride="carousel"><div class="carousel-inner">'+items+'</div>'+ctrls+'</div>';
}

document.querySelectorAll('.cpv-instagram-img').forEach(function(container) {
    var pane = container.closest('.tab-pane') || container.parentElement;
    var view = pane ? pane.querySelector('.cpv-instagram-img-view') : document.querySelector('.cpv-instagram-img-view');
    if (!view) return;

    function ig_getElements() {
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

    function ig_update() {
        var els = ig_getElements();
        if (els.length === 0) return;
        view.innerHTML = ig_renderGrid(els);
    }

    // Watch the cpv-img staging div (written by onMediaItemsChange)
    new MutationObserver(ig_update).observe(container, {
        childList: true, subtree: false,
        attributes: true, attributeFilter: ['src', 'data-type', 'data-file', 'data-poster']
    });

    // Also watch preview-list-medias directly for attribute changes
    // (covers the case where server sets data-poster after page load)
    var stagingList = pane ? pane.querySelector('.preview-list-medias') : null;
    if (stagingList) {
        new MutationObserver(ig_update).observe(stagingList, {
            childList: true, subtree: true,
            attributes: true, attributeFilter: ['data-file', 'data-type', 'data-poster', 'src']
        });
    }

    ig_update();
});
</script>
