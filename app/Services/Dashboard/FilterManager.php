<?php

declare(strict_types=1);

namespace AgroPCC\Services\Dashboard;

use AgroPCC\Services\FilterManager as BaseFilterManager;

final class FilterManager extends BaseFilterManager
{
    public function __construct(array $defaults = [], array $options = [])
    {
        parent::__construct(array_merge([
            'date_from' => '',
            'date_to' => '',
            'farm_id' => 0,
            'block_id' => 0,
            'season_id' => 0,
            'cost_center_id' => 0,
            'process' => '',
        ], $defaults), $options);
    }
}
