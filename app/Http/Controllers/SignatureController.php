<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    /**
     * Display the generated signature banner.
     */
    public function show(Request $request)
    {
        // Prüfe ob Banner aktiviert
        if (Setting::get('signature_enabled', '1') !== '1') {
            abort(403, 'Signaturbanner sind deaktiviert');
        }

        $type = $request->query('type', 1);
        $type = max(1, min(3, (int)$type));

        $cacheTime = (int)Setting::get('signature_cache_time', '3600');
        $cacheKey = "signature_banner_type_{$type}";

        if ($request->has('clear_cache')) {
            Cache::forget($cacheKey);
        }

        // Wir geben das Bild direkt als Response aus
        return Cache::remember($cacheKey, $cacheTime, function () use ($type) {
            return $this->generateBanner($type);
        });
    }

    private function generateBanner($type)
    {
        // Prüfe ob GD installiert ist
        if (!function_exists('imagecreatetruecolor')) {
            return response('PHP GD Erweiterung ist nicht aktiviert. Bitte in der php.ini aktivieren (extension=gd).', 500)
                ->header('Content-Type', 'text/plain');
        }

        // Banner-Dimensionen
        $width = 800;
        $height = 150;

        // Erstelle Bild
        $img = \imagecreatetruecolor($width, $height);
        \imagesavealpha($img, true);

        // Farben
        $transparent = \imagecolorallocatealpha($img, 0, 0, 0, 127);
        $glass_bg_top = \imagecolorallocatealpha($img, 240, 245, 255, 15);
        $glass_bg_bottom = \imagecolorallocatealpha($img, 220, 230, 245, 30);
        $glass_border = \imagecolorallocatealpha($img, 200, 210, 230, 50);
        $glass_shadow = \imagecolorallocatealpha($img, 0, 0, 0, 40);
        $text_dark = \imagecolorallocate($img, 45, 55, 72);
        $text_muted = \imagecolorallocate($img, 161, 161, 170);
        $accent = \imagecolorallocate($img, 102, 126, 234);

        // Hintergrund transparent
        \imagefill($img, 0, 0, $transparent);

        // Einstellungen laden
        $filmCount = (int)Setting::get('signature_film_count', '10');
        $filmSource = Setting::get('signature_film_source', 'newest');
        $showTitle = Setting::get('signature_show_title', '1') === '1';
        $showYear = Setting::get('signature_show_year', '1') === '1';

        // Filme laden
        $query = Movie::query()->where('is_deleted', false)->whereNull('boxset_parent');

        switch ($filmSource) {
            case 'newest_release':
                $query->orderBy('year', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'random':
                $query->inRandomOrder();
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $films = $query->limit($filmCount)->get();
        $totalFilms = Movie::where('is_deleted', false)->whereNull('boxset_parent')->count();

        // Font Pfad
        $fontPath = public_path('fonts/Roboto-Medium.ttf');

        // Hintergrund zeichnen
        $this->drawGlassBackground($img, 0, 0, $width - 1, $height - 1, $glass_bg_top, $glass_bg_bottom, $glass_border, $glass_shadow);

        if ($type === 1) {
            $this->renderType1($img, $films, $totalFilms, $fontPath, $text_dark, $accent);
        } elseif ($type === 2) {
            $this->renderType2($img, $films, $totalFilms, $fontPath, $text_dark, $accent, $text_muted, $filmCount);
        } elseif ($type === 3) {
            $this->renderType3($img, $films, $fontPath, $text_dark, $text_muted, $showTitle, $showYear);
        }

        // Buffer befüllen
        \ob_start();
        \imagepng($img);
        $imageData = \ob_get_clean();
        \imagedestroy($img);

        return response($imageData)->header('Content-Type', 'image/png');
    }

    private function drawGlassBackground($img, $x, $y, $w, $h, $bg_top, $bg_bottom, $border, $shadow)
    {
        $radius = 12;
        for ($i = 0; $i < $h; $i++) {
            $ratio = $i / $h;
            $r = \imagecolorsforindex($img, $bg_top)['red'] * (1 - $ratio) + \imagecolorsforindex($img, $bg_bottom)['red'] * $ratio;
            $g = \imagecolorsforindex($img, $bg_top)['green'] * (1 - $ratio) + \imagecolorsforindex($img, $bg_bottom)['green'] * $ratio;
            $b = \imagecolorsforindex($img, $bg_top)['blue'] * (1 - $ratio) + \imagecolorsforindex($img, $bg_bottom)['blue'] * $ratio;
            $a = \imagecolorsforindex($img, $bg_top)['alpha'] * (1 - $ratio) + \imagecolorsforindex($img, $bg_bottom)['alpha'] * $ratio;
            $color = \imagecolorallocatealpha($img, (int)$r, (int)$g, (int)$b, (int)$a);
            
            $lineStart = $x;
            $lineEnd = $x + $w;
            if ($i < $radius) {
                $offset = $radius - \sqrt($radius * $radius - ($radius - $i) * ($radius - $i));
                $lineStart = $x + $offset;
                $lineEnd = $x + $w - $offset;
            } elseif ($i > $h - $radius) {
                $offset = $radius - \sqrt($radius * $radius - ($i - ($h - $radius)) * ($i - ($h - $radius)));
                $lineStart = $x + $offset;
                $lineEnd = $x + $w - $offset;
            }
            \imageline($img, (int)$lineStart, $y + $i, (int)$lineEnd, $y + $i, $color);
        }

        // Border
        \imageline($img, $x + $radius, $y, $x + $w - $radius, $y, $border);
        \imageline($img, $x + $radius, $y + $h, $x + $w - $radius, $y + $h, $border);
        \imageline($img, $x, $y + $radius, $x, $y + $h - $radius, $border);
        \imageline($img, $x + $w, $y + $radius, $x + $w, $y + $h - $radius, $border);
    }

    private function renderType1($img, $films, $totalFilms, $fontPath, $text_dark, $accent)
    {
        $statsBoxWidth = 115;
        $statsBoxHeight = 120;
        $statsBoxX = 20;
        $statsBoxY = 15;

        $statsBoxBg = \imagecolorallocatealpha($img, 200, 210, 230, 40);
        $this->imagefilledroundedrectangle($img, $statsBoxX, $statsBoxY, $statsBoxX + $statsBoxWidth, $statsBoxY + $statsBoxHeight, 10, $statsBoxBg);
        
        $logo = $this->loadLogo(22);
        if ($logo) {
            $logoX = $statsBoxX + ($statsBoxWidth - \imagesx($logo)) / 2;
            \imagecopy($img, $logo, (int)$logoX, $statsBoxY + 14, 0, 0, \imagesx($logo), \imagesy($logo));
            \imagedestroy($logo);
        }

        $this->drawText($img, 10, $statsBoxX, $statsBoxY + 54, $text_dark, $fontPath, "Filme gesamt:", true, $statsBoxWidth);
        $this->drawText($img, 22, $statsBoxX, $statsBoxY + 95, $accent, $fontPath, (string)$totalFilms, true, $statsBoxWidth);

        $coverWidth = 57;
        $coverHeight = 83;
        $startX = $statsBoxX + $statsBoxWidth + 18;
        $startY = 35;
        $gap = 6;

        $count = 0;
        foreach ($films as $film) {
            $cover = $this->loadCover($film->cover_id, $coverWidth, $coverHeight);
            if ($cover) {
                $x = $startX + ($count * ($coverWidth + $gap));
                if ($x + $coverWidth > 800 - 15) {
                    \imagedestroy($cover);
                    break;
                }
                \imagecopy($img, $cover, (int)$x, $startY, 0, 0, $coverWidth, $coverHeight);
                \imagedestroy($cover);
                $count++;
            }
        }
    }

    private function renderType2($img, $films, $totalFilms, $fontPath, $text_dark, $accent, $text_muted, $filmCount)
    {
        $logo = $this->loadLogo(26);
        $textY = 32;
        $startX = 25;

        if ($logo) {
            \imagecopy($img, $logo, $startX, 11, 0, 0, \imagesx($logo), \imagesy($logo));
            $logoW = \imagesx($logo);
            $this->drawText($img, 10, $startX + $logoW + 25, $textY, $accent, $fontPath, "{$totalFilms} Filme");
            $this->drawText($img, 9, $startX + $logoW + 120, $textY, $text_muted, $fontPath, "{$filmCount} Neueste:");
            \imagedestroy($logo);
        }

        $coverWidth = 65;
        $coverHeight = 92;
        $startX = 30;
        $startY = 52;
        $gap = 8;

        $count = 0;
        foreach ($films as $film) {
            $cover = $this->loadCover($film->cover_id, $coverWidth, $coverHeight);
            if ($cover) {
                $x = $startX + ($count * ($coverWidth + $gap));
                if ($x + $coverWidth > 800 - 25) {
                    \imagedestroy($cover);
                    break;
                }
                \imagecopy($img, $cover, (int)$x, $startY, 0, 0, $coverWidth, $coverHeight);
                \imagedestroy($cover);
                $count++;
            }
        }
    }

    private function renderType3($img, $films, $fontPath, $text_dark, $text_muted, $showTitle, $showYear)
    {
        $coverWidth = 65;
        $coverHeight = 95;
        $startX = 20;
        $startY = 20;
        $gap = 10;

        $count = 0;
        foreach ($films as $film) {
            $cover = $this->loadCover($film->cover_id, $coverWidth, $coverHeight);
            if ($cover) {
                $x = $startX + ($count * ($coverWidth + $gap));
                if ($x + $coverWidth > 800 - 20) {
                    \imagedestroy($cover);
                    break;
                }
                \imagecopy($img, $cover, (int)$x, $startY, 0, 0, $coverWidth, $coverHeight);
                \imagedestroy($cover);

                if ($showTitle) {
                    $title = mb_substr($film->title, 0, 16);
                    $this->drawText($img, 8, $x, $startY + $coverHeight + 14, $text_dark, $fontPath, $title);
                }
                if ($showYear) {
                    $this->drawText($img, 7, $x + 18, $startY + $coverHeight + 26, $text_muted, $fontPath, (string)$film->year);
                }
                $count++;
            }
        }
    }

    private function loadCover($coverId, $targetWidth, $targetHeight)
    {
        if (empty($coverId)) return null;
        
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $path = null;

        // Versuche verschiedene Pfade (ähnlich wie im Movie Model)
        if (str_contains($coverId, '.')) {
            if ($disk->exists($coverId)) {
                $path = $disk->path($coverId);
            }
        }

        if (!$path) {
            // Legacy local cover format (v1.5)
            $legacyPath = 'covers/' . $coverId . 'f.jpg';
            if ($disk->exists($legacyPath)) {
                $path = $disk->path($legacyPath);
            }
        }

        if (!$path || !file_exists($path)) return null;

        $info = \getimagesize($path);
        if (!$info) return null;

        switch ($info[2]) {
            case \IMAGETYPE_JPEG: $src = @\imagecreatefromjpeg($path); break;
            case \IMAGETYPE_PNG: $src = @\imagecreatefrompng($path); break;
            default: return null;
        }

        if (!$src) return null;

        $dst = \imagecreatetruecolor($targetWidth, $targetHeight);
        \imagesavealpha($dst, true);
        \imagefill($dst, 0, 0, \imagecolorallocatealpha($dst, 0, 0, 0, 127));
        \imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, \imagesx($src), \imagesy($src));
        \imagedestroy($src);

        return $dst;
    }

    private function loadLogo($targetHeight)
    {
        $path = public_path('img/logo/logo_small.png');
        if (!file_exists($path)) return null;

        $src = \imagecreatefrompng($path);
        if (!$src) return null;

        $srcW = \imagesx($src);
        $srcH = \imagesy($src);
        $ratio = $srcW / $srcH;
        $targetWidth = (int)($targetHeight * $ratio);

        $dst = \imagecreatetruecolor($targetWidth, $targetHeight);
        \imagesavealpha($dst, true);
        \imagefill($dst, 0, 0, \imagecolorallocatealpha($dst, 0, 0, 0, 127));
        \imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcW, $srcH);
        \imagedestroy($src);

        return $dst;
    }

    private function drawText($img, $size, $x, $y, $color, $font, $text, $centerX = false, $boxW = 0)
    {
        if (\file_exists($font)) {
            if ($centerX && $boxW > 0) {
                $bbox = \imagettfbbox($size, 0, $font, $text);
                $textW = $bbox[2] - $bbox[0];
                $x = $x + ($boxW - $textW) / 2;
            }
            return \imagettftext($img, $size, 0, (int)$x, (int)$y, (int)$color, $font, $text);
        }
        return false;
    }

    private function imagefilledroundedrectangle($img, $x1, $y1, $x2, $y2, $radius, $color)
    {
        \imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        \imagefilledrectangle($img, $x1, $y1 + $radius, $x1 + $radius - 1, $y2 - $radius, $color);
        \imagefilledrectangle($img, $x2 - $radius + 1, $y1 + $radius, $x2, $y2 - $radius, $color);
        \imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        \imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        \imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        \imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }
}
