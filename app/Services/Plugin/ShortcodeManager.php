<?php

namespace App\Services\Plugin;

class ShortcodeManager
{
    private array $shortcodes = [];

    public function register(string $tag, callable $callback): void
    {
        $this->shortcodes[$tag] = $callback;
    }

    public function has(string $tag): bool
    {
        return isset($this->shortcodes[$tag]);
    }

    public function render(string $content): string
    {
        if ($this->shortcodes === []) {
            return $content;
        }

        return preg_replace_callback('/\[([a-zA-Z0-9_\-]+)([^\]]*?)\s*\/?\]/', function (array $matches) {
            $tag = $matches[1];
            if (! isset($this->shortcodes[$tag])) {
                return $matches[0];
            }

            return (string) call_user_func(
                $this->shortcodes[$tag],
                $this->parseAttributes($matches[2] ?? ''),
                $matches[0],
            );
        }, $content) ?? $content;
    }

    private function parseAttributes(string $raw): array
    {
        preg_match_all('/([a-zA-Z0-9_\-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s\]]+))/', $raw, $matches, PREG_SET_ORDER);

        $attrs = [];
        foreach ($matches as $match) {
            $attrs[$match[1]] = html_entity_decode($match[2] ?: $match[3] ?: $match[4] ?: '', ENT_QUOTES, 'UTF-8');
        }

        return $attrs;
    }
}
