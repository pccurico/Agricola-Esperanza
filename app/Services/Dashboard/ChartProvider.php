<?php

declare(strict_types=1);

namespace CampoSur\Services\Dashboard;

final class ChartProvider
{
    public function bar(array $labels, array $values, array $options = []): array
    {
        return $this->build('bar', $labels, [
            array_merge([
                'label' => $options['label'] ?? 'Serie',
                'data' => $values,
                'backgroundColor' => $options['backgroundColor'] ?? 'rgba(39, 109, 67, 0.85)',
                'borderColor' => $options['borderColor'] ?? 'rgba(39, 109, 67, 0.95)',
                'borderWidth' => 1,
            ], $options['dataset'] ?? []),
        ], $options['chart'] ?? []);
    }

    public function line(array $labels, array $values, array $options = []): array
    {
        return $this->build('line', $labels, [
            array_merge([
                'label' => $options['label'] ?? 'Serie',
                'data' => $values,
                'backgroundColor' => $options['backgroundColor'] ?? 'rgba(37, 79, 110, 0.16)',
                'borderColor' => $options['borderColor'] ?? 'rgba(37, 79, 110, 0.95)',
                'fill' => true,
                'tension' => 0.35,
            ], $options['dataset'] ?? []),
        ], $options['chart'] ?? []);
    }

    public function build(string $type, array $labels, array $datasets, array $options = []): array
    {
        return [
            'type' => $type,
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets,
            ],
            'options' => $options,
        ];
    }
}
