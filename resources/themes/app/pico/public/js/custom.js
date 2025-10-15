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