<div class="border border-gray-400 rounded bg-white">
    
    <div class="pf-13">
        
        <div class="d-flex gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex flex-column wp-100">
                <div class="d-flex align-items-center gap-8">
                    <span class="text-gray-900 text-hover-primary fs-14 fw-bold cpv-name"></span>
                    <span class="text-gray-600 fw-5 d-block fs-12">@<span class="cpv-username">{{ __("username") }}</span></span>
                    <span class="text-gray-600 fs-10 d-flex align-items-center m-t--7"><i class="fa-solid fa-period"></i></span>
                    <span class="text-gray-600 fw-semibold d-block fs-12">{{ date("M j") }}</span>
                </div>
                <div class="mt-2 mb-2">
                    <div class="cpv-text fs-14 mb-3 text-truncate-5"></div>

                    <div class="cpv-media">
                        <div class="cpv-img w-100 cpv-x-img d-none"></div>
                        <div class="cpv-x-img-view w-100">
                            <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
                        </div>
                    </div>

                    <div class="cpv-link d-flex justify-content-start w-100 d-none">
                        <div class="cpv-link-img img-wrap w-100 size-120 size-child border-end btl-r-10 bbl-r-10 border-start border-top border-bottom">
                            <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
                        </div>
                        <div class="d-flex flex-column justify-content-center w-100 bg-gray-100 fs-12 pf-13 btr-r-10 bbr-r-10 border-end border-top border-bottom">
                            <div class="cpv-default">
                                <div class="h-12 bg-gray-300 mb-2"></div>
                                <div class="h-12 bg-gray-300 mb-2"></div>
                                <div class="h-12 bg-gray-300 mb-1"></div>
                                <div class="h-12 bg-gray-300 mb-1 wp-50"></div>
                            </div>
                            <div class="cpv-link-web fs-10 fw-3 text-truncate-1">
                                
                            </div>
                            <div class="cpv-link-title fw-6 text-truncate-1">
                                
                            </div>
                            <div class="cpv-link-desc text-gray-700 text-truncate-2">
                                
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-start gap-64 text-gray-600">
                    <div class="d-flex flex-stack">
                        <div class="symbol symbol-45px me-2">
                            <i class="fal fa-comment"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-stack">
                        <div class="symbol symbol-45px me-2">
                            <i class="fal fa-retweet"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-stack">
                        <div class="symbol symbol-45px me-2">
                            <i class="fal fa-heart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

function xprofile_renderItem(el) {
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
function xprofile_renderGrid(elements) {
    var total   = elements.length;
    var visible = elements.slice(0, 4);
    var more    = total - 4;
    if (total === 1) {
        return '<div class="cpv-grid" style="grid-template-columns:1fr;">' + xprofile_renderItem(elements[0]) + '</div>';
    } else if (total === 2) {
        return '<div class="cpv-grid" style="grid-template-columns:repeat(2,1fr);">'
             + visible.map(function(el){ return xprofile_renderItem(el); }).join('') + '</div>';
    } else if (total === 3) {
        return '<div class="cpv-grid" style="grid-template-columns:2fr 1fr;grid-template-rows:repeat(2,1fr);">'
             + '<div class="img-wrap" style="grid-row:span 2;">' + xprofile_renderItem(elements[0]) + '</div>'
             + xprofile_renderItem(elements[1]) + xprofile_renderItem(elements[2]) + '</div>';
    } else {
        var html = '<div class="cpv-grid" style="grid-template-columns:repeat(2,1fr);">';
        visible.forEach(function(el, idx) {
            var item = xprofile_renderItem(el);
            if (idx === 3 && more > 0) {
                item = item.replace('</div>', '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.55);color:#fff;font-size:1.4rem;font-weight:700;">+'+(more+1)+'</div></div>');
            }
            html += item;
        });
        return html + '</div>';
    }
}

document.querySelectorAll('.cpv-x-img').forEach(function(container) {
    var pane = container.closest('.tab-pane') || container.parentElement;
    var view = pane ? pane.querySelector('.cpv-x-img-view') : document.querySelector('.cpv-x-img-view');
    if (!view) return;

    function xprofile_getElements() {
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

    function xprofile_update() {
        var els = xprofile_getElements();
        if (els.length === 0) return;
        view.innerHTML = xprofile_renderGrid(els);
    }

    // Watch the cpv-img staging div (written by onMediaItemsChange)
    new MutationObserver(xprofile_update).observe(container, {
        childList: true, subtree: false,
        attributes: true, attributeFilter: ['src', 'data-type', 'data-file', 'data-poster']
    });

    // Also watch preview-list-medias directly for attribute changes
    // (covers the case where server sets data-poster after page load)
    var stagingList = pane ? pane.querySelector('.preview-list-medias') : null;
    if (stagingList) {
        new MutationObserver(xprofile_update).observe(stagingList, {
            childList: true, subtree: true,
            attributes: true, attributeFilter: ['data-file', 'data-type', 'data-poster', 'src']
        });
    }

    xprofile_update();
});
</script>
