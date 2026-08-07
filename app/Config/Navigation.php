<?php

declare(strict_types=1);

return [
    'icons' => [
        'home' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z"/></svg>',
        'plant' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21V9m0 4c-4.5 0-7-2.5-7-7 4.5 0 7 2.5 7 7Zm0-2c0-4.5 2.5-7 7-7 0 4.5-2.5 7-7 7Z"/></svg>',
        'cart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6m4 13h.01M18 21h.01"/></svg>',
        'boxes' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 0v9m8-4.5-8 4.5-8-4.5M8 5.3l8 4.5M8 18.7v-4.5l4-2.2 4 2.2v4.5"/></svg>',
        'dollar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 6.5C16.2 5.5 14.7 5 12.8 5 10.1 5 8 6.4 8 8.5s1.8 3 4.5 3.5 4.5 1.4 4.5 3.5-2.1 3.5-4.8 3.5c-2 0-3.6-.6-4.7-1.8"/></svg>',
        'chart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5m0 14h17M8 16v-5m4 5V7m4 9v-8m4 8v-4"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.4-5 6-5s6 1.7 6 5M16 5.5a3 3 0 0 1 0 5.8M18 15c2 .6 3 2.1 3 5"/></svg>',
        'shield' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8 3v5c0 5-3.4 8.6-8 10-4.6-1.4-8-5-8-10V6l8-3Zm-3 9 2 2 4-4"/></svg>',
        'wrench' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14.7 6.3 3-3a5 5 0 0 0-6.4 6.4l-7.6 7.6a2.1 2.1 0 1 0 3 3l7.6-7.6a5 5 0 0 0 6.4-6.4l-3 3-3-3Z"/></svg>',
        'chevron' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>',
        'search' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg>',
        'star' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>',
        'menu' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>',
            'plus' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>',
            'bell' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0"/></svg>',
    ],
    'groups' => [
        ['id' => 'produccion', 'label' => 'Producción', 'description' => 'Predios, cuarteles, labores y cosecha.', 'icon' => 'plant', 'order' => 20, 'visible' => true, 'department' => 'produccion', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'production-dashboard', 'module' => 'production', 'label' => 'Producción', 'route' => '/production', 'permission' => 'production.view', 'icon' => 'chart', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'master-farms', 'module' => 'masters', 'label' => 'Predios', 'route' => '/masters', 'permission' => 'masters.view', 'icon' => 'plant', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'master-blocks', 'module' => 'masters', 'label' => 'Cuarteles', 'route' => '/masters', 'permission' => 'masters.view', 'icon' => 'plant', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'labor-module', 'module' => 'labor', 'label' => 'Labores', 'route' => '/labor', 'permission' => 'labor.view', 'icon' => 'users', 'order' => 40, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'harvest-module', 'module' => 'production', 'label' => 'Cosecha', 'route' => '/production', 'permission' => 'production.view', 'icon' => 'chart', 'order' => 50, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'bodega', 'label' => 'Bodega e Inventario', 'description' => 'Inventario, productos, movimientos y alertas.', 'icon' => 'boxes', 'order' => 30, 'visible' => true, 'department' => 'bodega', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'inventory-module', 'module' => 'inventory', 'label' => 'Inventario', 'route' => '/inventory', 'permission' => 'inventory.view', 'icon' => 'boxes', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'products-module', 'module' => 'inventory', 'label' => 'Productos', 'route' => '/inventory', 'permission' => 'inventory.view', 'icon' => 'boxes', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'warehouses-module', 'module' => 'warehouses', 'label' => 'Movimientos', 'route' => '/warehouses', 'permission' => 'warehouse.view', 'icon' => 'boxes', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'notifications-module', 'module' => 'notifications', 'label' => 'Alertas', 'route' => '/notifications', 'permission' => 'notifications.view', 'icon' => 'chart', 'order' => 40, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'compras', 'label' => 'Compras', 'description' => 'Solicitudes, proveedores y recepciones.', 'icon' => 'cart', 'order' => 40, 'visible' => true, 'department' => 'compras', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'requests-module', 'module' => 'requests', 'label' => 'Solicitudes', 'route' => '/requests', 'permission' => 'requests.view', 'icon' => 'cart', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'suppliers-module', 'module' => 'procurement', 'label' => 'Proveedores', 'route' => '/procurement', 'permission' => 'procurement.view', 'icon' => 'cart', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'receptions-module', 'module' => 'receptions', 'label' => 'Recepciones', 'route' => '/receptions', 'permission' => 'procurement.receive', 'icon' => 'boxes', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'finanzas', 'label' => 'Finanzas', 'description' => 'Costos, presupuestos y reportes.', 'icon' => 'dollar', 'order' => 50, 'visible' => true, 'department' => 'finanzas', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'finance-dashboard', 'module' => 'reports', 'label' => 'Dashboard Financiero', 'route' => '/reports', 'permission' => 'reports.view', 'icon' => 'chart', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'costs-module', 'module' => 'costs', 'label' => 'Costos', 'route' => '/costs', 'permission' => 'costs.view', 'icon' => 'dollar', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'budgets-module', 'module' => 'budgets', 'label' => 'Presupuestos', 'route' => '/budgets', 'permission' => 'budgets.view', 'icon' => 'chart', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'rrhh', 'label' => 'Recursos Humanos', 'description' => 'Trabajadores, mano de obra y asistencia.', 'icon' => 'users', 'order' => 60, 'visible' => true, 'department' => 'rrhh', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'labor-module', 'module' => 'labor', 'label' => 'Trabajadores', 'route' => '/labor', 'permission' => 'labor.view', 'icon' => 'users', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'workforce-module', 'module' => 'labor', 'label' => 'Mano de obra', 'route' => '/labor', 'permission' => 'labor.view', 'icon' => 'users', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'attendance-module', 'module' => 'planning', 'label' => 'Asistencia', 'route' => '/planning', 'permissions' => ['tasks.view', 'calendar.view'], 'icon' => 'chart', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'administracion', 'label' => 'Administración', 'description' => 'Empresa, usuarios, configuración y documentos.', 'icon' => 'wrench', 'order' => 70, 'visible' => true, 'department' => 'administracion', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'masters-module', 'module' => 'masters', 'label' => 'Empresa', 'route' => '/masters', 'permission' => 'masters.view', 'icon' => 'wrench', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'users-system', 'module' => 'users', 'label' => 'Usuarios', 'route' => '/users', 'permissions' => ['users.view', 'users.manage'], 'icon' => 'users', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'settings-system', 'module' => 'settings', 'label' => 'Configuración', 'route' => '/settings', 'permission' => 'setup.manage', 'icon' => 'wrench', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'documents-module', 'module' => 'documents', 'label' => 'Documentos', 'route' => '/documents', 'permission' => 'documents.view', 'icon' => 'boxes', 'order' => 40, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'gerencia', 'label' => 'Gerencia', 'description' => 'Centro de inteligencia, reportes y análisis de procesos.', 'icon' => 'chart', 'order' => 10, 'visible' => true, 'department' => 'gerencia', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'dashboard-home', 'module' => '', 'label' => 'Resumen ejecutivo', 'route' => '/', 'permission' => 'dashboard.view', 'icon' => 'home', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'management-intelligence', 'module' => 'intelligence', 'label' => 'Centro de Inteligencia Gerencial', 'route' => '/intelligence', 'permission' => 'dashboard.view', 'icon' => 'chart', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'executive-center', 'module' => 'reports', 'label' => 'Reportes gerenciales', 'route' => '/reports', 'permission' => 'reports.view', 'icon' => 'chart', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'process-analytics', 'module' => 'reports', 'label' => 'Análisis de procesos', 'route' => '/reports/trends', 'permission' => 'reports.view', 'icon' => 'chart', 'order' => 40, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
    ],
];
