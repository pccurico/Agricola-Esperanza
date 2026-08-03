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
    ],
    'groups' => [
        ['id' => 'home', 'label' => 'Inicio', 'description' => 'Accesos personales y resumen ejecutivo.', 'icon' => 'home', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'dashboard-home', 'module' => '', 'label' => 'Dashboard', 'route' => '/', 'permission' => 'dashboard.view', 'icon' => 'home', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'warehouse-department', 'label' => 'Bodega', 'description' => 'Inventario, bodegas, lotes, solicitudes y abastecimiento.', 'icon' => 'boxes', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'inventory-module', 'module' => 'inventory', 'label' => 'Inventario', 'route' => '?module=inventory', 'permission' => 'inventory.view', 'icon' => 'boxes', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'warehouses-module', 'module' => 'warehouses', 'label' => 'Bodegas y Lotes', 'route' => '?module=warehouses', 'permission' => 'warehouse.view', 'icon' => 'boxes', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'requests-module', 'module' => 'requests', 'label' => 'Solicitudes Internas', 'route' => '?module=requests', 'permission' => 'requests.view', 'icon' => 'cart', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'procurement-module', 'module' => 'procurement', 'label' => 'Compras y Abastecimiento', 'route' => '?module=procurement', 'permission' => 'procurement.view', 'icon' => 'cart', 'order' => 40, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'receptions-module', 'module' => 'receptions', 'label' => 'Recepciones', 'route' => '?module=receptions', 'permission' => 'procurement.receive', 'icon' => 'boxes', 'order' => 50, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'notifications-module', 'module' => 'notifications', 'label' => 'Notificaciones', 'route' => '?module=notifications', 'permission' => 'notifications.view', 'icon' => 'chart', 'order' => 60, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'hr-accounting', 'label' => 'RRHH / Contabilidad', 'description' => 'Personas, mano de obra, costos y control presupuestario.', 'icon' => 'users', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'labor-module', 'module' => 'labor', 'label' => 'Mano de Obra y Trabajadores', 'route' => '?module=labor', 'permission' => 'labor.view', 'icon' => 'users', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'costs-module', 'module' => 'costs', 'label' => 'Costos', 'route' => '?module=costs', 'permission' => 'costs.view', 'icon' => 'dollar', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'budgets-module', 'module' => 'budgets', 'label' => 'Presupuestos', 'route' => '?module=budgets', 'permission' => 'budgets.view', 'icon' => 'chart', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'administration', 'label' => 'Administración', 'description' => 'Operación agrícola, planificación, activos y documentos.', 'icon' => 'plant', 'order' => 40, 'visible' => true, 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'production-module', 'module' => 'production', 'label' => 'Producción', 'route' => '?module=production', 'permission' => 'production.view', 'icon' => 'plant', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'planning-module', 'module' => 'planning', 'label' => 'Tareas y Calendario', 'route' => '?module=planning', 'permissions' => ['tasks.view', 'calendar.view'], 'icon' => 'chart', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'machinery-module', 'module' => 'machinery', 'label' => 'Maquinaria', 'route' => '?module=machinery', 'permission' => 'machinery.view', 'icon' => 'wrench', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'documents-module', 'module' => 'documents', 'label' => 'Documentos', 'route' => '?module=documents', 'permission' => 'documents.view', 'icon' => 'boxes', 'order' => 40, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'masters-module', 'module' => 'masters', 'label' => 'Maestros Agrícolas', 'route' => '?module=masters', 'permission' => 'masters.view', 'icon' => 'wrench', 'order' => 50, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'management', 'label' => 'Gerencia', 'description' => 'Indicadores ejecutivos, informes y trazabilidad de actividad.', 'icon' => 'chart', 'order' => 50, 'visible' => true, 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'reports-module', 'module' => 'reports', 'label' => 'Dashboard Ejecutivo e Informes', 'route' => '?module=reports', 'permission' => 'reports.view', 'icon' => 'chart', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'audit-module', 'module' => 'audit', 'label' => 'Actividad del Sistema', 'route' => '?module=audit', 'permission' => 'reports.view', 'icon' => 'chart', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'system-administration', 'label' => 'Administración del Sistema', 'description' => 'Usuarios, roles, catálogos, configuración e integraciones.', 'icon' => 'shield', 'order' => 60, 'visible' => true, 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'users-system', 'module' => 'users', 'label' => 'Usuarios y Roles', 'route' => '?module=users', 'permissions' => ['users.view', 'users.manage', 'roles.manage'], 'icon' => 'users', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'catalogs-system', 'module' => 'catalogs', 'label' => 'Catálogos', 'route' => '?module=catalogs', 'permission' => 'setup.manage', 'icon' => 'boxes', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'settings-system', 'module' => 'settings', 'label' => 'Configuración', 'route' => '?module=settings', 'permission' => 'setup.manage', 'icon' => 'wrench', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'api-system', 'module' => 'api', 'label' => 'API e Integraciones', 'route' => '?module=api', 'permission' => 'api_tokens.manage', 'icon' => 'chart', 'order' => 40, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'tools', 'label' => 'Herramientas', 'description' => 'Utilidades de soporte y demostración.', 'icon' => 'wrench', 'order' => 70, 'visible' => true, 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'demo-tools', 'module' => 'demo', 'label' => 'Demo Data Manager', 'route' => '?module=demo', 'permission' => 'demo.manage', 'icon' => 'wrench', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
    ],
];
