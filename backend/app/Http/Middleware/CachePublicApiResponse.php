<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CachePublicApiResponse
{
    public function handle(Request $request, Closure $next, int $ttl = 300): Response
    {
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        $cacheKey = 'public-api:' . sha1($request->fullUrl());

        if ($cached = Cache::get($cacheKey)) {
            return response($cached, 200, [
                'Content-Type' => 'application/json',
                'X-Cache' => 'HIT',
            ]);
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            Cache::put($cacheKey, $response->getContent(), $ttl);
            $response->headers->set('X-Cache', 'MISS');
        }

        return $response;
    }
}
