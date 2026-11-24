<?php

namespace App\Tenancy;

use Illuminate\Http\Request;
use InvalidArgumentException;

class PlatformDomain
{
    public static function configured(): ?string
    {
        $domain = trim((string) config('tenancy.platform_domain'));
        if ($domain === '') {
            return null;
        }

        try {
            return DomainNormalizer::normalize($domain);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    public static function matches(Request $request): bool
    {
        $configured = self::configured();

        return $configured !== null
            && DomainNormalizer::normalize($request->getHost()) === $configured;
    }
}
