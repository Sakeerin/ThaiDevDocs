<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $minutes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $minutes = 60): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Don't cache authenticated requests
        if ($request->user()) {
            return $next($request);
        }

        // Generate cache key
        $cacheKey = 'response_' . md5($request->fullUrl());

        // Check if cached response exists
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            
            return response()
                ->json($cached['data'])
                ->header('X-Cache', 'HIT')
                ->header('Cache-Control', "public, max-age={$minutes}");
        }

        // Get fresh response
        $response = $next($request);

        // Cache successful responses
        if ($response->isSuccessful()) {
            $data = json_decode($response->getContent(), true);
            
            Cache::put($cacheKey, [
                'data' => $data,
                'cached_at' => now()->toIso8601String(),
            ], now()->addMinutes($minutes));

            $response->header('X-Cache', 'MISS');
            $response->header('Cache-Control', "public, max-age={$minutes}");
        }

        return $response;
    }
}
