@php
  $supportsThemeVars = $supportsThemeVars ?? false;
@endphp

@if(!$supportsThemeVars)
  <div class="alert alert-warning mb-3">
    La personalización de variables CSS no está habilitada todavía. Ejecutá <code>php artisan migrate</code>.
  </div>
@else
  <div class="border rounded p-3 mb-3">
    <h4 class="h5 mb-2">Tema visual del front</h4>
    <p class="text-muted small mb-3">Estos valores sobrescriben las variables <code>:root</code> usadas en <code>public/css/project.css</code>.</p>

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Google Font URL (fuente por defecto)</label>
        <input
          type="url"
          class="form-control"
          name="theme_vars[google_font_default_url]"
          value="{{ $themeVars['google_font_default_url'] ?? '' }}"
          placeholder="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;900&display=swap"
        >
      </div>
      <div class="col-md-6">
        <label class="form-label">Google Font URL (fuente primaria)</label>
        <input
          type="url"
          class="form-control"
          name="theme_vars[google_font_primary_url]"
          value="{{ $themeVars['google_font_primary_url'] ?? '' }}"
          placeholder="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;900&display=swap"
        >
      </div>

      <div class="col-md-6">
        <label class="form-label">--font-default</label>
        <input type="text" class="form-control" name="theme_vars[font_default]" value="{{ $themeVars['font_default'] ?? '' }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">--font-primary</label>
        <input type="text" class="form-control" name="theme_vars[font_primary]" value="{{ $themeVars['font_primary'] ?? '' }}">
      </div>

      <div class="col-md-3">
        <label class="form-label">--color-default</label>
        <input type="color" class="form-control form-control-color w-100" name="theme_vars[color_default]" value="{{ $themeVars['color_default'] ?? '#444444' }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">--color-primary</label>
        <input type="color" class="form-control form-control-color w-100" name="theme_vars[color_primary]" value="{{ $themeVars['color_primary'] ?? '#00205f' }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">--color-primary-dark</label>
        <input type="color" class="form-control form-control-color w-100" name="theme_vars[color_primary_dark]" value="{{ $themeVars['color_primary_dark'] ?? '#2e87d3' }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">--color-secondary</label>
        <input type="color" class="form-control form-control-color w-100" name="theme_vars[color_secondary]" value="{{ $themeVars['color_secondary'] ?? '#00205f' }}">
      </div>

      <div class="col-md-3">
        <label class="form-label">--color-tertiary</label>
        <input type="color" class="form-control form-control-color w-100" name="theme_vars[color_tertiary]" value="{{ $themeVars['color_tertiary'] ?? '#D52B1E' }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">--color-white</label>
        <input type="color" class="form-control form-control-color w-100" name="theme_vars[color_white]" value="{{ $themeVars['color_white'] ?? '#FFFFFF' }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">--color-light</label>
        <input type="color" class="form-control form-control-color w-100" name="theme_vars[color_light]" value="{{ $themeVars['color_light'] ?? '#f1f1f1' }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">--color-dark</label>
        <input type="color" class="form-control form-control-color w-100" name="theme_vars[color_dark]" value="{{ $themeVars['color_dark'] ?? '#002037' }}">
      </div>

      <div class="col-md-6">
        <label class="form-label">--regular-shadow</label>
        <input type="text" class="form-control" name="theme_vars[regular_shadow]" value="{{ $themeVars['regular_shadow'] ?? '' }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">scroll-behavior</label>
        <select class="form-select" name="theme_vars[scroll_behavior]">
          <option value="smooth" @selected(($themeVars['scroll_behavior'] ?? 'smooth') === 'smooth')>smooth</option>
          <option value="auto" @selected(($themeVars['scroll_behavior'] ?? 'smooth') === 'auto')>auto</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">--bs-link-color</label>
        <input type="color" class="form-control form-control-color w-100" name="theme_vars[bs_link_color]" value="{{ $themeVars['bs_link_color'] ?? '#00205f' }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">--swiper-navigation-color</label>
        <input type="color" class="form-control form-control-color w-100" name="theme_vars[swiper_navigation_color]" value="{{ $themeVars['swiper_navigation_color'] ?? '#00205f' }}">
      </div>
    </div>
  </div>
@endif
