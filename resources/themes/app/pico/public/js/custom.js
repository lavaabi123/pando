document.addEventListener("DOMContentLoaded", () => {
  const $brandSelect = $('#brandSelect');
  if (!$brandSelect.length) return;

  let inFlight; // AbortController for single-flight

  async function postJSON(url, payload) {
    if (inFlight) inFlight.abort();
    inFlight = new AbortController();

    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const res = await fetch(url, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": token,
        "Accept": "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
      signal: inFlight.signal
    });

    // Non-OK? Try to extract text for debugging
    if (!res.ok) {
      const text = await res.text();
      throw new Error(res.status + ' ' + res.statusText + ' — ' + text.slice(0, 300));
    }
    return res.json();
  }

  // Visual disable while posting
  function setBusy(b) {
    $brandSelect.prop('disabled', b).trigger('change.select2'); // updates UI if select2
    $brandSelect.closest('.select2-container').toggleClass('is-busy', b);
  }

  $brandSelect.on('change', async function () {
    const brandId = $(this).val();
    const aurl = $(this).data('change-url');
    if (!aurl) return console.warn('Missing data-change-url on #brandSelect');
    if (!brandId) return; // ignore placeholder

    try {
      setBusy(true);
      const data = await postJSON(aurl, { brand_id: brandId });

      if (data.success === true || data.status === 1) {
        if (data.redirect) window.location.href = data.redirect;
        else window.location.reload();
      } else {
        alert(data.message || 'Failed to change brand.');
        setBusy(false);
      }
    } catch (err) {
      // Common cases: 419 (CSRF), 401/403 (auth/permission), HTML error response
      console.error('Brand change error:', err);
      const msg = String(err);
      if (msg.includes('419')) alert('Session expired (419). Please refresh and try again.');
      else if (msg.includes('401') || msg.includes('403')) alert('You do not have access to change the brand.');
      else alert('Network/server error while changing brand.');
      setBusy(false);
    }
  });
});
(function(){
    const root = document.documentElement;
	const routes = window.routes;
	const token  = window.csrfToken;
    // remove any classes on <html> that start with primary-/secondary-
    function cleanColorClasses() {
      const toRemove = Array.from(root.classList).filter(c => c.startsWith('primary-') || c.startsWith('secondary-'));
      root.classList.remove(...toRemove);
    }
    function colorToClass(hex){ return (hex || '').replace('#',''); }

    
    $('.colorPicker').on('input', function() {
      const pColor = $('#colorPicker').val();
      const sColor = $('#colorPicker_sec').val();

      // mirror CI4 behavior
      cleanColorClasses();
      root.classList.add('primary-' + colorToClass(pColor));
      root.classList.add('secondary-' + colorToClass(sColor));

      root.style.setProperty('--d-primary', pColor);
      root.style.setProperty('--d-secondary', sColor);
      root.style.setProperty('--sp-primary', pColor);
      root.style.setProperty('--sp-secondary', sColor);

      $.ajax({
        url: routes.setColor,
        type: 'POST',
        data: { pColor: pColor, sColor: sColor, _token: token },
        dataType: 'json'
      });
    });

    // === Dark theme checkbox ===
    document.getElementById('theme_color_dark').addEventListener('click', function () {
  const checked = this.checked;
  const theme_color = checked ? 'dark' : 'light';

  fetch(routes.saveTheme, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': token
    },
    body: JSON.stringify({ theme_color })
  })
  .then(() => {
    if (checked) {
      document.documentElement.setAttribute('data-theme','dark');
      document.body.classList.remove('light');
      document.body.classList.add('dark');

      // load dark theme css
      loadThemeCss('/css/theme.css');
    } else {
      document.documentElement.setAttribute('data-theme','light');
      document.body.classList.remove('dark');
      document.body.classList.add('light');

      // remove theme.css if light mode
      const oldLink = document.getElementById('theme-css');
      if (oldLink) oldLink.remove();
    }
  });
});


    // === Reset to defaults ===
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

      $.ajax({
        url: routes.setColor,
        type: 'POST',
        data: { pColor: pColor, sColor: sColor, _token: token },
        dataType: 'json'
      });
    });
  })();
  
  function loadThemeCss() {
  // remove any existing theme.css
  const oldLink = document.getElementById('theme-css');
  if (oldLink) oldLink.remove();

  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = window.themeCssUrl; // <-- provided by Blade
  link.id = 'theme-css';
  document.head.appendChild(link);
}

// Example: if dark theme already set
if (document.documentElement.getAttribute('data-theme') === 'dark') {
  loadThemeCss();
}

// Example: toggle with checkbox
document.getElementById('theme_color_dark').addEventListener('change', function () {
  if (this.checked) {
    loadThemeCss();
  } else {
    const oldLink = document.getElementById('theme-css');
    if (oldLink) oldLink.remove();
  }
});