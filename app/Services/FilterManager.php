<?php

declare(strict_types=1);

namespace AgroPCC\Services;

class FilterManager
{
    private array $values;

    public function __construct(array $defaults = [], private readonly array $options = [])
    {
        $this->values = $defaults;
    }

    public function resolve(array $incoming): array
    {
        $resolved = $this->values;

        foreach ($resolved as $key => $value) {
            if (!array_key_exists($key, $incoming)) {
                continue;
            }

            $incomingValue = $incoming[$key];
            if ($incomingValue === '' || $incomingValue === null) {
                continue;
            }

            if (in_array($key, ['farm_id', 'block_id', 'season_id', 'cost_center_id'], true)) {
                $resolved[$key] = max(0, (int) $incomingValue);
                continue;
            }

            $resolved[$key] = trim((string) $incomingValue);
        }

        return $resolved;
    }

    public function defaults(): array
    {
        return $this->values;
    }

    public function options(): array
    {
        return $this->options;
    }
}
