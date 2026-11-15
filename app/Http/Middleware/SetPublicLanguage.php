<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicLanguage
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $language = (string) $request->route('locale');
        abort_unless(in_array($language, config('cms.content.supported_languages'), true), 404);
        app()->setLocale($language);

        return $next($request);
    }
}
