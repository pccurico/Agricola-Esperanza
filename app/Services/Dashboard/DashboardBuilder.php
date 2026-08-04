<?php

declare(strict_types=1);

namespace AgroPCC\Services\Dashboard;

final class DashboardBuilder
{
    public function __construct(
        private readonly FilterManager $filterManager,
        private readonly WidgetFactory $widgetFactory,
        private readonly ChartProvider $chartProvider,
        private readonly LayoutManager $layoutManager,
        private readonly PermissionResolver $permissionResolver,
        private readonly DashboardDataProviderInterface $dashboardDataProvider,
    ) {
    }

    public function build(array $requestParams, string $department, ?int $userId = null): array
    {
        $filterDefaults = $this->filterManager->defaults();
        $filters = $this->filterManager->resolve(array_merge($filterDefaults, $requestParams));

        $activeView = trim((string) ($requestParams['view'] ?? ''));

        $summary = $this->dashboardDataProvider->summary(
            $requestParams['period'] ?: 'month',
            null,
            $filters,
            $activeView,
            $department,
            $userId,
        );

        $savedViews = $summary['customization']['saved_views'] ?? [];
        $activeView = $this->layoutManager->resolveActiveView($activeView, $savedViews);

        $widgets = $this->widgetFactory->fromSummary($summary, $this->chartProvider);
        $widgets = $this->permissionResolver->filterWidgets($widgets);

        return array_merge($summary, [
            'filters' => $filters,
            'widgets' => $widgets,
            'saved_views' => $savedViews,
            'active_view' => $activeView,
        ]);
    }
}
