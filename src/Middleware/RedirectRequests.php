<?php

namespace Bogddan\Redirects\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is(...config('redirects.exclude', []))) {
            return $next($request);
        }

        $path = urldecode($request->path());
        $redirect = app('redirect.model')->findValidOrNull($path);

        if (! $redirect && $request->getQueryString()) {
            $path .= '?' . $request->getQueryString();
            $redirect = app('redirect.model')->findValidOrNull(urldecode($path));
        }

        if ($redirect && $redirect->exists) {
            return redirect($redirect->new_url, $redirect->status);
        }

        return $next($request);
    }
}
