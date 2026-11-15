<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password && ! $request->routeIs('admin.password.*', 'admin.logout')) {
            return redirect()->route('admin.password.edit');
        }

        return $next($request);
    }
}
