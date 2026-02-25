/* =========================================================
   custom.js — theme, dark mode, and Select2 brand picker
   ========================================================= */

/* ----------------------------------------------
   0) GLOBAL: Guard clicks on action buttons so
      Select2 doesn't treat them as selections.
      + also mark that the last click was an action.
---------------------------------------------- */
window.__s2ActionClick = false;
(function installSelect2ActionGuards(){
  const markAndStop = (e) => {
    const t = e.target;
    if (t && t.closest && t.closest('.js-brand-fav, .js-brand-edit, .js-brand-del')) {
      window.__s2ActionClick = true;      // mark action click
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      return false;
    }
  };
  // Use capture phase so we beat Select2's own handlers
  ['pointerdown','pointerup','mousedown','mouseup','touchstart','touchend','click','keydown']
    .forEach(evt => document.addEventListener(evt, markAndStop, true));
})();

/* ----------------------------------------------
   1) THEME COLORS & DARK MODE
---------------------------------------------- */
(function(){
  const root   = document.documentElement;
  const routes = window.routes || {};
  const token  = window.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.content || '');

  function cleanColorClasses() {
    const toRemove = Array.from(root.classList).filter(c => c.startsWith('primary-') || c.startsWith('secondary-'));
    if (toRemove.length) root.classList.remove(...toRemove);
  }
  const colorToClass = (hex) => (hex || '').replace('#','');

  // Color pickers
  $('.colorPicker').on('input', function() {
    const pColor = $('#colorPicker').val();
    const sColor = $('#colorPicker_sec').val();

    cleanColorClasses();
    root.classList.add('primary-' + colorToClass(pColor));
    root.classList.add('secondary-' + colorToClass(sColor));

    root.style.setProperty('--d-primary', pColor);
    root.style.setProperty('--d-secondary', sColor);
    root.style.setProperty('--sp-primary', pColor);
    root.style.setProperty('--sp-secondary', sColor);

    if (routes.setColor) {
      $.ajax({
        url: routes.setColor,
        type: 'POST',
        data: { pColor, sColor, _token: token },
        dataType: 'json'
      });
    }
  });

  // Dark theme toggle
  const themeChk = document.getElementById('theme_color_dark');
  function applyTheme(dark) {
    if (dark) {
      document.documentElement.setAttribute('data-theme','dark');
      document.body.classList.remove('light');
      document.body.classList.add('dark');
      loadThemeCss();
    } else {
      document.documentElement.setAttribute('data-theme','light');
      document.body.classList.remove('dark');
      document.body.classList.add('light');
      const oldLink = document.getElementById('theme-css');
      if (oldLink) oldLink.remove();
    }
  }
  if (themeChk) {
    themeChk.addEventListener('click', function () {
      const checked = this.checked;
      const theme_color = checked ? 'dark' : 'light';
      if (routes.saveTheme) {
        fetch(routes.saveTheme, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
          body: JSON.stringify({ theme_color })
        }).then(() => applyTheme(checked));
      } else {
        applyTheme(checked);
      }
    });
    themeChk.addEventListener('change', function () {
      if (this.checked) loadThemeCss();
      else {
        const oldLink = document.getElementById('theme-css');
        if (oldLink) oldLink.remove();
      }
    });
  }

  // Reset to defaults
  $('.colorPicker_reset').on('click', function() {
    const pColor = '#64C76C';
    const sColor = '#fd8107';
    $('#colorPicker').val(pColor);
    $('#colorPicker_sec').val(sColor);

    cleanColorClasses();
    root.classList.add('primary-' + colorToClass(pColor));
    root.classList.add('secondary-' + colorToClass(sColor));

    root.style.setProperty('--d-primary', pColor);
    root.style.setProperty('--d-secondary', sColor);
    root.style.setProperty('--sp-primary', pColor);
    root.style.setProperty('--sp-secondary', sColor);

    if (routes.setColor) {
      $.ajax({
        url: routes.setColor,
        type: 'POST',
        data: { pColor, sColor, _token: token },
        dataType: 'json'
      });
    }
  });

  // Load theme.css (dark)
  window.loadThemeCss = function loadThemeCss() {
    const oldLink = document.getElementById('theme-css');
    if (oldLink) oldLink.remove();
    const link = document.createElement('link');
    link.rel  = 'stylesheet';
    link.href = window.themeCssUrl || '/css/theme.css';
    link.id   = 'theme-css';
    document.head.appendChild(link);
  };

  // If page starts in dark mode
  if (document.documentElement.getAttribute('data-theme') === 'dark') {
    loadThemeCss();
  }
})();

