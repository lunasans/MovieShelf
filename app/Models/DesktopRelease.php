<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesktopRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'changelog',
        'download_url',
        'file_path',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Gibt das aktuellste öffentliche Release zurück.
     */
    public static function latestPublic()
    {
        return self::where('is_public', true)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
