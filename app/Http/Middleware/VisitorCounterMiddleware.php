<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Counter;
use Symfony\Component\HttpFoundation\Response;

class VisitorCounterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Exclude common static asset paths and bot-like files
        $path = $request->path();
        if (preg_match('/\.(ico|png|jpg|jpeg|gif|svg|css|js|map|txt|xml|json)$/i', $path)) {
            return $next($request);
        }

        // 2. Only count GET requests and non-AJAX requests
        if ($request->isMethod('GET') && !$request->ajax() && !$request->prefetch()) {
            
            // 3. User-Agent Bot Filtering (Simple list of common bots)
            $userAgent = $request->header('User-Agent', '');
            $bots = [
                'bot', 'crawler', 'spider', 'slurp', 'bingpreview', 'googlebot', 
                'baiduspider', 'yandex', 'duckduckbot', 'lighthouse', 'headless'
            ];
            
            foreach ($bots as $bot) {
                if (stripos($userAgent, $bot) !== false) {
                    return $next($request);
                }
            }

            // 4. Unique IP per Day check using Cache
            $ip = $request->ip();
            $today = now()->format('Y-m-d');
            $cacheKey = "visit:{$today}:" . md5($ip);

            if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                // Prevention of double-counting in the SAME request process
                static $alreadyCounted = false;
                
                if (!$alreadyCounted) {
                    // Increment total visits
                    $totalCounter = Counter::firstOrCreate(['page' => 'all']);
                    $totalCounter->increment('visits');
                    $totalCounter->last_visit = now();
                    $totalCounter->save();

                    // Increment daily visits
                    $dailyCounter = Counter::firstOrCreate(['page' => "daily:$today"]);
                    $dailyCounter->increment('visits');
                    $dailyCounter->last_visit = now();
                    $dailyCounter->save();
                    
                    $alreadyCounted = true;
                    
                    // Mark as visited for this IP today (expires at end of day)
                    \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->endOfDay());
                }
            }
        }

        return $next($request);
    }
}
