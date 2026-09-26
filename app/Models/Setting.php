<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'brand_name',
        'logo_path',
        'favicon_path',
        'color_primary',
        'color_primary_dark',
        'color_success',
        'color_warning',
        'color_danger',
        'theme_variant',
        'dev_mode',
        'footer_tagline',
        'social_instagram',
        'social_facebook',
        'social_tiktok',
        'social_whatsapp',
        'contact_email',
        'contact_phone',
        'contact_address',
    ];
}
