<?php
/**
 * MovieShelf Signatur-Banner Generator
 * Generiert dynamische Banner mit Film-Covern
 * Liest Einstellungen aus settings-Tabelle
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Fehlerausgabe unterdrücken
error_reporting(0);
ini_set('display_errors', 0);

// Prüfe ob Banner aktiviert
if (getSetting('signature_enabled', '1') != '1') {
    header('HTTP/1.1 403 Forbidden');
    exit('Signatur-Banner sind deaktiviert');
}

// Banner-Typ (1, 2, oder 3)
$type = isset($_GET['type']) ? (int)$_GET['type'] : 1;
$type = max(1, min(3, $type));

// Prüfe ob dieser Typ aktiviert ist
if (getSetting("signature_enable_type{$type}", '1') != '1') {
    header('HTTP/1.1 403 Forbidden');
    exit("Banner-Typ {$type} ist deaktiviert");
}

// Cache-Parameter aus Settings
$cacheTime = (int)getSetting('signature_cache_time', '3600');
$cacheFile = __DIR__ . "/cache/signature_type{$type}.png";

// Cache leeren wenn angefordert
if (isset($_GET['clear_cache'])) {
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
    exit('Cache geleert');
}

// Prüfe Cache
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=' . $cacheTime);
    readfile($cacheFile);
    exit;
}

// Banner-Dimensionen (von 120 auf 150 erhöht)
$width = 800;
$height = 150;

// Erstelle Bild
$img = imagecreatetruecolor($width, $height);
imagesavealpha($img, true);

// Farben - Heller Glaseffekt mit Gradient
$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
$glass_bg_top = imagecolorallocatealpha($img, 240, 245, 255, 15);  // Noch transparenter
$glass_bg_bottom = imagecolorallocatealpha($img, 220, 230, 245, 30);  
$glass_border = imagecolorallocatealpha($img, 200, 210, 230, 50);  // Dunklerer Rahmen für bessere Sichtbarkeit
$glass_shadow = imagecolorallocatealpha($img, 0, 0, 0, 40);  // Dezenter Schatten
$cover_shadow = imagecolorallocatealpha($img, 0, 0, 0, 60);  // Schatten für Cover
$text_white = imagecolorallocate($img, 228, 228, 231);
$text_muted = imagecolorallocate($img, 161, 161, 170);
$text_dark = imagecolorallocate($img, 45, 55, 72); // Dunkleres Navy-Grau für Kontrast
$accent = imagecolorallocate($img, 102, 126, 234);

// Hintergrund transparent
imagefill($img, 0, 0, $transparent);

// Einstellungen laden
$filmCount = (int)getSetting('signature_film_count', '10');
$filmSource = getSetting('signature_film_source', 'newest');
$showTitle = getSetting('signature_show_title', '1') == '1';
$showYear = getSetting('signature_show_year', '1') == '1';
$showRating = getSetting('signature_show_rating', '0') == '1';

// SQL-Query basierend auf Film-Quelle
try {
    switch ($filmSource) {
        case 'newest_release':
            $orderBy = 'year DESC, created_at DESC';
            break;
        case 'best_rated':
            $orderBy = 'created_at DESC'; // Fallback
            break;
        case 'random':
            $orderBy = 'RAND()';
            break;
        case 'newest':
        default:
            $orderBy = 'created_at DESC';
            break;
    }
    
    $stmt = $pdo->prepare("
        SELECT id, title, year, cover_id, created_at
        FROM dvds 
        WHERE deleted = 0 
        ORDER BY {$orderBy}
        LIMIT ?
    ");
    $stmt->execute([$filmCount]);
    $films = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sammlung-Stats
    $statsStmt = $pdo->query("SELECT COUNT(*) as total FROM dvds WHERE deleted = 0");
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    $totalFilms = $stats['total'] ?? 0;
    
} catch (PDOException $e) {
    $films = [];
    $totalFilms = 0;
}

// Hilfsfunktionen
function imagefilledroundedrectangle($img, $x1, $y1, $x2, $y2, $radius, $color) {
    // Zeichne ohne Überlappungen (wichtig für Transparenz/Alpha)
    // 1. Mittelteil (volle Höhe, reduzierte Breite)
    imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
    // 2. Linker Flügel (reduzierte Höhe)
    imagefilledrectangle($img, $x1, $y1 + $radius, $x1 + $radius - 1, $y2 - $radius, $color);
    // 3. Rechter Flügel (reduzierte Höhe)
    imagefilledrectangle($img, $x2 - $radius + 1, $y1 + $radius, $x2, $y2 - $radius, $color);
    
    // 4. Die vier Ecken (Viertel-Ellipsen)
    imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
}

function drawGlassBackground($img, $x, $y, $w, $h, $bg_top, $bg_bottom, $border, $shadow) {
    $radius = 12;
    for ($i = 0; $i < $h; $i++) {
        $ratio = $i / $h;
        $r = imagecolorsforindex($img, $bg_top)['red'] * (1 - $ratio) + imagecolorsforindex($img, $bg_bottom)['red'] * $ratio;
        $g = imagecolorsforindex($img, $bg_top)['green'] * (1 - $ratio) + imagecolorsforindex($img, $bg_bottom)['green'] * $ratio;
        $b = imagecolorsforindex($img, $bg_top)['blue'] * (1 - $ratio) + imagecolorsforindex($img, $bg_bottom)['blue'] * $ratio;
        $a = imagecolorsforindex($img, $bg_top)['alpha'] * (1 - $ratio) + imagecolorsforindex($img, $bg_bottom)['alpha'] * $ratio;
        $color = imagecolorallocatealpha($img, $r, $g, $b, $a);
        $lineStart = $x;
        $lineEnd = $x + $w;
        if ($i < $radius) {
            $offset = $radius - sqrt($radius * $radius - ($radius - $i) * ($radius - $i));
            $lineStart = $x + $offset;
            $lineEnd = $x + $w - $offset;
        } elseif ($i > $h - $radius) {
            $offset = $radius - sqrt($radius * $radius - ($i - ($h - $radius)) * ($i - ($h - $radius)));
            $lineStart = $x + $offset;
            $lineEnd = $x + $w - $offset;
        }
        imageline($img, $lineStart, $y + $i, $lineEnd, $y + $i, $color);
    }
    for ($i = 0; $i < 3; $i++) {
        $shadowAlpha = 60 + ($i * 20);
        $shadowColor = imagecolorallocatealpha($img, 0, 0, 0, $shadowAlpha);
        $offset = $i * 2;
        imageline($img, $x + $radius + $offset, $y + $h + $i, $x + $w - $radius - $offset, $y + $h + $i, $shadowColor);
    }
    imagesetthickness($img, 1);
    for ($angle = 0; $angle < 360; $angle += 2) {
        $rad = deg2rad($angle);
        if ($angle >= 180 && $angle < 270) imagesetpixel($img, $x + $radius + cos($rad) * $radius, $y + $radius + sin($rad) * $radius, $border);
        if ($angle >= 270 && $angle < 360) imagesetpixel($img, $x + $w - $radius + cos($rad) * $radius, $y + $radius + sin($rad) * $radius, $border);
        if ($angle >= 90 && $angle < 180) imagesetpixel($img, $x + $radius + cos($rad) * $radius, $y + $h - $radius + sin($rad) * $radius, $border);
        if ($angle >= 0 && $angle < 90) imagesetpixel($img, $x + $w - $radius + cos($rad) * $radius, $y + $h - $radius + sin($rad) * $radius, $border);
    }
    imageline($img, $x + $radius, $y, $x + $w - $radius, $y, $border);
    imageline($img, $x + $radius, $y + $h, $x + $w - $radius, $y + $h, $border);
    imageline($img, $x, $y + $radius, $x, $y + $h - $radius, $border);
    imageline($img, $x + $w, $y + $radius, $x + $w, $y + $h - $radius, $border);
}

function loadCover($coverId, $targetWidth, $targetHeight) {
    if (empty($coverId)) return null;
    $extensions = ['.jpg', '.jpeg', '.png'];
    foreach ($extensions as $ext) {
        $file = BASE_PATH . '/' . COVER_IMG_PATH . "/{$coverId}f{$ext}";
        if (file_exists($file)) {
            $info = getimagesize($file);
            if (!$info) continue;
            switch ($info[2]) {
                case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($file); break;
                case IMAGETYPE_PNG: $src = imagecreatefrompng($file); break;
                default: continue 2;
            }
            if (!$src) continue;
            $dst = imagecreatetruecolor($targetWidth, $targetHeight);
            imagesavealpha($dst, true);
            imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, imagesx($src), imagesy($src));
            $radius = 6; $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            for ($x = 0; $x < $radius; $x++) {
                for ($y = 0; $y < $radius; $y++) {
                    if (($x - $radius)**2 + ($y - $radius)**2 > $radius**2) imagesetpixel($dst, $x, $y, $transparent);
                    if (($targetWidth - 1 - $x - $radius)**2 + ($y - $radius)**2 > $radius**2) imagesetpixel($dst, $targetWidth - 1 - $x, $y, $transparent);
                    if (($x - $radius)**2 + ($targetHeight - 1 - $y - $radius)**2 > $radius**2) imagesetpixel($dst, $x, $targetHeight - 1 - $y, $transparent);
                    if (($targetWidth - 1 - $x - $radius)**2 + ($targetHeight - 1 - $y - $radius)**2 > $radius**2) imagesetpixel($dst, $targetWidth - 1 - $x, $targetHeight - 1 - $y, $transparent);
                }
            }
            imagedestroy($src); return $dst;
        }
    }
    return null;
}

// Font Pfad
$fontPath = BASE_PATH . '/assets/fonts/Roboto/static/Roboto-Medium.ttf';

// Helfer für TTF-Text (erleichtert die Umstellung)
function drawText($img, $size, $x, $y, $color, $font, $text, $centerX = false, $boxW = 0) {
    if (file_exists($font)) {
        if ($centerX && $boxW > 0) {
            $bbox = imagettfbbox($size, 0, $font, $text);
            $textW = $bbox[2] - $bbox[0];
            $x = $x + ($boxW - $textW) / 2;
        }
        return imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
    } else {
        // Fallback falls Font fehlt
        $fontSize = ($size > 12) ? 4 : 3;
        return imagestring($img, $fontSize, $x, $y - $size, $text, $color);
    }
}

function loadLogo($targetHeight) {
    $file = BASE_PATH . '/assets/images/logo/logo_small.png';
    if (!file_exists($file)) return null;
    $src = imagecreatefrompng($file);
    if (!$src) return null;
    $srcW = imagesx($src);
    $srcH = imagesy($src);
    $ratio = $srcW / $srcH;
    $targetWidth = (int)($targetHeight * $ratio);
    $dst = imagecreatetruecolor($targetWidth, $targetHeight);
    imagesavealpha($dst, true);
    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcW, $srcH);
    imagedestroy($src);
    return $dst;
}

// ============================================
// VARIANTE 1: Cover Grid mit Statistik (Optimiert auf 150px)
// ============================================
if ($type === 1) {
    drawGlassBackground($img, 0, 0, $width - 1, $height - 1, $glass_bg_top, $glass_bg_bottom, $glass_border, $glass_shadow);
    
    // Statistik-Box (größer und zentriert)
    $statsBoxWidth = 115;
    $statsBoxHeight = 120;
    $statsBoxX = 20;
    $statsBoxY = 15;
    
    $statsBoxBg = imagecolorallocatealpha($img, 200, 210, 230, 40);
    imagefilledroundedrectangle($img, $statsBoxX, $statsBoxY, $statsBoxX + $statsBoxWidth, $statsBoxY + $statsBoxHeight, 10, $statsBoxBg);
    $statsBoxBorder = imagecolorallocatealpha($img, 180, 190, 210, 60);
    imagerectangle($img, $statsBoxX + 1, $statsBoxY + 1, $statsBoxX + $statsBoxWidth - 1, $statsBoxY + $statsBoxHeight - 1, $statsBoxBorder);
    
    // Logo in der Stats-Box
    $logoH = 22;
    $logo = loadLogo($logoH);
    if ($logo) {
        $logoX = $statsBoxX + ($statsBoxWidth - imagesx($logo)) / 2;
        imagecopy($img, $logo, $logoX, $statsBoxY + 14, 0, 0, imagesx($logo), imagesy($logo));
        imagedestroy($logo);
    }
    
    drawText($img, 10, $statsBoxX, $statsBoxY + 54, $text_dark, $fontPath, "Filme gesamt:", true, $statsBoxWidth);
    drawText($img, 22, $statsBoxX, $statsBoxY + 95, $accent, $fontPath, (string)$totalFilms, true, $statsBoxWidth);
    
    // Cover Grid
    $coverWidth = 75;
    $coverHeight = 110;
    $startX = $statsBoxX + $statsBoxWidth + 18;
    $startY = 20;
    $gap = 6;
    
    foreach ($films as $i => $film) {
        $x = $startX + ($i * ($coverWidth + $gap));
        if ($x + $coverWidth > $width - 15) break;
        $cover = loadCover($film['cover_id'], $coverWidth, $coverHeight);
        if ($cover) {
            for ($s = 0; $s < 3; $s++) {
                $shadowCol = imagecolorallocatealpha($img, 0, 0, 0, 70 - ($s * 20));
                imagerectangle($img, $x + $s, $startY + $s, $x + $coverWidth + $s, $startY + $coverHeight + $s, $shadowCol);
            }
            imagecopy($img, $cover, $x, $startY, 0, 0, $coverWidth, $coverHeight);
            imagedestroy($cover);
        }
    }
}

// ============================================
// VARIANTE 2: Cover + Stats (Optimiert auf 150px)
// ============================================
elseif ($type === 2) {
    drawGlassBackground($img, 0, 0, $width - 1, $height - 1, $glass_bg_top, $glass_bg_bottom, $glass_border, $glass_shadow);
    
    // Header mit Logo statt Text
    $logoH = 26;
    $logo = loadLogo($logoH);
    $textY = 32;
    $startX = 25;
    
    if ($logo) {
        imagecopy($img, $logo, $startX, 11, 0, 0, imagesx($logo), imagesy($logo));
        $logoW = imagesx($logo);
        drawText($img, 10, $startX + $logoW + 25, $textY, $accent, $fontPath, "{$totalFilms} Filme");
        drawText($img, 9, $startX + $logoW + 120, $textY, $text_muted, $fontPath, "{$filmCount} Neueste:");
        imagedestroy($logo);
    } else {
        drawText($img, 13, $startX, $textY, $text_dark, $fontPath, "MovieShelf");
        drawText($img, 10, $startX + 140, $textY, $accent, $fontPath, "{$totalFilms} Filme");
        drawText($img, 9, $startX + 260, $textY, $text_muted, $fontPath, "{$filmCount} Neueste:");
    }
    
    imageline($img, 25, 45, $width - 25, 45, $glass_border);
    
    // Cover (wieder größer, aber mit viel Luft)
    $coverWidth = 65;
    $coverHeight = 92;
    $startX = 30;
    $startY = 52;
    $gap = 8;
    
    foreach ($films as $i => $film) {
        $x = $startX + ($i * ($coverWidth + $gap));
        if ($x + $coverWidth > $width - 25) break;
        $cover = loadCover($film['cover_id'], $coverWidth, $coverHeight);
        if ($cover) {
            for ($s = 0; $s < 2; $s++) {
                $shadowCol = imagecolorallocatealpha($img, 0, 0, 0, 60 - ($s * 20));
                imagerectangle($img, $x + $s, $startY + $s, $x + $coverWidth + $s, $startY + $coverHeight + $s, $shadowCol);
            }
            imagecopy($img, $cover, $x, $startY, 0, 0, $coverWidth, $coverHeight);
            imagedestroy($cover);
        }
    }
}

// ============================================
// VARIANTE 3: Compact Liste (Optimiert auf 150px)
// ============================================
elseif ($type === 3) {
    drawGlassBackground($img, 0, 0, $width - 1, $height - 1, $glass_bg_top, $glass_bg_bottom, $glass_border, $glass_shadow);
    
    $coverWidth = 65;
    $coverHeight = 95;
    $startX = 20;
    $startY = 20;
    $gap = 10;
    
    foreach ($films as $i => $film) {
        $x = $startX + ($i * ($coverWidth + $gap));
        if ($x + $coverWidth > $width - 20) break;
        $cover = loadCover($film['cover_id'], $coverWidth, $coverHeight);
        if ($cover) {
            for ($s = 0; $s < 3; $s++) {
                $shadowCol = imagecolorallocatealpha($img, 0, 0, 0, 75 - ($s * 20));
                imagerectangle($img, $x + $s, $startY + $s, $x + $coverWidth + $s, $startY + $coverHeight + $s, $shadowCol);
            }
            imagecopy($img, $cover, $x, $startY, 0, 0, $coverWidth, $coverHeight);
            imagedestroy($cover);
            
            // Text unter dem Cover (jetzt mit viel Platz und Kontrast)
            if ($showTitle) {
                $title = mb_substr($film['title'], 0, 11);
                drawText($img, 8, $x, $startY + $coverHeight + 14, $text_dark, $fontPath, $title);
            }
            if ($showYear) {
                drawText($img, 7, $x + 18, $startY + $coverHeight + 26, $text_muted, $fontPath, (string)$film['year']);
            }
        }
    }
}

// Speichere Cache
if (!is_dir(__DIR__ . '/cache')) {
    mkdir(__DIR__ . '/cache', 0755, true);
}

$quality = (int)getSetting('signature_quality', '9');
imagepng($img, $cacheFile, $quality);

// Ausgabe
header('Content-Type: image/png');
header('Cache-Control: public, max-age=' . $cacheTime);
imagepng($img, null, $quality);

// Cleanup
imagedestroy($img);