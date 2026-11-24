<?php

namespace App\Http\Middleware;

use App\Models\TenantDomain;
use App\Tenancy\DomainNormalizer;
use App\Tenancy\PlatformDomain;
use App\Tenancy\PlatformRuntimeManager;
use App\Tenancy\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(
        private TenantDatabaseManager $databases,
        private PlatformRuntimeManager $platformRuntime,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (PlatformDomain::matches($request)) {
            abort_unless(
                $request->is(trim((string) config('tenancy.platform_path', 'platform'), '/'))
                    || $request->is(trim((string) config('tenancy.platform_path', 'platform'), '/').'/*'),
                404,
            );

            $request->attributes->set('platform_runtime', true);
            $this->platformRuntime->initialize();

            try {
                return $next($request);
            } finally {
                $this->platformRuntime->end();
            }
        }

        if (! config('tenancy.enabled')) {
            return $next($request);
        }

        $domain = DomainNormalizer::normalize($request->getHost());
        $tenant = TenantDomain::query()
            ->with('tenant')
            ->where('domain', $domain)
            ->first()
            ?->tenant;

        abort_unless($tenant?->is_active, 404, 'Website is not configured for this domain.');

        $this->databases->initialize($tenant);

        try {
            return $next($request);
        } finally {
            $this->databases->end();
        }
    }
}
