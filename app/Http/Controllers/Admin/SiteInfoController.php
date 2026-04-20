<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SiteInfoController extends Controller
{
    public function index()
    {
        $info = SiteInfo::query()->first();
        $supportsThemeVars = $this->hasSiteInfoColumn('theme_vars');
        return view('admin.info.index', compact('info', 'supportsThemeVars'));
    }

    public function create()
    {
        // En general habrá una sola fila; si ya existe, redirigimos a editar
        $info = SiteInfo::query()->first();
        if ($info) {
            return redirect()->route('admin.info.edit', $info);
        }
        $supportsThemeVars = $this->hasSiteInfoColumn('theme_vars');
        return view('admin.info.create', compact('supportsThemeVars'));
    }

    public function store(Request $request)
    {
        $hasSiteTitleColumn = $this->hasSiteInfoColumn('site_title');
        $hasThemeVarsColumn = $this->hasSiteInfoColumn('theme_vars');

        $rules = [
            'company_name'    => ['nullable','string','max:255'],
            'company_address' => ['nullable','string','max:255'],
            'company_website' => ['nullable','string','max:255'],
            'support_email'   => ['nullable','email','max:255'],
            'logo'            => ['nullable','image','mimes:png,jpg,jpeg,webp','max:2048'],
            'remove_logo'     => ['nullable','boolean'],
        ];

        if ($hasSiteTitleColumn) {
            $rules['site_title'] = ['nullable','string','max:255'];
        }

        if ($hasThemeVarsColumn) {
            $rules = array_merge($rules, $this->themeVarsRules());
        }

        $data = $request->validate($rules);
        if ($hasThemeVarsColumn) {
            $data['theme_vars'] = $this->normalizeThemeVars((array) ($data['theme_vars'] ?? []));
        }

        $info = SiteInfo::firstOrNew([]);

        // Quitar logo si lo piden
        if ($request->boolean('remove_logo') && $info->logo_path) {
            Storage::disk('public')->delete($info->logo_path);
            $info->logo_path = null;
        }

        // Subir nuevo logo
        if ($request->hasFile('logo')) {
            if ($info->logo_path) {
                Storage::disk('public')->delete($info->logo_path);
            }
            // guarda en storage/app/public/site/logo
            $info->logo_path = $request->file('logo')->store('site/logo', 'public');
        }

        $info->fill($data)->save();

        return redirect()->route('admin.info.index')->with('success', 'Información institucional actualizada.');
    }

    public function edit(SiteInfo $info)
    {
        $supportsThemeVars = $this->hasSiteInfoColumn('theme_vars');
        return view('admin.info.edit', compact('info', 'supportsThemeVars'));
    }

    public function update(Request $request, SiteInfo $info)
    {
        $hasSiteTitleColumn = $this->hasSiteInfoColumn('site_title');
        $hasThemeVarsColumn = $this->hasSiteInfoColumn('theme_vars');

        $rules = [
            'company_name'    => ['nullable','string','max:255'],
            'company_address' => ['nullable','string','max:255'],
            'company_website' => ['nullable','string','max:255'],
            'support_email'   => ['nullable','email','max:255'],
            'logo'            => ['nullable','image','mimes:png,jpg,jpeg,webp','max:2048'],
            'remove_logo'     => ['nullable','boolean'],
        ];

        if ($hasSiteTitleColumn) {
            $rules['site_title'] = ['nullable','string','max:255'];
        }

        if ($hasThemeVarsColumn) {
            $rules = array_merge($rules, $this->themeVarsRules());
        }

        $data = $request->validate($rules);
        if ($hasThemeVarsColumn) {
            $data['theme_vars'] = $this->normalizeThemeVars((array) ($data['theme_vars'] ?? []));
        }

        // Quitar logo si lo piden
        if ($request->boolean('remove_logo') && $info->logo_path) {
            Storage::disk('public')->delete($info->logo_path);
            $info->logo_path = null;
        }

        // Subir nuevo logo
        if ($request->hasFile('logo')) {
            if ($info->logo_path) {
                Storage::disk('public')->delete($info->logo_path);
            }
            // guarda en storage/app/public/site/logo
            $info->logo_path = $request->file('logo')->store('site/logo', 'public');
        }

        $info->fill($data)->save();

        return redirect()->route('admin.info.index')->with('success', 'Información institucional actualizada.');
    }

    protected function hasSiteInfoColumn(string $column): bool
    {
        return Schema::hasTable('site_infos') && Schema::hasColumn('site_infos', $column);
    }

    protected function themeVarsRules(): array
    {
        $hexColorRule = ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'];
        $safeTextRule = ['nullable', 'string', 'max:120', 'regex:/^[^;{}<>]+$/'];
        $googleFontUrlRule = ['nullable', 'url', 'max:500', 'regex:/^https:\/\/fonts\.googleapis\.com\//i'];

        return [
            'theme_vars' => ['nullable', 'array'],
            'theme_vars.google_font_default_url' => $googleFontUrlRule,
            'theme_vars.google_font_primary_url' => $googleFontUrlRule,
            'theme_vars.font_default' => $safeTextRule,
            'theme_vars.font_primary' => $safeTextRule,
            'theme_vars.color_default' => $hexColorRule,
            'theme_vars.color_primary' => $hexColorRule,
            'theme_vars.color_primary_dark' => $hexColorRule,
            'theme_vars.color_secondary' => $hexColorRule,
            'theme_vars.color_tertiary' => $hexColorRule,
            'theme_vars.color_white' => $hexColorRule,
            'theme_vars.color_light' => $hexColorRule,
            'theme_vars.color_dark' => $hexColorRule,
            'theme_vars.regular_shadow' => ['nullable', 'string', 'max:150', 'regex:/^[^;{}<>]+$/'],
            'theme_vars.scroll_behavior' => ['nullable', 'in:auto,smooth'],
            'theme_vars.bs_link_color' => $hexColorRule,
            'theme_vars.swiper_navigation_color' => $hexColorRule,
        ];
    }

    protected function normalizeThemeVars(array $incoming): array
    {
        $normalized = [];
        foreach (SiteInfo::THEME_VAR_DEFAULTS as $key => $default) {
            $value = $incoming[$key] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $normalized[$key] = ($value === null || $value === '') ? $default : $value;
        }

        return $normalized;
    }
}
