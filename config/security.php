<?php

/*
|--------------------------------------------------------------------------
| Security-Response-Header
|--------------------------------------------------------------------------
| Wird von App\Http\Middleware\SecurityHeaders auf alle Web-Antworten
| gesetzt. Bereits gesetzte Header werden nicht überschrieben. Ein Header
| mit Wert null/'' wird nicht gesendet.
|
| Die CSP muss alle extern eingebundenen Quellen abdecken:
|  - www.youtube-nocookie.com   → Trailer-Embeds
|  - img-src https:             → Cover/Backdrops können absolute URLs sein
|                                 (Legacy-Daten), daher bewusst breit
|  - 'unsafe-eval'              → von Alpine.js benötigt (Expression-Parser)
|  - 'unsafe-inline'            → Inline-<script>/<style> in den Blade-Views
|
| Turnstile (challenges.cloudflare.com) gehört zur Cloud und ist hier
| bewusst nicht freigegeben.
*/

return [

    'headers' => [

        'Content-Security-Policy' => env('SECURITY_CSP', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            "style-src 'self' 'unsafe-inline'",
            "font-src 'self' data:",
            "img-src 'self' data: blob: https:",
            "media-src 'self' blob:",
            "connect-src 'self'",
            "frame-src 'self' https://www.youtube-nocookie.com",
            "worker-src 'self' blob:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ])),

        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ],

    // Nur auf HTTPS-Antworten gesetzt.
    'hsts' => env('SECURITY_HSTS', 'max-age=31536000; includeSubDomains'),

    /*
    | Schlüssel für die clientseitige Verschlüsselung der Off-site-Backups
    | (S3/R2). 32 Zufallsbytes, base64-kodiert – erzeugen mit:
    |   php -r "echo base64_encode(random_bytes(32));"
    | Ohne Schlüssel schlägt der Off-site-Upload mit klarer Meldung fehl.
    | ACHTUNG: Schlüssel separat sichern – ohne ihn sind die .ms.enc-Dateien
    | nicht wiederherstellbar.
    */
    'backup_encryption_key' => env('BACKUP_ENCRYPTION_KEY'),

];
