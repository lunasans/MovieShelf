<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LandingPage extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'sort_order', 'is_active', 'show_in_nav'];

    protected function casts(): array
    {
        return [
            'is_active'   => 'boolean',
            'show_in_nav' => 'boolean',
        ];
    }

    public static function generateSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
