<?php

namespace App\Tenancy;

use InvalidArgumentException;

class DomainNormalizer
{
    public static function normalize(string $domain): string
    {
        $domain = strtolower(rtrim(trim($domain), '.'));

        if (str_contains($domain, '://')) {
            $domain = (string) parse_url($domain, PHP_URL_HOST);
        } elseif (str_contains($domain, ':') && substr_count($domain, ':') === 1) {
            $domain = explode(':', $domain, 2)[0];
        }

        $ascii = function_exists('idn_to_ascii')
            ? idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46)
            : $domain;

        $domain = is_string($ascii) ? strtolower(rtrim($ascii, '.')) : '';
        if ($domain === '' || strlen($domain) > 253 || ! preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $domain)) {
            throw new InvalidArgumentException("Domain '{$domain}' is invalid.");
        }

        return $domain;
    }
}
