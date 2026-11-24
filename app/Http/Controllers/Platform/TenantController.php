<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantPackage;
use App\Services\Plugin\PluginManager;
use App\Services\Theme\ThemeManager;
use App\Tenancy\DomainNormalizer;
use App\Tenancy\PlatformAuditLogger;
use App\Tenancy\TenantDatabaseManager;
use App\Tenancy\TenantHealthChecker;
use App\Tenancy\TenantMaintenanceManager;
use App\Tenancy\TenantMigrator;
use App\Tenancy\TenantPackageManager;
use App\Tenancy\TenantProvisioner;
use App\Tenancy\TenantSeederCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');

        $query = Tenant::query()
            ->with(['domains', 'packages'])
            ->withCount('packages')
            ->latest();
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhereHas('domains', fn ($domains) => $domains->where('domain', 'like', "%{$search}%"));
            });
        }
        if (in_array($status, ['active', 'suspended'], true)) {
            $query->where('is_active', $status === 'active');
        }

        return view('platform.tenants.index', [
            'tenants' => $query->paginate(20)->withQueryString(),
            'counts' => [
                'all' => Tenant::query()->count(),
                'active' => Tenant::query()->where('is_active', true)->count(),
                'suspended' => Tenant::query()->where('is_active', false)->count(),
            ],
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(ThemeManager $themes, PluginManager $plugins): View
    {
        $password = trim((string) config('tenancy.initial_admin.password'));

        return view('platform.tenants.create', [
            ...$this->catalogData($themes, $plugins),
            'initialAdmin' => [
                'username' => (string) config('tenancy.initial_admin.username', 'admin'),
                'name' => (string) config('tenancy.initial_admin.name', 'Administrator'),
                'email' => (string) config('tenancy.initial_admin.email', 'admin@admin.com'),
                'password' => $password !== '' ? $password : Str::password(20),
            ],
        ]);
    }

    public function store(
        Request $request,
        TenantProvisioner $provisioner,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $validated = $this->validateTenant($request, true);

        try {
            $tenant = $provisioner->provision([
                ...$validated,
                'primary_domain' => $validated['primary_domain'],
                'aliases' => $this->parseAliases($request->input('aliases')),
                'plugins' => $validated['plugins'] ?? [],
                'create_database' => $request->boolean('create_database'),
                'is_active' => $request->boolean('is_active'),
            ]);
            $audit->log('tenant.created', $tenant, [
                'domain' => $validated['primary_domain'],
                'database' => $validated['db_database'],
            ]);
        } catch (Throwable $e) {
            return back()->withInput()->withErrors(['provision' => $e->getMessage()]);
        }

        return redirect()
            ->route('platform.tenants.show', $tenant)
            ->with('success', "Đã tạo tenant {$tenant->name}.");
    }

    public function show(
        Request $request,
        Tenant $tenant,
        ThemeManager $themes,
        PluginManager $plugins,
        TenantMaintenanceManager $maintenance,
        TenantPackageManager $packages,
        TenantSeederCatalog $seeders,
    ): View {
        $tenant->load(['domains', 'packages']);

        $tenantAdmins = $maintenance->administrators($tenant);
        $editingAdminId = $request->integer('edit_admin');

        return view('platform.tenants.show', [
            'tenant' => $tenant,
            ...$this->catalogData($themes, $plugins),
            'health' => session('tenant_health'),
            'activeTheme' => $packages->activeTheme($tenant),
            'tenantAdmins' => $tenantAdmins,
            'editingTenantAdmin' => collect($tenantAdmins['items'] ?? [])
                ->firstWhere('id', $editingAdminId),
            'auditLogs' => $tenant->auditLogs()->with('admin')->latest('created_at')->limit(30)->get(),
            'seeders' => $seeders->all(),
        ]);
    }

    public function edit(Tenant $tenant, ThemeManager $themes, PluginManager $plugins): View
    {
        $tenant->load(['domains', 'packages']);

        return view('platform.tenants.edit', [
            'tenant' => $tenant,
            ...$this->catalogData($themes, $plugins),
        ]);
    }

    public function update(
        Request $request,
        Tenant $tenant,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $validated = $this->validateTenant($request, false);

        try {
            $primary = DomainNormalizer::normalize($validated['primary_domain']);
            $domains = collect($this->parseAliases($request->input('aliases')))
                ->map(fn (string $domain) => DomainNormalizer::normalize($domain))
                ->push($primary)
                ->unique()
                ->values();
            if (config('tenancy.platform_domain')
                && $domains->contains(DomainNormalizer::normalize((string) config('tenancy.platform_domain')))) {
                throw ValidationException::withMessages([
                    'primary_domain' => 'Domain này được dành riêng cho Platform Admin.',
                ]);
            }
            $conflicts = TenantDomain::query()
                ->where('tenant_id', '!=', $tenant->id)
                ->whereIn('domain', $domains)
                ->pluck('domain');
            if ($conflicts->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'primary_domain' => 'Domain đã thuộc tenant khác: '.$conflicts->implode(', '),
                ]);
            }

            DB::connection((string) config('tenancy.central_connection', 'central'))
                ->transaction(function () use ($tenant, $validated, $domains, $primary) {
                    $attributes = [
                        'name' => $validated['name'],
                        'db_driver' => $validated['db_driver'],
                        'db_host' => $validated['db_driver'] === 'sqlite' ? null : ($validated['db_host'] ?? null),
                        'db_port' => $validated['db_driver'] === 'sqlite' ? null : ($validated['db_port'] ?? null),
                        'db_database' => $validated['db_database'],
                        'db_username' => $validated['db_driver'] === 'sqlite' ? null : ($validated['db_username'] ?? null),
                    ];
                    if (! empty($validated['db_password'])) {
                        $attributes['db_password'] = $validated['db_password'];
                    }
                    $tenant->update($attributes);
                    $tenant->domains()->delete();
                    foreach ($domains as $domain) {
                        $tenant->domains()->create([
                            'domain' => $domain,
                            'is_primary' => $domain === $primary,
                        ]);
                    }
                });
            $audit->log('tenant.updated', $tenant, ['domain' => $primary]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return back()->withInput()->withErrors(['tenant' => $e->getMessage()]);
        }

        return redirect()
            ->route('platform.tenants.show', $tenant)
            ->with('success', 'Đã cập nhật thông tin tenant.');
    }

    public function status(
        Request $request,
        Tenant $tenant,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);
        $tenant->update(['is_active' => (bool) $validated['is_active']]);
        $audit->log('tenant.status', $tenant, ['is_active' => $tenant->is_active]);

        return back()->with(
            'success',
            $tenant->is_active ? 'Tenant đã hoạt động trở lại.' : 'Tenant đã được tạm ngưng.',
        );
    }

    public function health(
        Tenant $tenant,
        TenantHealthChecker $checker,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $result = $checker->check($tenant);
        $audit->log('tenant.health', $tenant, [
            'ok' => $result['ok'],
            'latency_ms' => $result['latency_ms'],
        ]);

        return back()
            ->with('tenant_health', $result)
            ->with($result['ok'] ? 'success' : 'error', $result['ok']
                ? "Kết nối tenant thành công ({$result['latency_ms']} ms)."
                : 'Kiểm tra tenant thất bại.');
    }

    public function migrate(
        Tenant $tenant,
        TenantDatabaseManager $databases,
        TenantMigrator $migrator,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        try {
            $databases->initialize($tenant);
            foreach ($migrator->migrate() as $run) {
                if ($run['exit_code'] !== 0) {
                    throw new \RuntimeException("Migration lỗi tại {$run['path']}: ".trim($run['output']));
                }
            }
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        } finally {
            $databases->end();
        }

        $audit->log('tenant.migrated', $tenant);

        return back()->with('success', 'Đã migrate database tenant.');
    }

    public function package(
        Request $request,
        Tenant $tenant,
        TenantPackageManager $packages,
        PlatformAuditLogger $audit,
    ): RedirectResponse {
        $validated = $request->validate([
            'package_type' => ['required', Rule::in([TenantPackage::TYPE_THEME, TenantPackage::TYPE_PLUGIN])],
            'package_name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9][a-z0-9_-]*$/'],
            'operation' => ['required', Rule::in(['activate', 'assign', 'revoke', 'replace'])],
        ]);

        try {
            if ($validated['package_type'] === TenantPackage::TYPE_THEME) {
                if ($validated['operation'] === 'revoke') {
                    $packages->revokeTheme($tenant, $validated['package_name']);
                } else {
                    $packages->assignTheme(
                        $tenant,
                        $validated['package_name'],
                        in_array($validated['operation'], ['activate', 'replace'], true),
                    );
                }
            } elseif ($validated['operation'] === 'revoke') {
                $packages->revokePlugin($tenant, $validated['package_name']);
            } else {
                $packages->assignPlugin($tenant, $validated['package_name'], true);
            }
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $audit->log('tenant.package', $tenant, $validated);

        return back()->with('success', 'Đã cập nhật package của tenant.');
    }

    private function validateTenant(Request $request, bool $creating): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'primary_domain' => ['required', 'string', 'max:253'],
            'aliases' => ['nullable', 'string', 'max:4000'],
            'db_driver' => ['required', Rule::in(['mysql', 'mariadb', 'pgsql', 'sqlsrv', 'sqlite'])],
            'db_host' => ['nullable', 'string', 'max:255'],
            'db_port' => ['nullable', 'integer', 'between:1,65535'],
            'db_database' => ['required', 'string', 'max:1000'],
            'db_username' => ['nullable', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:1000'],
        ];

        if ($creating) {
            $rules += [
                'theme' => ['required', 'string', 'max:255'],
                'themes' => ['nullable', 'array'],
                'themes.*' => ['string', 'max:255'],
                'plugins' => ['nullable', 'array'],
                'plugins.*' => ['string', 'max:255'],
                'admin_user' => ['nullable', 'string', 'max:50', 'required_with:admin_password'],
                'admin_name' => ['nullable', 'string', 'max:100'],
                'admin_email' => ['nullable', 'email', 'max:150'],
                'admin_password' => ['nullable', 'string', 'min:12', 'required_with:admin_user'],
            ];
        }

        return $request->validate($rules);
    }

    private function parseAliases(mixed $value): array
    {
        return collect(preg_split('/[\s,]+/', trim((string) $value)) ?: [])
            ->filter()
            ->values()
            ->all();
    }

    private function catalogData(ThemeManager $themes, PluginManager $plugins): array
    {
        return [
            'themes' => $themes->catalog()->sortBy('title')->values(),
            'plugins' => $plugins->catalog()->sortBy('title')->values(),
        ];
    }
}
