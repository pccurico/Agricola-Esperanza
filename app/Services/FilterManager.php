<?php

declare(strict_types=1);

namespace CampoSur\Services;

final class FilterManager
{
    private array $defaults;
    private array $options;

    public function __construct(array $defaults = [], array $options = [])
    {
        $this->defaults = $defaults;
        $this->options = $options;
    }

    public function resolve(array $input): array
    {
        $resolved = $this->defaults;

        foreach ($resolved as $key => $value) {
            if (!array_key_exists($key, $input)) {
                continue;
            }

            $incoming = $input[$key];
            if ($incoming === '' || $incoming === null) {
                continue;
            }

            if (is_int($value)) {
                $resolved[$key] = max(0, (int) $incoming);
                continue;
            }

            $resolved[$key] = trim((string) $incoming);
        }

        return $resolved;
    }

    public function defaults(): array
    {
        return $this->defaults;
    }

    public function options(): array
    {
        return $this->options;
    }
}
