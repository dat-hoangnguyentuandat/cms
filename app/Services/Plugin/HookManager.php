<?php

namespace App\Services\Plugin;

class HookManager
{
    private array $listeners = [];

    public function listen(string $hook, callable $callback, int $priority = 10): void
    {
        $this->listeners[$hook][$priority][] = $callback;
        ksort($this->listeners[$hook]);
    }

    public function apply(string $hook, mixed $value, mixed ...$args): mixed
    {
        foreach ($this->listeners[$hook] ?? [] as $callbacks) {
            foreach ($callbacks as $callback) {
                $value = $callback($value, ...$args);
            }
        }

        return $value;
    }

    public function do(string $hook, mixed ...$args): void
    {
        foreach ($this->listeners[$hook] ?? [] as $callbacks) {
            foreach ($callbacks as $callback) {
                $callback(...$args);
            }
        }
    }
}
