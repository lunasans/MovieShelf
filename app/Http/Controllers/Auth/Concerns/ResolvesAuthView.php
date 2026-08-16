<?php

namespace App\Http\Controllers\Auth\Concerns;

use Illuminate\Http\Request;

/**
 * routes/auth.php haengt an beiden Domains: an der Central-Domain und – ueber
 * TenancyServiceProvider::mapRoutes() – an jedem Regal. Die Auth-Seiten sollen
 * dort jeweils zum Umfeld passen:
 *
 *   Central-Domain -> central/auth/*  (MovieShelf-Branding, Landingpage-Design)
 *   Tenant-Regal   -> tenant/auth/*   (gleicher Aufbau, aber mit site_title
 *                                      und Theme-Akzent des jeweiligen Regals)
 */
trait ResolvesAuthView
{
    protected function authView(Request $request, string $name): string
    {
        $isCentral = in_array($request->getHost(), config('tenancy.central_domains', []), true);

        return $isCentral ? "central.auth.{$name}" : "tenant.auth.{$name}";
    }
}
