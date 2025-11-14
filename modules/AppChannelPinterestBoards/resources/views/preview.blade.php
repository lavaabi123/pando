<div class="border border-gray-400 rounded bg-white">
    
    <div class="d-flex pf-13">
        
        <div class="d-flex align-items-center gap-8">
            <div class="size-40 size-child">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="align-self-center rounded-circle border cpv-avatar" alt="">
            </div>
            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                <div class="flex-grow-1 me-2 text-truncate">
                    <a href="javascript:void(0);" class="text-gray-800 text-hover-primary fs-14 fw-bold cpv-name">{{ __("Your name") }}</a>
                </div>
            </div>
        </div>

    </div>

    <div class="mb-0">
        

        <div class="cpv-media">
            <div class="cpv-img w-100 cpv-pinterest-img d-none"></div>
            <div class="cpv-pinterest-img-view w-100">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
            </div>
        </div>

        <div class="cpv-link d-flex justify-content-start w-100 d-none border-top">
            <div class="cpv-link-img img-wrap w-100 size-120 size-child border-end">
                <img src="{{ theme_public_asset( "img/default.png" ) }}" class="w-100">
            </div>
            <div class="d-flex flex-column justify-content-center w-100 bg-gray-100 fs-12 pf-13">
                <div class="cpv-default">
                    <div class="h-12 bg-gray-300 mb-2"></div>
                    <div class="h-12 bg-gray-300 mb-2"></div>
                    <div class="h-12 bg-gray-300 mb-1"></div>
                    <div class="h-12 bg-gray-300 mb-1 wp-50"></div>
                </div>
                <div class="cpv-link-web text-uppercase fs-10 fw-3 text-truncate-1">
                    
                </div>
                <div class="cpv-link-title fw-6 text-truncate-1">
                    
                </div>
                <div class="cpv-link-desc text-gray-700 text-truncate-2">
                    
                </div>
            </div>
        </div>
    </div>

    <div class="border-top px-3 py-2">
        <div class="d-flex mb-2">
			<div class="d-flex flex-stack">
				<div class="symbol symbol-45px me-4">
					<i class="far fa-heart fs-20"></i>
				</div>
			</div>
			<div class="d-flex flex-stack">
				<div class="symbol symbol-45px me-2">
					<i class="far fa-comment fs-20"></i>
				</div>
			</div>
		</div>
		<div class="d-flex align-items-center flex-row-fluid flex-wrap">
			<div class="flex-grow-1 me-2 text-over-all">
				<div class="fs-12">
					<a href="javascript:void(0);" class="text-gray-800 text-hover-primary fw-bold me-2">{{ __("Your name") }}</a>
					
					<div class="cpv-text fs-14 mb-3 text-truncate-5"></div>
				</div>
				<span class="text-muted fw-semibold d-block fs-10 text-uppercase mt-2">{{ date("d F, Y h:i a") }}</span>
			</div>
		</div>
    </div>

</div>

<script>
function pinterest_renderMediaGrid(elements) {
    var pinterest_total = elements.length;
    var pinterest_visible = elements.slice(0, 4);
    var pinterest_moreCount = pinterest_total - 4;

    let pinterest_html = '';

    if (pinterest_total === 1) {
        pinterest_html += `
            <div class="cpv-grid" style="grid-template-columns: 1fr;">
                <div class="img-wrap">${elements[0].outerHTML}</div>
            </div>
        `;
    } else if (pinterest_total === 2) {
        pinterest_html += `
            <div class="cpv-grid" style="grid-template-columns: repeat(2, 1fr);">
                ${pinterest_visible.map(el => `<div class="img-wrap">${el.outerHTML}</div>`).join('')}
            </div>
        `;
    } else if (pinterest_total === 3) {
        pinterest_html += `
            <div class="cpv-grid" style="grid-template-columns: 2fr 1fr; grid-template-rows: repeat(2, 1fr);">
                <div class="img-wrap" style="grid-row: span 2;">${elements[0].outerHTML}</div>
                <div class="img-wrap">${elements[1].outerHTML}</div>
                <div class="img-wrap">${elements[2].outerHTML}</div>
            </div>
        `;
    } else {
        pinterest_html += `<div class="cpv-grid" style="grid-template-columns: repeat(2, 1fr);">`;
        pinterest_visible.forEach((el, idx) => {
            var pinterest_isLast = idx === 3 && pinterest_moreCount > 0;
            var pinterest_overlay = pinterest_isLast ? `<div class="overlay">+${pinterest_moreCount}</div>` : '';
            pinterest_html += `<div class="img-wrap">${el.outerHTML}${pinterest_overlay}</div>`;
        });
        pinterest_html += `</div>`;
    }

    return pinterest_html;
}

function pinterest_onMediaItemsChange() {
    var pinterest_elements = document.querySelectorAll('.cpv-pinterest-img > img, .cpv-pinterest-img > div');
    if (pinterest_elements.length > 0) {
        var pinterest_mediaList = Array.from(pinterest_elements).filter(el =>
            el.tagName.toLowerCase() === 'img' || el.tagName.toLowerCase() === 'div'
        );

        var pinterest_rendered = pinterest_renderMediaGrid(pinterest_mediaList);
        document.querySelector('.cpv-pinterest-img-view').innerHTML = pinterest_rendered;
    }
}

// Setup MutationObserver
var pinterest_container = document.querySelector('.cpv-pinterest-img');
if (pinterest_container) {
    var pinterest_observer = new MutationObserver(pinterest_onMediaItemsChange);
    pinterest_observer.observe(pinterest_container, {
        childList: true,
        subtree: false,
        attributes: true,
        attributeFilter: ['src'],
    });

    pinterest_onMediaItemsChange();
}
</script>
