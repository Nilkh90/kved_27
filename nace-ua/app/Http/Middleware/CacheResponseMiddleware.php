<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheResponseMiddleware
{
    /**
     * Very small response cache for public GET pages.
     *
     * We keep this tiny to avoid any coupling issues with package versions,
     * while still unblocking Phase 2 caching requirements.
     */
    public function handle(Request $request, Closure $next, int $minutes = 60): Response
    {
        if (! config('responsecache.enabled', true)) {
            return $next($request);
        }

        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        // Don't cache when there is an authenticated user session.
        if ($request->hasSession() && $request->session()->isStarted() && $request->session()->has('login_web')) {
            return $next($request);
        }

        $key = 'resp:' . sha1($request->fullUrl());

        /** @var \Illuminate\Contracts\Cache\Repository $cache */
        $cache = cache()->store();

        $cached = $cache->get($key);
        if (is_array($cached) && isset($cached['status'], $cached['headers'], $cached['content'])) {
            return new Response($cached['content'], (int) $cached['status'], (array) $cached['headers']);
        }

        /** @var Response $response */
        $response = $next($request);

        if ($response->isSuccessful()) {
            $cache->put($key, [
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->all(),
                'content' => $response->getContent(),
            ], now()->addMinutes($minutes));
        }

        return $response;
    }
}

