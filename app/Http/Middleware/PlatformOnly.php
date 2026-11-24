<?php

namespace App\Http\Middleware;

use App\Tenancy\PlatformDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlatformOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(PlatformDomain::matches($request), 404);

        return $next($request);
    }
}
