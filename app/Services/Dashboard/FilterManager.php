<?php

declare(strict_types=1);

namespace CampoSur\Services\Dashboard;

final class FilterManager
{
    private array $values = [
        'date_from' => '',
        'date_to' => '',
        'farm_id' => 0,
        'block_id' => 0,
        'process' => '',
    ];

    public function __construct(array $defaults = [], private readonly array $options = [])
    {
        $this->values = array_merge($this->values, $defaults);
    }

    public function resolve(array $incoming): array
    {
        $filters = $this->values;

        foreach ($filters as $key => $value) {
            if (!array_key_exists($key, $incoming)) {
                continue;
            }

            if ($incoming[$key] === '' || $incoming[$key] === null) {
                continue;
            }

            if (in_array($key, ['farm_id', 'block_id'], true)) {
                $filters[$key] = max(0, (int) $incoming[$key]);
                continue;
            }

            $filters[$key] = trim((string) $incoming[$key]);
        }

        return $filters;
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