// Custom Brand Dropdown Handler
document.addEventListener("DOMContentLoaded", () => {
    const dropdownToggle = document.getElementById('brandDropdownToggle');
    const dropdownMenu = document.getElementById('brandDropdownMenu');
    const brandList = document.getElementById('brandList');
    const searchInput = document.getElementById('brandSearchInput');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const addBrandBtn = document.getElementById('addBrandBtn');
    
    if (!dropdownToggle || !dropdownMenu) return;

    let currentFilter = 'alphabetic'; // Changed default to 'alphabetic'
    
    // Toggle dropdown
    dropdownToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdownMenu.classList.toggle('active');
        if (dropdownMenu.classList.contains('active')) {
            searchInput.focus();
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('active');
        }
    });
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            filterAndSortBrands(searchTerm, currentFilter);
        });
    }
    
    // Filter buttons
    filterButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentFilter = btn.dataset.filter;
            filterAndSortBrands(searchInput.value.toLowerCase(), currentFilter);
        });
    });
    
    // Filter and sort brands function
    function filterAndSortBrands(searchTerm, filter) {
        const brandItems = Array.from(document.querySelectorAll('.brand-item'));
        
        // First, filter based on search term
        const visibleItems = brandItems.filter(item => {
            const brandName = item.dataset.brandName;
            const matchesSearch = !searchTerm || brandName.includes(searchTerm);
            
            // Show/hide based on search
            item.style.display = matchesSearch ? 'flex' : 'none';
            
            return matchesSearch;
        });
        
        // Then, sort based on filter
        if (filter === 'alphabetic') {
            // Sort alphabetically (already sorted from backend, but ensure order)
            visibleItems.sort((a, b) => {
                const nameA = a.dataset.brandName;
                const nameB = b.dataset.brandName;
                return nameA.localeCompare(nameB);
            });
        } else if (filter === 'recent') {
            // Sort by last_used_at (most recent first), then alphabetically
            visibleItems.sort((a, b) => {
                const lastUsedA = parseInt(a.dataset.lastUsedAt) || 0;
                const lastUsedB = parseInt(b.dataset.lastUsedAt) || 0;
                
                // If both have last_used_at, sort by most recent
                if (lastUsedA && lastUsedB) {
                    return lastUsedB - lastUsedA; // Descending order (newest first)
                }
                
                // Items with last_used_at come first
                if (lastUsedA && !lastUsedB) return -1;
                if (!lastUsedA && lastUsedB) return 1;
                
                // If neither has last_used_at, sort alphabetically
                return a.dataset.brandName.localeCompare(b.dataset.brandName);
            });
        } else if (filter === 'favorite') {
            // Sort favorites first, then alphabetically
            visibleItems.sort((a, b) => {
                const isFavA = a.dataset.isFavorite === '1';
                const isFavB = b.dataset.isFavorite === '1';
                
                if (isFavA && !isFavB) return -1;
                if (!isFavA && isFavB) return 1;
                
                // If both are favorites or both are not, sort alphabetically
                return a.dataset.brandName.localeCompare(b.dataset.brandName);
            });
        }
        
        // Re-append items in the new order
        const brandListElement = document.getElementById('brandList');
        visibleItems.forEach(item => {
            brandListElement.appendChild(item);
        });
    }
    
    // Initial sort on page load
    filterAndSortBrands('', currentFilter);
    
    // Handle brand selection (clicking on avatar or name)
    if (brandList) {
        brandList.addEventListener('click', async (e) => {
            const brandItem = e.target.closest('.brand-item');
            
            // Check if click was on an action button
            if (e.target.closest('.brand-action-btn')) {
                return; // Don't select brand if clicking action button
            }
            
            if (brandItem) {
                const brandId = brandItem.dataset.brandId;
                const brandName = brandItem.querySelector('.brand-name-text').textContent;
                const avatar = brandItem.querySelector('.brand-avatar, .brand-avatar-placeholder').cloneNode(true);
                
                //console.log('Brand selected:', brandId, brandName);
                
                // Update the toggle button
                const selectedDisplay = dropdownToggle.querySelector('.selected-brand-display') || document.createElement('div');
                selectedDisplay.className = 'selected-brand-display';
                
                if (avatar.classList.contains('brand-avatar')) {
                    avatar.className = 'brand-avatar-small';
                } else {
                    avatar.className = 'brand-avatar-placeholder-small';
                }
                
                selectedDisplay.innerHTML = '';
                selectedDisplay.appendChild(avatar);
                selectedDisplay.innerHTML += `<span>${brandName}</span>`;
                
                dropdownToggle.innerHTML = '';
                dropdownToggle.appendChild(selectedDisplay);
                dropdownToggle.innerHTML += '<i class="fas fa-chevron-down ms-auto"></i>';
                
                // Update hidden input
                document.getElementById('selectedBrandId').value = brandId;
                
                // Close dropdown
                dropdownMenu.classList.remove('active');
                
                // Call brand change API
                await changeBrand(brandId);
            }
        });
    }
    
    // Favorite button handler
    document.addEventListener('click', async (e) => {
        if (e.target.closest('.favorite-btn')) {
            e.stopPropagation();
            const btn = e.target.closest('.favorite-btn');
            const brandId = btn.dataset.brandId;
            
            //console.log('Favorite button clicked for brand:', brandId);
            
            // Toggle UI immediately
            btn.classList.toggle('active');
            const icon = btn.querySelector('i');
            if (btn.classList.contains('active')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
            
            // Update data attribute
            const brandItem = btn.closest('.brand-item');
            brandItem.dataset.isFavorite = btn.classList.contains('active') ? '1' : '0';
            
            // Call API
            await toggleFavorite(brandId, btn);
        }
    });
    
    // Edit button handler
    document.addEventListener('click', (e) => {
        if (e.target.closest('.edit-btn')) {
            e.stopPropagation();
            const btn = e.target.closest('.edit-btn');
            const brandId = btn.dataset.brandId;
            
            //console.log('Edit button clicked for brand:', brandId);
            window.location.href = `${window.appRoutes.brandEdit}/${brandId}/edit`;
        }
    });
    
    // Delete button handler
    document.addEventListener('click', async (e) => {
        if (e.target.closest('.delete-btn')) {
            e.stopPropagation();
            const btn = e.target.closest('.delete-btn');
            const brandId = btn.dataset.brandId;
            
            //console.log('Delete button clicked for brand:', brandId);
            
            if (confirm('Are you sure you want to delete this brand?')) {
                await deleteBrand(brandId);
            }
        }
    });
    
    // Add brand button
    if (addBrandBtn) {
        addBrandBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            //console.log('Add brand clicked');
            window.location.href = window.appRoutes.brandCreate;
        });
    }
    
    // API Functions
    // API Functions
    async function changeBrand(brandId) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
        try {
            const response = await fetch(window.appRoutes.brandChange, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ brand_id: brandId })
            });
            
            const data = await response.json();
            
            if (data.success || data.status === 1) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message || 'Failed to change brand');
            }
        } catch (error) {
            console.error('Error changing brand:', error);
            alert('Failed to change brand');
        }
    }
    
    async function toggleFavorite(brandId, button) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
        try {
            const response = await fetch(window.appRoutes.brandToggleFavorite, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ brand_id: brandId })
            });
            
            const data = await response.json();
            //console.log('Toggle favorite response:', data);
        } catch (error) {
            //console.error('Error toggling favorite:', error);
            // Revert UI on error
            button.classList.toggle('active');
            const icon = button.querySelector('i');
            if (button.classList.contains('active')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
        }
    }
    
    async function deleteBrand(brandId) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
        try {
            const response = await fetch(`${window.appRoutes.brandDelete}/${brandId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Failed to delete brand');
            }
        } catch (error) {
            console.error('Error deleting brand:', error);
            alert('Failed to delete brand');
        }
    }
});

// Put this in your main JS file or in a script tag
function initHorizontalScroll() {
    document.querySelectorAll('.horizontal-scroll').forEach(container => {
        // Clone to remove any existing listeners (avoid duplicates)
        const clone = container.cloneNode(true);
        container.parentNode.replaceChild(clone, container);
        
        // Add fresh listener to the cloned element
        clone.addEventListener('wheel', function(e) {
            if (this.scrollWidth > this.clientWidth) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                this.scrollLeft += e.deltaY;
            }
        }, { passive: false, capture: true });
    });
}

// Initial load
document.addEventListener('DOMContentLoaded', initHorizontalScroll);