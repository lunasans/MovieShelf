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
        // Only count GET requests and non-AJAX requests (to avoid counting background actions)
        if ($request->isMethod('GET') && !$request->ajax() && !$request->prefetch()) {
            
            // Prevention of double-counting in the same request process
            static $alreadyCounted = false;
            
            if (!$alreadyCounted) {
                // Increment total visits
                $totalCounter = Counter::firstOrCreate(['page' => 'all']);
                $totalCounter->increment('visits');
                $totalCounter->last_visit = now();
                $totalCounter->save();

                // Increment daily visits
                $today = now()->format('Y-m-d');
                $dailyCounter = Counter::firstOrCreate(['page' => "daily:$today"]);
                $dailyCounter->increment('visits');
                $dailyCounter->last_visit = now();
                $dailyCounter->save();
                
                $alreadyCounted = true;
            }
        }

        return $next($request);
    }
}
