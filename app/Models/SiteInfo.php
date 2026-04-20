<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteInfo extends Model
{
    public const THEME_VAR_DEFAULTS = [
        'google_font_default_url' => '',
        'google_font_primary_url' => '',
        'font_default' => '"Poppins"',
        'font_primary' => '"Poppins"',
        'color_default' => '#444444',
        'color_primary' => '#00205f',
        'color_primary_dark' => '#2e87d3',
        'color_secondary' => '#00205f',
        'color_tertiary' => '#D52B1E',
        'color_white' => '#FFFFFF',
        'color_light' => '#f1f1f1',
        'color_dark' => '#002037',
        'regular_shadow' => '0 .5rem 1rem rgba(0,0,0,.15)!important',
        'scroll_behavior' => 'smooth',
        'bs_link_color' => '#00205f',
        'swiper_navigation_color' => '#00205f',
    ];

    protected $fillable = [
        'site_title',
        'company_name',
        'company_address',
        'company_website',
        'support_email',
        'logo_path',
        'theme_vars',
    ];

    protected $casts = [
        'theme_vars' => 'array',
    ];

    // Accesor práctico: $info->logo_url
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function resolvedThemeVars(): array
    {
        $stored = is_array($this->theme_vars) ? $this->theme_vars : [];
        return array_replace(self::THEME_VAR_DEFAULTS, array_intersect_key($stored, self::THEME_VAR_DEFAULTS));
    }
}
