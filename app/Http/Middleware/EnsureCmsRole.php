<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCmsRole
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if ($request->user() === null || $request->user()->status !== 'active' || ! $request->user()->role()->whereIn('name', $roles)->exists()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}
