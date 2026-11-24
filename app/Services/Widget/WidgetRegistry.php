<?php

namespace App\Services\Widget;

use Illuminate\Support\Facades\File;

/**
 * Central registry for homepage layout widgets.
 *
 * Each theme ships a widgets.php that returns an array with:
 *   zones   – zone_key => ['label' => string]
 *   widgets – block_type => [
 *               'label'         => string,
 *               'allowed_zones' => string[],
 *               'views'         => zone_key => view_name|null,
 *             ]
 *   defaults – array of block definitions seeded when theme is first used
 *
 * Falls back to themes/default/widgets.php when a theme has no widgets.php.
 */
class WidgetRegistry
{
    private array $config = [];

    private array $registeredWidgets = [];

    public function __construct(private string $themesPath) {}

    public function load(string $theme): void
    {
        $file = $this->themesPath.'/'.$theme.'/widgets.php';

        if (! File::exists($file)) {
            $file = $this->themesPath.'/default/widgets.php';
        }

        if (! File::exists($file)) {
            $this->config = $this->fallbackConfig();
            $this->mergeRegisteredWidgets();

            return;
        }

        $this->config = require $file;
        $this->mergeRegisteredWidgets();
    }

    /** All zone definitions: zone_key => label */
    public function zones(): array
    {
        return collect($this->config['zones'] ?? [])
            ->map(fn ($z) => is_array($z) ? $z['label'] : $z)
            ->toArray();
    }

    /** All widget definitions */
    public function widgets(): array
    {
        return $this->config['widgets'] ?? [];
    }

    /** Settings schema for a widget type, or an empty array when it still uses legacy handling. */
    public function settingsSchema(string $type): array
    {
        $schema = $this->config['widgets'][$type]['settings_schema'] ?? [];

        return is_array($schema) ? $schema : [];
    }

    public function registerWidget(string $type, array $definition): void
    {
        $this->registeredWidgets[$type] = $definition;
        $this->config['widgets'][$type] = $definition;
    }

    public function registerWidgets(array $widgets): void
    {
        foreach ($widgets as $type => $definition) {
            if (is_array($definition)) {
                $this->registerWidget($type, $definition);
            }
        }
    }

    /** Widget label */
    public function label(string $type): string
    {
        return $this->config['widgets'][$type]['label'] ?? $type;
    }

    public function widget(string $type): array
    {
        return $this->config['widgets'][$type] ?? [];
    }

    /** Zones this widget type is allowed in */
    public function allowedZones(string $type): array
    {
        return $this->config['widgets'][$type]['allowed_zones'] ?? [];
    }

    /** Map of zone_key => view_name for a widget type */
    public function views(string $type): array
    {
        return $this->config['widgets'][$type]['views'] ?? [];
    }

    /** View name for a widget type in a specific zone, null if not supported */
    public function view(string $type, string $zone): ?string
    {
        return $this->config['widgets'][$type]['views'][$zone] ?? null;
    }

    /** allowedZones map for all widgets — used to generate JS validation */
    public function allowedZonesMap(): array
    {
        $map = [];
        foreach ($this->config['widgets'] ?? [] as $type => $def) {
            foreach ($def['allowed_zones'] ?? [] as $zone) {
                $map[$zone][] = $type;
            }
        }

        return $map;
    }

    /** Default blocks to seed when a theme is first used */
    public function defaults(): array
    {
        return $this->config['defaults'] ?? [];
    }

    /** All block_type => label pairs (for admin dropdowns) */
    public function blockTypes(): array
    {
        return collect($this->config['widgets'] ?? [])
            ->map(fn ($w) => $w['label'])
            ->toArray();
    }

    private function fallbackConfig(): array
    {
        return [
            'zones' => [
                'header' => ['label' => 'Header'],
                'main' => ['label' => 'Trang chủ'],
                'sidebar' => ['label' => 'Sidebar'],
                'footer_top' => ['label' => 'Footer trên'],
                'footer_1' => ['label' => 'Footer 1'],
                'footer_2' => ['label' => 'Footer 2'],
                'footer_3' => ['label' => 'Footer 3'],
                'footer_bottom' => ['label' => 'Footer dưới'],
            ],
            'widgets' => [],
            'defaults' => [],
        ];
    }

    private function mergeRegisteredWidgets(): void
    {
        if ($this->registeredWidgets === []) {
            return;
        }

        $this->config['widgets'] = array_merge($this->config['widgets'] ?? [], $this->registeredWidgets);
    }
}
