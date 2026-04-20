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
        'download_url_linux_appimage',
        'download_url_linux_deb',
        'file_path',
        'file_hash',
        'file_hash_linux_appimage',
        'file_hash_linux_deb',
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
