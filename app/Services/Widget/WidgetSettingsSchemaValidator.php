<?php

namespace App\Services\Widget;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WidgetSettingsSchemaValidator
{
    public function __construct(private WidgetRegistry $registry) {}

    public function hasSchema(string $blockType): bool
    {
        return $this->registry->settingsSchema($blockType) !== [];
    }

    public function validate(Request $request, string $blockType, array $existing = []): array
    {
        $schema = $this->registry->settingsSchema($blockType);
        if ($schema === []) {
            return $existing;
        }

        $fields = $this->normalizeFields($schema);
        $rules = [];

        foreach ($fields as $key => $field) {
            $this->appendRules($rules, "settings.$key", $field);
        }

        $request->validate($rules);

        return $this->collectValues($request, $fields, $existing, $blockType);
    }

    private function appendRules(array &$rules, string $path, array $field): void
    {
        $type = $field['type'] ?? 'text';
        $nullable = ($field['required'] ?? false) ? 'required' : 'nullable';

        if ($type === 'repeater') {
            $rules[$path] = 'nullable|array';
            foreach ($this->normalizeFields($field['fields'] ?? []) as $childKey => $childField) {
                $this->appendRules($rules, "$path.*.$childKey", $childField);
                if (($childField['type'] ?? null) === 'image') {
                    $rules["$path.*.existing_$childKey"] = 'nullable|string|max:255';
                }
            }

            return;
        }

        $rules[$path] = match ($type) {
            'number' => $this->numberRules($field, $nullable),
            'select' => $this->selectRules($field, $nullable),
            'checkbox' => 'nullable|boolean',
            'image' => $this->imageRules($field, $nullable),
            'textarea', 'richtext' => $nullable.'|string|max:'.(int) ($field['max'] ?? 20000),
            'url' => $nullable.'|string|max:'.(int) ($field['max'] ?? 255),
            'color' => $nullable.'|string|max:20',
            default => $nullable.'|string|max:'.(int) ($field['max'] ?? 255),
        };
    }

    private function collectValues(Request $request, array $fields, array $existing, string $blockType): array
    {
        $out = $existing;

        foreach ($fields as $key => $field) {
            $type = $field['type'] ?? 'text';

            if ($type === 'checkbox') {
                data_set($out, $key, $request->boolean("settings.$key"));
                continue;
            }

            if ($type === 'image') {
                data_set($out, $key, $this->storeImage($request, "settings.$key", data_get($existing, $key), $field, $blockType));
                continue;
            }

            if ($type === 'repeater') {
                data_set($out, $key, $this->collectRepeater($request, $key, $field, data_get($existing, $key, []), $blockType));
                continue;
            }

            $value = $request->input("settings.$key", $field['default'] ?? null);
            if ($type === 'number') {
                data_set($out, $key, $value === null || $value === '' ? (int) ($field['default'] ?? 0) : (int) $value);
            } else {
                data_set($out, $key, $value ?? '');
            }
        }

        return $out;
    }

    private function collectRepeater(Request $request, string $key, array $field, array $existing, string $blockType): array
    {
        $rows = array_values($request->input("settings.$key", []));
        $children = $this->normalizeFields($field['fields'] ?? []);
        $out = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $item = [];
            foreach ($children as $childKey => $childField) {
                $type = $childField['type'] ?? 'text';
                if ($type === 'checkbox') {
                    $item[$childKey] = (bool) ($row[$childKey] ?? false);
                } elseif ($type === 'image') {
                    $item[$childKey] = $this->storeImage(
                        $request,
                        "settings.$key.$index.$childKey",
                        $row["existing_$childKey"] ?? ($existing[$index][$childKey] ?? null),
                        $childField,
                        $blockType,
                    );
                } elseif ($type === 'number') {
                    $value = $row[$childKey] ?? ($childField['default'] ?? 0);
                    $item[$childKey] = $value === '' ? (int) ($childField['default'] ?? 0) : (int) $value;
                } else {
                    $item[$childKey] = $row[$childKey] ?? ($childField['default'] ?? '');
                }
            }

            if ($this->rowHasValue($item)) {
                $out[] = $item;
            }
        }

        return $out;
    }

    private function storeImage(Request $request, string $path, ?string $existing, array $field, string $blockType): ?string
    {
        if (! $request->hasFile($path)) {
            return $existing;
        }

        return $request->file($path)->store($field['store'] ?? 'homepage-'.$blockType, 'public');
    }

    private function normalizeFields(array $schema): array
    {
        return $schema['fields'] ?? $schema;
    }

    private function numberRules(array $field, string $nullable): array
    {
        $rules = [$nullable, 'integer'];
        if (isset($field['min'])) {
            $rules[] = 'min:'.(int) $field['min'];
        }
        if (isset($field['max'])) {
            $rules[] = 'max:'.(int) $field['max'];
        }

        return $rules;
    }

    private function selectRules(array $field, string $nullable): array
    {
        $options = array_keys($field['options'] ?? []);

        return $options === [] ? [$nullable, 'string'] : [$nullable, Rule::in($options)];
    }

    private function imageRules(array $field, string $nullable): string
    {
        return $nullable.'|image|max:'.(int) ($field['max'] ?? 4096);
    }

    private function rowHasValue(array $item): bool
    {
        foreach ($item as $value) {
            if (is_bool($value) && $value) {
                return true;
            }
            if (is_scalar($value) && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }
}
