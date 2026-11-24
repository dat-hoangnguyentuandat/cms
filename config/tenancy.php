<?php

return [
    'enabled' => (bool) env('TENANCY_ENABLED', false),

    'central_connection' => env('TENANCY_CENTRAL_CONNECTION', 'central'),
    'tenant_connection' => 'tenant',

    /*
    | Platform administration is served only from this exact hostname. Keep it
    | separate from every customer domain, for example platform.cms.example.
    */
    'platform_domain' => env('PLATFORM_DOMAIN'),
    'platform_path' => 'platform',

    'initial_admin' => [
        'username' => env('TENANCY_INITIAL_ADMIN_USERNAME', 'admin'),
        'name' => env('TENANCY_INITIAL_ADMIN_NAME', 'Administrator'),
        'email' => env('TENANCY_INITIAL_ADMIN_EMAIL', 'admin@admin.com'),
        'password' => env('TENANCY_INITIAL_ADMIN_PASSWORD'),
    ],

    /*
    | Theme and plugin source code is a shared catalog. In multi-tenant mode,
    | tenant administrators may activate catalog entries but cannot replace or
    | delete the shared packages unless this flag is explicitly enabled.
    */
    'allow_package_management' => (bool) env('TENANCY_ALLOW_PACKAGE_MANAGEMENT', false),
];
