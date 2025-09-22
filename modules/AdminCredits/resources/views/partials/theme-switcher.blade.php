<div class="colors d-flex align-items-center gap-1">
    <div class="form-check form-check-inline ml-10">
      <input class="form-check-input" type="checkbox" name="theme_color" id="theme_color_dark" value="dark" {{ $theme === 'dark' ? 'checked' : '' }}>
      <label class="form-check-label" for="theme_color_dark">Dark Theme</label>
    </div>

    <input type="color" id="colorPicker" value="{{ $primaryHex }}" class="colorPicker w-25 h-20" role="button">
    <input type="color" id="colorPicker_sec" value="{{ $secHex }}" class="colorPicker w-25 h-20" role="button">

    <i class="fa-solid fa-undo colorPicker_reset fs-20" data-bs-toggle="tooltip" data-bs-placement="bottom"
       title="Reset" role="button" aria-label="Reset"></i>
  </div>
  <script>
  window.routes = {
    setColor: @json(route('profile.set_color')),
    saveTheme: @json(route('settings.save_theme')),
  };
  window.csrfToken = "{{ csrf_token() }}";
  window.themeCssUrl = "{{ theme_public_asset('css/theme.css') }}";
</script>