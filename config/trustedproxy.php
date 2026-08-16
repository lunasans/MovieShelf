<?php

/*
|--------------------------------------------------------------------------
| Vertrauenswuerdige Proxies
|--------------------------------------------------------------------------
| Wird von bootstrap/app.php an Middleware::trustProxies() uebergeben.
|
| Nur von diesen Adressen werden X-Forwarded-*-Header akzeptiert. Frueher
| stand hier '*' – damit konnte jeder, der den Origin-Server direkt erreicht,
| per X-Forwarded-For eine beliebige Client-IP vortaeuschen und saemtliche
| IP-basierten throttle-Limits (Login, /claim, SSO, 2FA) umgehen.
|
| Default deckt den ueblichen Aufbau ab: Cloudflare vor nginx, nginx auf
| demselben Host wie PHP-FPM. Abweichende Setups per TRUSTED_PROXIES in der
| .env ueberschreiben (kommagetrennte IPs/CIDRs, '*' schaltet die Pruefung ab).
|
| Cloudflare-Ranges: https://www.cloudflare.com/ips/ – aendern sich selten,
| bei Bedarf hier nachziehen.
*/

$default = [
    // Lokaler Reverse-Proxy (nginx/Apache auf demselben Host)
    '127.0.0.1',
    '::1',

    // Cloudflare IPv4
    '173.245.48.0/20',
    '103.21.244.0/22',
    '103.22.200.0/22',
    '103.31.4.0/22',
    '141.101.64.0/18',
    '108.162.192.0/18',
    '190.93.240.0/20',
    '188.114.96.0/20',
    '197.234.240.0/22',
    '198.41.128.0/17',
    '162.158.0.0/15',
    '104.16.0.0/13',
    '104.24.0.0/14',
    '172.64.0.0/13',
    '131.0.72.0/22',

    // Cloudflare IPv6
    '2400:cb00::/32',
    '2606:4700::/32',
    '2803:f800::/32',
    '2405:b500::/32',
    '2405:8100::/32',
    '2a06:98c0::/29',
    '2c0f:f248::/32',
];

$configured = env('TRUSTED_PROXIES');

if ($configured === '*') {
    $proxies = '*';
} elseif (is_string($configured) && trim($configured) !== '') {
    $proxies = array_values(array_filter(array_map('trim', explode(',', $configured))));
} else {
    $proxies = $default;
}

return [
    'proxies' => $proxies,
];
