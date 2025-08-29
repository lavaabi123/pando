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