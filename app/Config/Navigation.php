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
        ['id' => 'gerencia', 'label' => 'Gerencia', 'description' => 'Información estratégica, dashboards y controles ejecutivos.', 'icon' => 'chart', 'order' => 10, 'visible' => true, 'department' => 'gerencia', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'dashboard-home', 'module' => '', 'label' => 'Resumen ejecutivo', 'route' => '/', 'permission' => 'dashboard.view', 'icon' => 'home', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'reports-module', 'module' => 'reports', 'label' => 'Informes', 'route' => '/reports', 'permission' => 'reports.view', 'icon' => 'chart', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'audit-module', 'module' => 'audit', 'label' => 'Actividad', 'route' => '/audit', 'permission' => 'reports.view', 'icon' => 'chart', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'administracion', 'label' => 'Administración', 'description' => 'Gestión operativa de procesos productivos, compras, equipos y documentos.', 'icon' => 'wrench', 'order' => 20, 'visible' => true, 'department' => 'administracion', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'masters-module', 'module' => 'masters', 'label' => 'Administración', 'route' => '/masters', 'permission' => 'masters.view', 'icon' => 'wrench', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'procurement-module', 'module' => 'procurement', 'label' => 'Compras', 'route' => '/procurement', 'permission' => 'procurement.view', 'icon' => 'cart', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'production-module', 'module' => 'production', 'label' => 'Producción', 'route' => '/production', 'permission' => 'production.view', 'icon' => 'plant', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'machinery-module', 'module' => 'machinery', 'label' => 'Maquinaria', 'route' => '/machinery', 'permission' => 'machinery.view', 'icon' => 'wrench', 'order' => 40, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'planning-module', 'module' => 'planning', 'label' => 'Tareas y Calendario', 'route' => '/planning', 'permissions' => ['tasks.view', 'calendar.view'], 'icon' => 'chart', 'order' => 50, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'documents-module', 'module' => 'documents', 'label' => 'Documentos', 'route' => '/documents', 'permission' => 'documents.view', 'icon' => 'boxes', 'order' => 60, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'contabilidad', 'label' => 'Contabilidad y Finanzas', 'description' => 'Control de costos, presupuestos y análisis financiero.', 'icon' => 'dollar', 'order' => 30, 'visible' => true, 'department' => 'contabilidad', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'costs-module', 'module' => 'costs', 'label' => 'Costos', 'route' => '/costs', 'permission' => 'costs.view', 'icon' => 'dollar', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'budgets-module', 'module' => 'budgets', 'label' => 'Presupuestos', 'route' => '/budgets', 'permission' => 'budgets.view', 'icon' => 'chart', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'rrhh', 'label' => 'RR.HH', 'description' => 'Gestión del personal, cuadrillas y costos laborales.', 'icon' => 'users', 'order' => 40, 'visible' => true, 'department' => 'rrhh', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'labor-module', 'module' => 'labor', 'label' => 'Trabajador', 'route' => '/labor', 'permission' => 'labor.view', 'icon' => 'users', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'bodega', 'label' => 'Bodega', 'description' => 'Inventario, bodegas, solicitudes internas y stock.', 'icon' => 'boxes', 'order' => 50, 'visible' => true, 'department' => 'bodega', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'inventory-module', 'module' => 'inventory', 'label' => 'Inventario', 'route' => '/inventory', 'permission' => 'inventory.view', 'icon' => 'boxes', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'warehouses-module', 'module' => 'warehouses', 'label' => 'Bodegas y Lotes', 'route' => '/warehouses', 'permission' => 'warehouse.view', 'icon' => 'boxes', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'requests-module', 'module' => 'requests', 'label' => 'Solicitudes Internas', 'route' => '/requests', 'permission' => 'requests.view', 'icon' => 'cart', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'receptions-module', 'module' => 'receptions', 'label' => 'Recepciones', 'route' => '/receptions', 'permission' => 'procurement.receive', 'icon' => 'boxes', 'order' => 40, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'notifications-module', 'module' => 'notifications', 'label' => 'Notificaciones', 'route' => '/notifications', 'permission' => 'notifications.view', 'icon' => 'chart', 'order' => 50, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
        ['id' => 'sistema', 'label' => 'Configuración Sistema', 'description' => 'Usuarios, permisos, catálogos y herramientas del sistema.', 'icon' => 'shield', 'order' => 60, 'visible' => true, 'department' => 'sistema', 'badge' => null, 'count' => null, 'items' => [
            ['id' => 'users-system', 'module' => 'users', 'label' => 'Usuarios', 'route' => '/users', 'permissions' => ['users.view', 'users.manage'], 'icon' => 'users', 'order' => 10, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'roles-system', 'module' => 'roles', 'label' => 'Roles', 'route' => '/role', 'permissions' => ['roles.manage', 'users.manage'], 'icon' => 'shield', 'order' => 20, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'settings-system', 'module' => 'settings', 'label' => 'Configuración', 'route' => '/settings', 'permission' => 'setup.manage', 'icon' => 'wrench', 'order' => 30, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'catalogs-system', 'module' => 'catalogs', 'label' => 'Catálogos', 'route' => '/catalogs', 'permission' => 'setup.manage', 'icon' => 'boxes', 'order' => 40, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'api-module', 'module' => 'api', 'label' => 'API e Integraciones', 'route' => '/api', 'permission' => 'api_tokens.manage', 'icon' => 'chart', 'order' => 50, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'tools-center', 'module' => 'tools', 'label' => 'Centro de Herramientas', 'route' => '/tools', 'permission' => 'setup.manage', 'icon' => 'wrench', 'order' => 60, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'demo-tools', 'module' => 'demo', 'label' => 'Demo Data Manager', 'route' => '/demo', 'permission' => 'demo.manage', 'icon' => 'wrench', 'order' => 70, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
            ['id' => 'profile-system', 'module' => 'profile', 'label' => 'Mi perfil', 'route' => '/profile', 'permission' => 'dashboard.view', 'icon' => 'users', 'order' => 80, 'visible' => true, 'badge' => null, 'count' => null, 'favorite' => false],
        ]],
    ],
];
