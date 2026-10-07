<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\SettingsHelper;
use App\Support\ThemePresets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    public function index(): View
    {
        $settings = SettingsHelper::get();
        $presetOptions = ThemePresets::getVariantOptions();

        return view('admin.developer.index', compact('settings', 'presetOptions'));
    }

    public function update(Request $request)
    {
        $presetKeys = array_keys(ThemePresets::all());

        $validated = $request->validate([
            'brand_name' => 'required|string|max:50',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'favicon' => 'nullable|image|mimes:png,ico|max:512',
            'color_primary' => 'required|string|max:7',
            'color_primary_dark' => 'required|string|max:7',
            'color_secondary' => 'required|string|max:7',
            'color_secondary_dark' => 'required|string|max:7',
            'color_success' => 'required|string|max:7',
            'color_warning' => 'required|string|max:7',
            'color_danger' => 'required|string|max:7',
            'theme_variant' => 'required|in:'.implode(',', $presetKeys),
            'dev_mode' => 'boolean',
            'footer_tagline' => 'nullable|string|max:200',
            'social_instagram' => 'nullable|url|max:255',
            'social_facebook' => 'nullable|url|max:255',
            'social_tiktok' => 'nullable|url|max:255',
            'social_whatsapp' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_address' => 'nullable|string|max:500',
        ]);

        $setting = Setting::first() ?? new Setting;
        $setting->brand_name = $validated['brand_name'];

        // Apply preset colors if theme_variant changed (unless in dev_mode where user customizes manually)
        $newVariant = $validated['theme_variant'];
        $isDevMode = $request->boolean('dev_mode');

        if (! $isDevMode || $setting->theme_variant !== $newVariant) {
            ThemePresets::applyToSettings($newVariant, $setting);
        } else {
            // In dev_mode and same variant - use manually entered colors
            $setting->color_primary = $validated['color_primary'];
            $setting->color_primary_dark = $validated['color_primary_dark'];
            $setting->color_secondary = $validated['color_secondary'];
            $setting->color_secondary_dark = $validated['color_secondary_dark'];
            $setting->color_success = $validated['color_success'];
            $setting->color_warning = $validated['color_warning'];
            $setting->color_danger = $validated['color_danger'];
        }

        $setting->theme_variant = $newVariant;
        $setting->dev_mode = $isDevMode;
        $setting->footer_tagline = $validated['footer_tagline'] ?? null;
        $setting->social_instagram = $validated['social_instagram'] ?? null;
        $setting->social_facebook = $validated['social_facebook'] ?? null;
        $setting->social_tiktok = $validated['social_tiktok'] ?? null;
        $setting->social_whatsapp = $validated['social_whatsapp'] ?? null;
        $setting->contact_email = $validated['contact_email'] ?? null;
        $setting->contact_phone = $validated['contact_phone'] ?? null;
        $setting->contact_address = $validated['contact_address'] ?? null;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            $setting->logo_path = $path;
        }
        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('branding', 'public');
            $setting->favicon_path = $path;
        }
        $setting->save();

        Cache::forget('app_settings');

        return redirect()->route('admin.developer')->with('swal_success', 'Pengaturan berhasil disimpan');
    }
}
