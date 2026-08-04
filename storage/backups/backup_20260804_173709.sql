-- Respaldo de base de datos generado por PHP
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `api_tokens`;
CREATE TABLE `api_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_api_tokens_hash` (`token_hash`),
  KEY `fk_api_tokens_company` (`company_id`),
  KEY `fk_api_tokens_user` (`user_id`),
  CONSTRAINT `fk_api_tokens_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_api_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `attachments`;
CREATE TABLE `attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `document_id` bigint unsigned DEFAULT NULL,
  `entity_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint unsigned NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_attachments_document` (`document_id`),
  KEY `fk_attachments_user` (`uploaded_by`),
  KEY `idx_attachments_entity` (`company_id`,`entity_type`,`entity_id`),
  CONSTRAINT `fk_attachments_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attachments_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_attachments_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  `details` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_audit_user` (`user_id`),
  KEY `idx_audit_company_date` (`company_id`,`created_at`),
  CONSTRAINT `fk_audit_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `audit_logs` WRITE;
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('1', '1', '1', 'ERROR', 'tools', NULL, '{"error": "No se encontró mysqldump; no se puede crear el respaldo automático."}', '::1', '2026-08-03 14:59:26');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('2', '1', '1', 'ERROR', 'tools', NULL, '{"error": "No se encontró mysqldump; no se puede crear el respaldo automático."}', NULL, '2026-08-03 15:02:35');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('3', '1', '1', 'ERROR', 'tools', NULL, '{"error": "No se encontró mysqldump; no se puede crear el respaldo automático."}', NULL, '2026-08-03 15:02:58');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('4', '1', '1', 'ERROR', 'tools', NULL, '{"error": "No se encontró mysqldump; no se puede crear el respaldo automático."}', '::1', '2026-08-03 15:04:17');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('5', '1', '1', 'ERROR', 'tools', NULL, '{"error": "No se encontró mysqldump; no se puede crear el respaldo automático."}', '::1', '2026-08-03 15:08:46');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('6', '1', '1', 'CREATE', 'user', NULL, NULL, '::1', '2026-08-03 17:24:28');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('7', '1', '1', 'CREATE', 'role', NULL, NULL, '::1', '2026-08-03 17:25:56');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('8', '1', '1', 'DEACTIVATE', 'user', NULL, NULL, '::1', '2026-08-03 17:26:06');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('9', '1', '1', 'ACTIVATE', 'user', NULL, NULL, '::1', '2026-08-03 17:27:02');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('10', '1', '1', 'DEACTIVATE', 'user', NULL, NULL, '::1', '2026-08-03 17:28:31');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('11', '1', '1', 'ACTIVATE', 'user', NULL, NULL, '::1', '2026-08-03 17:28:54');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('12', '1', '1', 'UPDATE', 'role', NULL, NULL, '::1', '2026-08-03 17:40:57');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('13', '1', '1', 'UPDATE', 'role', NULL, NULL, '::1', '2026-08-03 17:44:00');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('14', '1', '1', 'UPDATE', 'role', NULL, NULL, '::1', '2026-08-03 17:44:31');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('15', '1', '1', 'CREATE', 'role', NULL, NULL, '::1', '2026-08-03 17:46:39');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('16', '1', '1', 'CREATE', 'role', NULL, NULL, '::1', '2026-08-03 17:47:25');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('17', '1', '1', 'ERROR', 'tools', NULL, '{"error": "No se encontró mysqldump; no se puede crear el respaldo automático."}', '::1', '2026-08-03 18:06:25');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('18', '1', '1', 'UPDATE', 'user', NULL, NULL, '::1', '2026-08-03 18:30:25');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('19', '1', '1', 'UPDATE', 'user', NULL, NULL, '::1', '2026-08-03 21:32:37');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('20', '1', '1', 'UPDATE', 'role', NULL, NULL, '::1', '2026-08-03 21:34:03');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('21', '1', '1', 'CREATE', 'role', NULL, NULL, '::1', '2026-08-04 12:11:30');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('22', '1', '1', 'EXPORT', 'reports', NULL, '{"format": "xlsx"}', '::1', '2026-08-04 12:25:24');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('23', '1', '1', 'EXPORT', 'reports', NULL, '{"format": "pdf", "report": "executive"}', '::1', '2026-08-04 12:57:31');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('24', '1', '1', 'EXPORT', 'reports', NULL, '{"format": "xlsx", "report": "executive"}', '::1', '2026-08-04 12:57:48');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('25', '1', '1', 'EXPORT', 'reports', NULL, '{"format": "csv", "report": "executive"}', '::1', '2026-08-04 12:59:07');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('26', '1', '1', 'EXPORT', 'reports', NULL, '{"format": "pdf", "report": "executive"}', '::1', '2026-08-04 13:06:39');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES ('27', '1', '1', 'EXPORT', 'reports', NULL, '{"format": "pdf", "report": "executive"}', '::1', '2026-08-04 13:09:31');
UNLOCK TABLES;

DROP TABLE IF EXISTS `backup_records`;
CREATE TABLE `backup_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL,
  `checksum` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'STARTED',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_backups_company` (`company_id`),
  KEY `fk_backups_user` (`created_by`),
  CONSTRAINT `fk_backups_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_backups_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `backup_records` WRITE;
INSERT INTO `backup_records` (`id`, `company_id`, `file_path`, `file_size`, `checksum`, `status`, `created_by`, `created_at`) VALUES ('1', '1', '', '0', 'No se encontró mysqldump en el entorno.', 'FAILED', '1', '2026-08-03 14:59:25');
INSERT INTO `backup_records` (`id`, `company_id`, `file_path`, `file_size`, `checksum`, `status`, `created_by`, `created_at`) VALUES ('2', '1', '', '0', 'No se encontró mysqldump en el entorno.', 'FAILED', '1', '2026-08-03 15:02:24');
INSERT INTO `backup_records` (`id`, `company_id`, `file_path`, `file_size`, `checksum`, `status`, `created_by`, `created_at`) VALUES ('3', '1', '', '0', 'No se encontró mysqldump en el entorno.', 'FAILED', '1', '2026-08-03 15:02:35');
INSERT INTO `backup_records` (`id`, `company_id`, `file_path`, `file_size`, `checksum`, `status`, `created_by`, `created_at`) VALUES ('4', '1', '', '0', 'No se encontró mysqldump en el entorno.', 'FAILED', '1', '2026-08-03 15:02:58');
INSERT INTO `backup_records` (`id`, `company_id`, `file_path`, `file_size`, `checksum`, `status`, `created_by`, `created_at`) VALUES ('5', '1', '', '0', 'No se encontró mysqldump en el entorno.', 'FAILED', '1', '2026-08-03 15:04:17');
INSERT INTO `backup_records` (`id`, `company_id`, `file_path`, `file_size`, `checksum`, `status`, `created_by`, `created_at`) VALUES ('6', '1', '', '0', 'No se encontró mysqldump en el entorno.', 'FAILED', '1', '2026-08-03 15:08:46');
INSERT INTO `backup_records` (`id`, `company_id`, `file_path`, `file_size`, `checksum`, `status`, `created_by`, `created_at`) VALUES ('7', '1', '', '0', 'No se encontró mysqldump en el entorno.', 'FAILED', '1', '2026-08-03 18:06:25');
INSERT INTO `backup_records` (`id`, `company_id`, `file_path`, `file_size`, `checksum`, `status`, `created_by`, `created_at`) VALUES ('8', '1', 'storage/backups/backup_20260803_180725.sql', '263073', '40f0d948daa68c0333c01dbff7120272d80cff5f254599dbf8fe6706aea24ba1', 'COMPLETED', '1', '2026-08-03 18:07:25');
INSERT INTO `backup_records` (`id`, `company_id`, `file_path`, `file_size`, `checksum`, `status`, `created_by`, `created_at`) VALUES ('9', '1', '', '0', NULL, 'STARTED', '1', '2026-08-04 17:37:09');
UNLOCK TABLES;

DROP TABLE IF EXISTS `blocks`;
CREATE TABLE `blocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `farm_id` bigint unsigned NOT NULL,
  `species_id` bigint unsigned DEFAULT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hectares` decimal(12,2) NOT NULL DEFAULT '0.00',
  `planting_year` smallint unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blocks_farm_code` (`farm_id`,`code`),
  KEY `fk_blocks_species` (`species_id`),
  KEY `idx_blocks_company` (`company_id`,`active`),
  CONSTRAINT `fk_blocks_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_blocks_farm` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`),
  CONSTRAINT `fk_blocks_species` FOREIGN KEY (`species_id`) REFERENCES `species` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `blocks` WRITE;
INSERT INTO `blocks` (`id`, `company_id`, `farm_id`, `species_id`, `code`, `name`, `hectares`, `planting_year`, `active`, `created_at`, `updated_at`) VALUES ('31', '1', '11', '16', 'CN-01', 'Cerezo Norte 1', '22.40', '2018', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `blocks` (`id`, `company_id`, `farm_id`, `species_id`, `code`, `name`, `hectares`, `planting_year`, `active`, `created_at`, `updated_at`) VALUES ('32', '1', '11', '16', 'CN-02', 'Cerezo Norte 2', '25.10', '2019', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `blocks` (`id`, `company_id`, `farm_id`, `species_id`, `code`, `name`, `hectares`, `planting_year`, `active`, `created_at`, `updated_at`) VALUES ('33', '1', '11', '17', 'UN-01', 'Parronal Norte', '31.00', '2017', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `blocks` (`id`, `company_id`, `farm_id`, `species_id`, `code`, `name`, `hectares`, `planting_year`, `active`, `created_at`, `updated_at`) VALUES ('34', '1', '12', '18', 'MR-01', 'Manzanos Río 1', '28.60', '2016', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `blocks` (`id`, `company_id`, `farm_id`, `species_id`, `code`, `name`, `hectares`, `planting_year`, `active`, `created_at`, `updated_at`) VALUES ('35', '1', '12', '18', 'MR-02', 'Manzanos Río 2', '30.20', '2020', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `blocks` (`id`, `company_id`, `farm_id`, `species_id`, `code`, `name`, `hectares`, `planting_year`, `active`, `created_at`, `updated_at`) VALUES ('36', '1', '12', '16', 'CR-01', 'Cerezos Río', '18.40', '2019', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `budgets`;
CREATE TABLE `budgets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `season_id` bigint unsigned NOT NULL,
  `cost_center_id` bigint unsigned NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_budgets_scope` (`company_id`,`season_id`,`cost_center_id`,`period_start`,`period_end`),
  KEY `fk_budgets_season` (`season_id`),
  KEY `fk_budgets_center` (`cost_center_id`),
  KEY `fk_budgets_user` (`created_by`),
  KEY `idx_budgets_period_scope` (`company_id`,`period_start`,`period_end`,`season_id`,`cost_center_id`),
  CONSTRAINT `fk_budgets_center` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`),
  CONSTRAINT `fk_budgets_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_budgets_season` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`),
  CONSTRAINT `fk_budgets_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `budgets` WRITE;
INSERT INTO `budgets` (`id`, `company_id`, `season_id`, `cost_center_id`, `period_start`, `period_end`, `amount`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('13', '1', '12', '25', '2025-07-01', '2026-06-30', '18500000.00', 'APPROVED', 'Presupuesto anual de producción', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `budgets` (`id`, `company_id`, `season_id`, `cost_center_id`, `period_start`, `period_end`, `amount`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('14', '1', '12', '26', '2025-07-01', '2026-06-30', '7200000.00', 'APPROVED', 'Energía y mantención de riego', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `budgets` (`id`, `company_id`, `season_id`, `cost_center_id`, `period_start`, `period_end`, `amount`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('15', '1', '12', '28', '2025-07-01', '2026-06-30', '4100000.00', 'APPROVED', 'Insumos y embalajes de bodega', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `budgets` (`id`, `company_id`, `season_id`, `cost_center_id`, `period_start`, `period_end`, `amount`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('16', '1', '12', '27', '2025-07-01', '2026-06-30', '2850000.00', 'APPROVED', 'Servicios profesionales y administración', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `calendar_events`;
CREATE TABLE `calendar_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `event_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `farm_id` bigint unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_calendar_user` (`created_by`),
  KEY `fk_calendar_farm` (`farm_id`),
  KEY `idx_calendar_dates` (`company_id`,`starts_at`,`ends_at`),
  CONSTRAINT `fk_calendar_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_calendar_farm` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_calendar_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `calendar_events` WRITE;
INSERT INTO `calendar_events` (`id`, `company_id`, `created_by`, `title`, `description`, `starts_at`, `ends_at`, `event_type`, `farm_id`, `created_at`) VALUES ('13', '1', '1', 'Revisión de riego Campo Norte', 'Revisar presión y funcionamiento de sectores.', '2025-10-10 09:00:00', '2025-10-10 11:00:00', 'RIEGO', '11', '2026-08-03 16:52:22');
INSERT INTO `calendar_events` (`id`, `company_id`, `created_by`, `title`, `description`, `starts_at`, `ends_at`, `event_type`, `farm_id`, `created_at`) VALUES ('14', '1', '1', 'Planificación de cosecha', 'Reunión de coordinación con cuadrillas.', '2025-12-02 15:00:00', '2025-12-02 16:30:00', 'REUNION', '12', '2026-08-03 16:52:22');
INSERT INTO `calendar_events` (`id`, `company_id`, `created_by`, `title`, `description`, `starts_at`, `ends_at`, `event_type`, `farm_id`, `created_at`) VALUES ('15', '1', '1', 'Control de inventario de bodega', 'Validar lotes y fechas de vencimiento.', '2025-10-14 10:30:00', '2025-10-14 12:00:00', 'INVENTARIO', '11', '2026-08-03 16:52:22');
INSERT INTO `calendar_events` (`id`, `company_id`, `created_by`, `title`, `description`, `starts_at`, `ends_at`, `event_type`, `farm_id`, `created_at`) VALUES ('16', '1', '1', 'Seguimiento de compra de insumos', 'Revisión de ordenes y recepciones pendientes.', '2025-10-22 13:00:00', '2025-10-22 14:30:00', 'COMPRAS', '12', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `tax_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_name` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_clients_company_tax_id` (`company_id`,`tax_id`),
  CONSTRAINT `fk_clients_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `clients` WRITE;
INSERT INTO `clients` (`id`, `company_id`, `tax_id`, `business_name`, `contact_name`, `email`, `phone`, `active`, `created_at`, `updated_at`) VALUES ('9', '1', '96.111.222-1', 'Frutas Andinas S.A.', 'Claudia Araya', 'compras@frutasandinas.cl', '+56223456789', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `clients` (`id`, `company_id`, `tax_id`, `business_name`, `contact_name`, `email`, `phone`, `active`, `created_at`, `updated_at`) VALUES ('10', '1', '96.222.333-2', 'Exportadora Central Ltda.', 'Mauricio León', 'abastecimiento@exportadoracentral.cl', '+56224567890', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `legal_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trade_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commune` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `companies` WRITE;
INSERT INTO `companies` (`id`, `legal_name`, `trade_name`, `tax_id`, `logo_path`, `email`, `phone`, `address`, `commune`, `region`, `active`, `created_at`, `updated_at`) VALUES ('1', 'AGRICOLA LA ESPERANZA', 'ESPERANZA LONTUE', '77.338.580-7', 'storage/uploads/company-logo-285a145ae299932c65684fe8.png', 'lidia@esperanzalontue.cl', '+56 9 8380 8034', NULL, 'MOLINA', 'MAULE', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
UNLOCK TABLES;

DROP TABLE IF EXISTS `company_settings`;
CREATE TABLE `company_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_company_settings_key` (`company_id`,`setting_key`),
  CONSTRAINT `fk_company_settings_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `company_settings` WRITE;
INSERT INTO `company_settings` (`id`, `company_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES ('1', '1', 'dashboard.user.1.view.g', '{"filters":{"date_from":"2026-08-01","date_to":"2026-08-03","process":"","farm_id":0,"block_id":0},"widgets":[],"label":"g"}', '2026-08-03 17:32:02', '2026-08-03 17:32:02');
INSERT INTO `company_settings` (`id`, `company_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES ('2', '1', 'dashboard.user.1.active_view', 'g', '2026-08-03 17:32:02', '2026-08-03 17:32:02');
UNLOCK TABLES;

DROP TABLE IF EXISTS `contractors`;
CREATE TABLE `contractors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `business_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_name` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contractors_company_tax_id` (`company_id`,`tax_id`),
  CONSTRAINT `fk_contractors_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `contractors` WRITE;
INSERT INTO `contractors` (`id`, `company_id`, `business_name`, `tax_id`, `contact_name`, `phone`, `active`, `created_at`) VALUES ('9', '1', 'Servicios Rurales Maule SpA', '77.444.555-6', 'Sergio Molina', '+56994567890', '1', '2026-08-03 16:52:22');
INSERT INTO `contractors` (`id`, `company_id`, `business_name`, `tax_id`, `contact_name`, `phone`, `active`, `created_at`) VALUES ('10', '1', 'Transportes Cosecha Ltda.', '76.555.666-7', 'Daniela Fuentes', '+56995678901', '1', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `cost_centers`;
CREATE TABLE `cost_centers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cost_centers_company_code` (`company_id`,`code`),
  CONSTRAINT `fk_cost_centers_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `cost_centers` WRITE;
INSERT INTO `cost_centers` (`id`, `company_id`, `code`, `name`, `category`, `active`) VALUES ('1', '1', 'ADM-001', 'Administración general', 'ADMINISTRACION', '1');
INSERT INTO `cost_centers` (`id`, `company_id`, `code`, `name`, `category`, `active`) VALUES ('2', '1', 'MO-001', 'Mano de obra agrícola', 'MANO_DE_OBRA', '1');
INSERT INTO `cost_centers` (`id`, `company_id`, `code`, `name`, `category`, `active`) VALUES ('3', '1', 'INV-001', 'Inversiones y proyectos', 'INVERSION', '1');
INSERT INTO `cost_centers` (`id`, `company_id`, `code`, `name`, `category`, `active`) VALUES ('4', '1', 'SG-001', 'Servicios y gastos generales', 'SERVICIOS_GASTOS', '1');
INSERT INTO `cost_centers` (`id`, `company_id`, `code`, `name`, `category`, `active`) VALUES ('5', '1', 'BOD-001', 'Bodega e insumos', 'BODEGA', '1');
INSERT INTO `cost_centers` (`id`, `company_id`, `code`, `name`, `category`, `active`) VALUES ('25', '1', 'DEMO-PROD-001', 'Producción agrícola', 'INVERSION', '1');
INSERT INTO `cost_centers` (`id`, `company_id`, `code`, `name`, `category`, `active`) VALUES ('26', '1', 'DEMO-RIE-001', 'Riego y energía', 'SERVICIOS_GASTOS', '1');
INSERT INTO `cost_centers` (`id`, `company_id`, `code`, `name`, `category`, `active`) VALUES ('27', '1', 'DEMO-ADM-001', 'Administración', 'ADMINISTRACION', '1');
INSERT INTO `cost_centers` (`id`, `company_id`, `code`, `name`, `category`, `active`) VALUES ('28', '1', 'DEMO-BOD-001', 'Bodega e insumos', 'BODEGA', '1');
UNLOCK TABLES;

DROP TABLE IF EXISTS `crew_workers`;
CREATE TABLE `crew_workers` (
  `crew_id` bigint unsigned NOT NULL,
  `worker_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`crew_id`,`worker_id`),
  KEY `fk_crew_workers_worker` (`worker_id`),
  CONSTRAINT `fk_crew_workers_crew` FOREIGN KEY (`crew_id`) REFERENCES `crews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_crew_workers_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `crews`;
CREATE TABLE `crews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supervisor_id` bigint unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crews_company_name` (`company_id`,`name`),
  KEY `fk_crews_supervisor` (`supervisor_id`),
  CONSTRAINT `fk_crews_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_crews_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `workers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `crews` WRITE;
INSERT INTO `crews` (`id`, `company_id`, `name`, `supervisor_id`, `active`, `created_at`) VALUES ('9', '1', 'Cuadrilla Campo Norte', '30', '1', '2026-08-03 16:52:22');
INSERT INTO `crews` (`id`, `company_id`, `name`, `supervisor_id`, `active`, `created_at`) VALUES ('10', '1', 'Cuadrilla Fundo Río', '34', '1', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `demo_batches`;
CREATE TABLE `demo_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `installation_id` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INSTALLED',
  `installed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `removed_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_demo_batches_user` (`created_by`),
  KEY `idx_demo_batches_company_status` (`company_id`,`status`,`installed_at`),
  CONSTRAINT `fk_demo_batches_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_demo_batches_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `demo_batches` WRITE;
INSERT INTO `demo_batches` (`id`, `company_id`, `installation_id`, `version`, `status`, `installed_at`, `removed_at`, `created_by`) VALUES ('2', '1', 'ee1d40e20d4ce377a92934fd0295ec53', '1.0', 'REMOVED', '2026-07-28 03:48:30', '2026-07-28 03:48:39', '1');
INSERT INTO `demo_batches` (`id`, `company_id`, `installation_id`, `version`, `status`, `installed_at`, `removed_at`, `created_by`) VALUES ('3', '1', 'c69b1936c40a06a71ffbb1efa07726f0', '1.0', 'REMOVED', '2026-07-28 03:48:44', '2026-08-03 16:37:06', '1');
INSERT INTO `demo_batches` (`id`, `company_id`, `installation_id`, `version`, `status`, `installed_at`, `removed_at`, `created_by`) VALUES ('4', '1', '0e304ca868b68e787c377a415ac6f26f', '1.0', 'REMOVED', '2026-08-03 16:37:06', '2026-08-03 16:42:28', '1');
INSERT INTO `demo_batches` (`id`, `company_id`, `installation_id`, `version`, `status`, `installed_at`, `removed_at`, `created_by`) VALUES ('5', '1', 'fb1e47b276250009abd3bfdd1c8d52dd', '1.0', 'REMOVED', '2026-08-03 16:43:55', '2026-08-03 16:52:22', '1');
INSERT INTO `demo_batches` (`id`, `company_id`, `installation_id`, `version`, `status`, `installed_at`, `removed_at`, `created_by`) VALUES ('6', '1', '83ef87fa2e3f699ee6bf8ccc9346a65a', '1.0', 'INSTALLED', '2026-08-03 16:52:22', NULL, '1');
UNLOCK TABLES;

DROP TABLE IF EXISTS `demo_records`;
CREATE TABLE `demo_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` bigint unsigned NOT NULL,
  `table_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` bigint unsigned NOT NULL,
  `record_key` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_demo_records_batch_key` (`batch_id`,`record_key`),
  UNIQUE KEY `uq_demo_records_table_id` (`batch_id`,`table_name`,`record_id`),
  KEY `idx_demo_records_lookup` (`table_name`,`record_id`),
  CONSTRAINT `fk_demo_records_batch` FOREIGN KEY (`batch_id`) REFERENCES `demo_batches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=616 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `demo_records` WRITE;
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('16', '2', 'farms', '3', 'campo-norte', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('17', '2', 'farms', '4', 'fundo-rio', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('18', '2', 'species', '4', 'cerezo', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('19', '2', 'species', '5', 'uva', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('20', '2', 'species', '6', 'manzano', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('21', '2', 'seasons', '3', 'temporada-2024-2025', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('22', '2', 'seasons', '4', 'temporada-2025-2026', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('23', '2', 'blocks', '7', 'norte-cerezo-01', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('24', '2', 'blocks', '8', 'norte-cerezo-02', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('25', '2', 'blocks', '9', 'norte-uva-01', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('26', '2', 'blocks', '10', 'rio-manzano-01', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('27', '2', 'blocks', '11', 'rio-manzano-02', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('28', '2', 'blocks', '12', 'rio-cerezo-01', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('29', '2', 'cost_centers', '9', 'produccion', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('30', '2', 'cost_centers', '10', 'riego', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('31', '2', 'cost_centers', '11', 'administracion', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('32', '2', 'cost_centers', '12', 'bodega', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('33', '2', 'workers', '1', 'ana', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('34', '2', 'workers', '2', 'carlos', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('35', '2', 'workers', '3', 'maria', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('36', '2', 'workers', '4', 'jorge', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('37', '2', 'workers', '5', 'patricia', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('38', '2', 'workers', '6', 'rodrigo', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('39', '2', 'inventory_items', '1', 'fertilizante', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('40', '2', 'inventory_items', '2', 'azufre', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('41', '2', 'inventory_items', '3', 'cinta-riego', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('42', '2', 'inventory_items', '4', 'caja-cosecha', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('43', '2', 'inventory_items', '5', 'guante', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('44', '2', 'inventory_items', '6', 'aceite', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('45', '2', 'warehouses', '1', 'bodega-norte', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('46', '2', 'warehouses', '2', 'bodega-rio', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('47', '2', 'warehouse_locations', '1', 'norte-insumos', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('48', '2', 'warehouse_locations', '2', 'norte-epp', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('49', '2', 'warehouse_locations', '3', 'rio-insumos', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('50', '2', 'suppliers', '1', 'agro-sur', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('51', '2', 'suppliers', '2', 'riego-tec', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('52', '2', 'suppliers', '3', 'maquinaria-maule', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('53', '2', 'clients', '1', 'frutas-andinas', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('54', '2', 'clients', '2', 'exportadora-central', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('55', '2', 'contractors', '1', 'servicios-rurales', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('56', '2', 'contractors', '2', 'transporte-cosecha', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('57', '2', 'machinery', '1', 'tractor-01', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('58', '2', 'machinery', '2', 'tractor-02', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('59', '2', 'machinery', '3', 'pulverizador', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('60', '2', 'crews', '1', 'cuadrilla-norte', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('61', '2', 'crews', '2', 'cuadrilla-rio', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('62', '2', 'purchase_orders', '1', 'oc-2025-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('63', '2', 'purchase_orders', '2', 'oc-2025-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('64', '2', 'purchase_orders', '3', 'oc-2025-003', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('65', '2', 'purchase_order_items', '1', 'oci-fertilizante', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('66', '2', 'purchase_order_items', '2', 'oci-cinta', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('67', '2', 'purchase_order_items', '3', 'oci-caja', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('68', '2', 'inventory_lots', '1', 'lote-npk', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('69', '2', 'inventory_lots', '2', 'lote-cinta', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('70', '2', 'inventory_lots', '3', 'lote-guantes', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('71', '2', 'inventory_movements', '1', 'mov-npk', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('72', '2', 'inventory_movements', '2', 'mov-cinta', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('73', '2', 'inventory_movements', '3', 'mov-guantes', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('74', '2', 'inventory_movements', '4', 'mov-npk-salida', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('75', '2', 'purchase_receptions', '1', 'recepcion-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('76', '2', 'purchase_receptions', '2', 'recepcion-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('77', '2', 'purchase_reception_items', '1', 'recepcion-item-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('78', '2', 'purchase_reception_items', '2', 'recepcion-item-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('79', '2', 'labor_entries', '1', 'labor-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('80', '2', 'labor_entries', '2', 'labor-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('81', '2', 'labor_entries', '3', 'labor-003', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('82', '2', 'labor_entries', '4', 'labor-004', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('83', '2', 'production_entries', '1', 'prod-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('84', '2', 'production_entries', '2', 'prod-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('85', '2', 'production_entries', '3', 'prod-003', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('86', '2', 'expense_entries', '1', 'expense-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('87', '2', 'expense_entries', '2', 'expense-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('88', '2', 'expense_entries', '3', 'expense-003', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('89', '2', 'budgets', '1', 'budget-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('90', '2', 'budgets', '2', 'budget-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('91', '2', 'machinery_maintenance', '1', 'maint-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('92', '2', 'machinery_maintenance', '2', 'maint-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('93', '2', 'fuel_movements', '1', 'fuel-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('94', '2', 'fuel_movements', '2', 'fuel-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('95', '2', 'fuel_movements', '3', 'fuel-003', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('96', '2', 'inventory_transfers', '1', 'transfer-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('97', '2', 'internal_requests', '1', 'request-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('98', '2', 'internal_requests', '2', 'request-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('99', '2', 'internal_request_items', '1', 'request-item-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('100', '2', 'internal_request_items', '2', 'request-item-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('101', '2', 'documents', '1', 'document-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('102', '2', 'documents', '2', 'document-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('103', '2', 'documents', '3', 'document-003', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('104', '2', 'notifications', '1', 'notification-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('105', '2', 'notifications', '2', 'notification-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('106', '2', 'notifications', '3', 'notification-003', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('107', '2', 'calendar_events', '1', 'event-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('108', '2', 'calendar_events', '2', 'event-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('109', '2', 'tasks', '1', 'task-001', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('110', '2', 'tasks', '2', 'task-002', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('111', '2', 'tasks', '3', 'task-003', '2026-07-28 03:48:30');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('112', '3', 'farms', '5', 'campo-norte', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('113', '3', 'farms', '6', 'fundo-rio', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('114', '3', 'species', '7', 'cerezo', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('115', '3', 'species', '8', 'uva', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('116', '3', 'species', '9', 'manzano', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('117', '3', 'seasons', '5', 'temporada-2024-2025', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('118', '3', 'seasons', '6', 'temporada-2025-2026', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('119', '3', 'blocks', '13', 'norte-cerezo-01', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('120', '3', 'blocks', '14', 'norte-cerezo-02', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('121', '3', 'blocks', '15', 'norte-uva-01', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('122', '3', 'blocks', '16', 'rio-manzano-01', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('123', '3', 'blocks', '17', 'rio-manzano-02', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('124', '3', 'blocks', '18', 'rio-cerezo-01', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('125', '3', 'cost_centers', '13', 'produccion', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('126', '3', 'cost_centers', '14', 'riego', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('127', '3', 'cost_centers', '15', 'administracion', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('128', '3', 'cost_centers', '16', 'bodega', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('129', '3', 'workers', '7', 'ana', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('130', '3', 'workers', '8', 'carlos', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('131', '3', 'workers', '9', 'maria', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('132', '3', 'workers', '10', 'jorge', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('133', '3', 'workers', '11', 'patricia', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('134', '3', 'workers', '12', 'rodrigo', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('135', '3', 'inventory_items', '7', 'fertilizante', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('136', '3', 'inventory_items', '8', 'azufre', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('137', '3', 'inventory_items', '9', 'cinta-riego', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('138', '3', 'inventory_items', '10', 'caja-cosecha', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('139', '3', 'inventory_items', '11', 'guante', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('140', '3', 'inventory_items', '12', 'aceite', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('141', '3', 'warehouses', '3', 'bodega-norte', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('142', '3', 'warehouses', '4', 'bodega-rio', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('143', '3', 'warehouse_locations', '4', 'norte-insumos', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('144', '3', 'warehouse_locations', '5', 'norte-epp', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('145', '3', 'warehouse_locations', '6', 'rio-insumos', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('146', '3', 'suppliers', '4', 'agro-sur', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('147', '3', 'suppliers', '5', 'riego-tec', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('148', '3', 'suppliers', '6', 'maquinaria-maule', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('149', '3', 'clients', '3', 'frutas-andinas', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('150', '3', 'clients', '4', 'exportadora-central', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('151', '3', 'contractors', '3', 'servicios-rurales', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('152', '3', 'contractors', '4', 'transporte-cosecha', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('153', '3', 'machinery', '4', 'tractor-01', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('154', '3', 'machinery', '5', 'tractor-02', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('155', '3', 'machinery', '6', 'pulverizador', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('156', '3', 'crews', '3', 'cuadrilla-norte', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('157', '3', 'crews', '4', 'cuadrilla-rio', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('158', '3', 'purchase_orders', '4', 'oc-2025-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('159', '3', 'purchase_orders', '5', 'oc-2025-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('160', '3', 'purchase_orders', '6', 'oc-2025-003', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('161', '3', 'purchase_order_items', '4', 'oci-fertilizante', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('162', '3', 'purchase_order_items', '5', 'oci-cinta', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('163', '3', 'purchase_order_items', '6', 'oci-caja', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('164', '3', 'inventory_lots', '4', 'lote-npk', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('165', '3', 'inventory_lots', '5', 'lote-cinta', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('166', '3', 'inventory_lots', '6', 'lote-guantes', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('167', '3', 'inventory_movements', '5', 'mov-npk', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('168', '3', 'inventory_movements', '6', 'mov-cinta', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('169', '3', 'inventory_movements', '7', 'mov-guantes', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('170', '3', 'inventory_movements', '8', 'mov-npk-salida', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('171', '3', 'purchase_receptions', '3', 'recepcion-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('172', '3', 'purchase_receptions', '4', 'recepcion-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('173', '3', 'purchase_reception_items', '3', 'recepcion-item-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('174', '3', 'purchase_reception_items', '4', 'recepcion-item-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('175', '3', 'labor_entries', '5', 'labor-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('176', '3', 'labor_entries', '6', 'labor-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('177', '3', 'labor_entries', '7', 'labor-003', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('178', '3', 'labor_entries', '8', 'labor-004', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('179', '3', 'production_entries', '4', 'prod-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('180', '3', 'production_entries', '5', 'prod-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('181', '3', 'production_entries', '6', 'prod-003', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('182', '3', 'expense_entries', '4', 'expense-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('183', '3', 'expense_entries', '5', 'expense-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('184', '3', 'expense_entries', '6', 'expense-003', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('185', '3', 'budgets', '3', 'budget-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('186', '3', 'budgets', '4', 'budget-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('187', '3', 'machinery_maintenance', '3', 'maint-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('188', '3', 'machinery_maintenance', '4', 'maint-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('189', '3', 'fuel_movements', '4', 'fuel-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('190', '3', 'fuel_movements', '5', 'fuel-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('191', '3', 'fuel_movements', '6', 'fuel-003', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('192', '3', 'inventory_transfers', '2', 'transfer-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('193', '3', 'internal_requests', '3', 'request-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('194', '3', 'internal_requests', '4', 'request-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('195', '3', 'internal_request_items', '3', 'request-item-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('196', '3', 'internal_request_items', '4', 'request-item-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('197', '3', 'documents', '4', 'document-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('198', '3', 'documents', '5', 'document-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('199', '3', 'documents', '6', 'document-003', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('200', '3', 'notifications', '4', 'notification-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('201', '3', 'notifications', '5', 'notification-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('202', '3', 'notifications', '6', 'notification-003', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('203', '3', 'calendar_events', '3', 'event-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('204', '3', 'calendar_events', '4', 'event-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('205', '3', 'tasks', '4', 'task-001', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('206', '3', 'tasks', '5', 'task-002', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('207', '3', 'tasks', '6', 'task-003', '2026-07-28 03:48:44');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('208', '4', 'farms', '7', 'campo-norte', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('209', '4', 'farms', '8', 'fundo-rio', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('210', '4', 'species', '10', 'cerezo', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('211', '4', 'species', '11', 'uva', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('212', '4', 'species', '12', 'manzano', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('213', '4', 'seasons', '7', 'temporada-2024-2025', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('214', '4', 'seasons', '8', 'temporada-2025-2026', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('215', '4', 'blocks', '19', 'norte-cerezo-01', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('216', '4', 'blocks', '20', 'norte-cerezo-02', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('217', '4', 'blocks', '21', 'norte-uva-01', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('218', '4', 'blocks', '22', 'rio-manzano-01', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('219', '4', 'blocks', '23', 'rio-manzano-02', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('220', '4', 'blocks', '24', 'rio-cerezo-01', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('221', '4', 'cost_centers', '17', 'produccion', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('222', '4', 'cost_centers', '18', 'riego', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('223', '4', 'cost_centers', '19', 'administracion', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('224', '4', 'cost_centers', '20', 'bodega', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('225', '4', 'workers', '13', 'ana', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('226', '4', 'workers', '14', 'carlos', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('227', '4', 'workers', '15', 'maria', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('228', '4', 'workers', '16', 'jorge', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('229', '4', 'workers', '17', 'patricia', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('230', '4', 'workers', '18', 'rodrigo', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('231', '4', 'workers', '19', 'fernando', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('232', '4', 'workers', '20', 'sofia', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('233', '4', 'inventory_items', '13', 'fertilizante', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('234', '4', 'inventory_items', '14', 'azufre', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('235', '4', 'inventory_items', '15', 'cinta-riego', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('236', '4', 'inventory_items', '16', 'caja-cosecha', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('237', '4', 'inventory_items', '17', 'guante', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('238', '4', 'inventory_items', '18', 'aceite', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('239', '4', 'inventory_items', '19', 'semillas-cerezo', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('240', '4', 'inventory_items', '20', 'herbicida', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('241', '4', 'inventory_items', '21', 'manguera', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('242', '4', 'warehouses', '5', 'bodega-norte', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('243', '4', 'warehouses', '6', 'bodega-rio', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('244', '4', 'warehouse_locations', '7', 'norte-insumos', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('245', '4', 'warehouse_locations', '8', 'norte-epp', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('246', '4', 'warehouse_locations', '9', 'rio-insumos', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('247', '4', 'warehouse_locations', '10', 'rio-pallets', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('248', '4', 'suppliers', '7', 'agro-sur', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('249', '4', 'suppliers', '8', 'riego-tec', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('250', '4', 'suppliers', '9', 'maquinaria-maule', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('251', '4', 'clients', '5', 'frutas-andinas', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('252', '4', 'clients', '6', 'exportadora-central', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('253', '4', 'contractors', '5', 'servicios-rurales', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('254', '4', 'contractors', '6', 'transporte-cosecha', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('255', '4', 'machinery', '7', 'tractor-01', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('256', '4', 'machinery', '8', 'tractor-02', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('257', '4', 'machinery', '9', 'pulverizador', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('258', '4', 'crews', '5', 'cuadrilla-norte', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('259', '4', 'crews', '6', 'cuadrilla-rio', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('260', '4', 'purchase_orders', '7', 'oc-2025-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('261', '4', 'purchase_orders', '8', 'oc-2025-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('262', '4', 'purchase_orders', '9', 'oc-2025-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('263', '4', 'purchase_orders', '10', 'oc-2025-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('264', '4', 'purchase_orders', '11', 'oc-2025-005', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('265', '4', 'purchase_order_items', '7', 'oci-fertilizante', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('266', '4', 'purchase_order_items', '8', 'oci-cinta', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('267', '4', 'purchase_order_items', '9', 'oci-caja', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('268', '4', 'purchase_order_items', '10', 'oci-herbicida', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('269', '4', 'purchase_order_items', '11', 'oci-manguera', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('270', '4', 'inventory_lots', '7', 'lote-npk', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('271', '4', 'inventory_lots', '8', 'lote-cinta', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('272', '4', 'inventory_lots', '9', 'lote-guantes', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('273', '4', 'inventory_lots', '10', 'lote-herbicida', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('274', '4', 'inventory_lots', '11', 'lote-manguera', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('275', '4', 'inventory_movements', '9', 'mov-npk', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('276', '4', 'inventory_movements', '10', 'mov-cinta', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('277', '4', 'inventory_movements', '11', 'mov-guantes', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('278', '4', 'inventory_movements', '12', 'mov-npk-salida', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('279', '4', 'inventory_movements', '13', 'mov-herbicida', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('280', '4', 'inventory_movements', '14', 'mov-manguera', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('281', '4', 'purchase_receptions', '5', 'recepcion-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('282', '4', 'purchase_receptions', '6', 'recepcion-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('283', '4', 'purchase_receptions', '7', 'recepcion-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('284', '4', 'purchase_reception_items', '5', 'recepcion-item-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('285', '4', 'purchase_reception_items', '6', 'recepcion-item-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('286', '4', 'purchase_reception_items', '7', 'recepcion-item-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('287', '4', 'labor_entries', '9', 'labor-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('288', '4', 'labor_entries', '10', 'labor-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('289', '4', 'labor_entries', '11', 'labor-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('290', '4', 'labor_entries', '12', 'labor-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('291', '4', 'labor_entries', '13', 'labor-005', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('292', '4', 'labor_entries', '14', 'labor-006', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('293', '4', 'production_entries', '7', 'prod-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('294', '4', 'production_entries', '8', 'prod-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('295', '4', 'production_entries', '9', 'prod-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('296', '4', 'production_entries', '10', 'prod-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('297', '4', 'production_entries', '11', 'prod-005', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('298', '4', 'expense_entries', '7', 'expense-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('299', '4', 'expense_entries', '8', 'expense-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('300', '4', 'expense_entries', '9', 'expense-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('301', '4', 'expense_entries', '10', 'expense-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('302', '4', 'expense_entries', '11', 'expense-005', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('303', '4', 'budgets', '5', 'budget-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('304', '4', 'budgets', '6', 'budget-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('305', '4', 'budgets', '7', 'budget-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('306', '4', 'budgets', '8', 'budget-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('307', '4', 'machinery_maintenance', '5', 'maint-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('308', '4', 'machinery_maintenance', '6', 'maint-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('309', '4', 'machinery_maintenance', '7', 'maint-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('310', '4', 'machinery_maintenance', '8', 'maint-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('311', '4', 'fuel_movements', '7', 'fuel-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('312', '4', 'fuel_movements', '8', 'fuel-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('313', '4', 'fuel_movements', '9', 'fuel-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('314', '4', 'fuel_movements', '10', 'fuel-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('315', '4', 'fuel_movements', '11', 'fuel-005', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('316', '4', 'inventory_transfers', '3', 'transfer-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('317', '4', 'internal_requests', '5', 'request-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('318', '4', 'internal_requests', '6', 'request-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('319', '4', 'internal_requests', '7', 'request-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('320', '4', 'internal_requests', '8', 'request-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('321', '4', 'internal_request_items', '5', 'request-item-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('322', '4', 'internal_request_items', '6', 'request-item-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('323', '4', 'internal_request_items', '7', 'request-item-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('324', '4', 'internal_request_items', '8', 'request-item-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('325', '4', 'documents', '7', 'document-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('326', '4', 'documents', '8', 'document-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('327', '4', 'documents', '9', 'document-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('328', '4', 'documents', '10', 'document-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('329', '4', 'documents', '11', 'document-005', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('330', '4', 'notifications', '7', 'notification-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('331', '4', 'notifications', '8', 'notification-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('332', '4', 'notifications', '9', 'notification-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('333', '4', 'notifications', '10', 'notification-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('334', '4', 'notifications', '11', 'notification-005', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('335', '4', 'calendar_events', '5', 'event-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('336', '4', 'calendar_events', '6', 'event-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('337', '4', 'calendar_events', '7', 'event-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('338', '4', 'calendar_events', '8', 'event-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('339', '4', 'tasks', '7', 'task-001', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('340', '4', 'tasks', '8', 'task-002', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('341', '4', 'tasks', '9', 'task-003', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('342', '4', 'tasks', '10', 'task-004', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('343', '4', 'tasks', '11', 'task-005', '2026-08-03 16:37:06');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('344', '5', 'farms', '9', 'campo-norte', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('345', '5', 'farms', '10', 'fundo-rio', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('346', '5', 'species', '13', 'cerezo', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('347', '5', 'species', '14', 'uva', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('348', '5', 'species', '15', 'manzano', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('349', '5', 'seasons', '9', 'temporada-2024-2025', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('350', '5', 'seasons', '10', 'temporada-2025-2026', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('351', '5', 'blocks', '25', 'norte-cerezo-01', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('352', '5', 'blocks', '26', 'norte-cerezo-02', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('353', '5', 'blocks', '27', 'norte-uva-01', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('354', '5', 'blocks', '28', 'rio-manzano-01', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('355', '5', 'blocks', '29', 'rio-manzano-02', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('356', '5', 'blocks', '30', 'rio-cerezo-01', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('357', '5', 'cost_centers', '21', 'produccion', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('358', '5', 'cost_centers', '22', 'riego', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('359', '5', 'cost_centers', '23', 'administracion', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('360', '5', 'cost_centers', '24', 'bodega', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('361', '5', 'workers', '21', 'ana', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('362', '5', 'workers', '22', 'carlos', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('363', '5', 'workers', '23', 'maria', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('364', '5', 'workers', '24', 'jorge', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('365', '5', 'workers', '25', 'patricia', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('366', '5', 'workers', '26', 'rodrigo', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('367', '5', 'workers', '27', 'fernando', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('368', '5', 'workers', '28', 'sofia', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('369', '5', 'inventory_items', '22', 'fertilizante', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('370', '5', 'inventory_items', '23', 'azufre', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('371', '5', 'inventory_items', '24', 'cinta-riego', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('372', '5', 'inventory_items', '25', 'caja-cosecha', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('373', '5', 'inventory_items', '26', 'guante', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('374', '5', 'inventory_items', '27', 'aceite', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('375', '5', 'inventory_items', '28', 'semillas-cerezo', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('376', '5', 'inventory_items', '29', 'herbicida', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('377', '5', 'inventory_items', '30', 'manguera', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('378', '5', 'warehouses', '7', 'bodega-norte', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('379', '5', 'warehouses', '8', 'bodega-rio', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('380', '5', 'warehouse_locations', '11', 'norte-insumos', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('381', '5', 'warehouse_locations', '12', 'norte-epp', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('382', '5', 'warehouse_locations', '13', 'rio-insumos', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('383', '5', 'warehouse_locations', '14', 'rio-pallets', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('384', '5', 'suppliers', '10', 'agro-sur', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('385', '5', 'suppliers', '11', 'riego-tec', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('386', '5', 'suppliers', '12', 'maquinaria-maule', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('387', '5', 'clients', '7', 'frutas-andinas', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('388', '5', 'clients', '8', 'exportadora-central', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('389', '5', 'contractors', '7', 'servicios-rurales', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('390', '5', 'contractors', '8', 'transporte-cosecha', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('391', '5', 'machinery', '10', 'tractor-01', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('392', '5', 'machinery', '11', 'tractor-02', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('393', '5', 'machinery', '12', 'pulverizador', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('394', '5', 'crews', '7', 'cuadrilla-norte', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('395', '5', 'crews', '8', 'cuadrilla-rio', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('396', '5', 'purchase_orders', '12', 'oc-2025-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('397', '5', 'purchase_orders', '13', 'oc-2025-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('398', '5', 'purchase_orders', '14', 'oc-2025-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('399', '5', 'purchase_orders', '15', 'oc-2025-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('400', '5', 'purchase_orders', '16', 'oc-2025-005', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('401', '5', 'purchase_order_items', '12', 'oci-fertilizante', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('402', '5', 'purchase_order_items', '13', 'oci-cinta', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('403', '5', 'purchase_order_items', '14', 'oci-caja', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('404', '5', 'purchase_order_items', '15', 'oci-herbicida', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('405', '5', 'purchase_order_items', '16', 'oci-manguera', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('406', '5', 'inventory_lots', '12', 'lote-npk', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('407', '5', 'inventory_lots', '13', 'lote-cinta', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('408', '5', 'inventory_lots', '14', 'lote-guantes', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('409', '5', 'inventory_lots', '15', 'lote-herbicida', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('410', '5', 'inventory_lots', '16', 'lote-manguera', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('411', '5', 'inventory_movements', '15', 'mov-npk', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('412', '5', 'inventory_movements', '16', 'mov-cinta', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('413', '5', 'inventory_movements', '17', 'mov-guantes', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('414', '5', 'inventory_movements', '18', 'mov-npk-salida', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('415', '5', 'inventory_movements', '19', 'mov-herbicida', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('416', '5', 'inventory_movements', '20', 'mov-manguera', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('417', '5', 'purchase_receptions', '8', 'recepcion-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('418', '5', 'purchase_receptions', '9', 'recepcion-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('419', '5', 'purchase_receptions', '10', 'recepcion-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('420', '5', 'purchase_reception_items', '8', 'recepcion-item-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('421', '5', 'purchase_reception_items', '9', 'recepcion-item-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('422', '5', 'purchase_reception_items', '10', 'recepcion-item-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('423', '5', 'labor_entries', '15', 'labor-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('424', '5', 'labor_entries', '16', 'labor-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('425', '5', 'labor_entries', '17', 'labor-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('426', '5', 'labor_entries', '18', 'labor-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('427', '5', 'labor_entries', '19', 'labor-005', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('428', '5', 'labor_entries', '20', 'labor-006', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('429', '5', 'production_entries', '12', 'prod-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('430', '5', 'production_entries', '13', 'prod-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('431', '5', 'production_entries', '14', 'prod-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('432', '5', 'production_entries', '15', 'prod-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('433', '5', 'production_entries', '16', 'prod-005', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('434', '5', 'expense_entries', '12', 'expense-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('435', '5', 'expense_entries', '13', 'expense-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('436', '5', 'expense_entries', '14', 'expense-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('437', '5', 'expense_entries', '15', 'expense-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('438', '5', 'expense_entries', '16', 'expense-005', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('439', '5', 'budgets', '9', 'budget-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('440', '5', 'budgets', '10', 'budget-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('441', '5', 'budgets', '11', 'budget-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('442', '5', 'budgets', '12', 'budget-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('443', '5', 'machinery_maintenance', '9', 'maint-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('444', '5', 'machinery_maintenance', '10', 'maint-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('445', '5', 'machinery_maintenance', '11', 'maint-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('446', '5', 'machinery_maintenance', '12', 'maint-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('447', '5', 'fuel_movements', '12', 'fuel-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('448', '5', 'fuel_movements', '13', 'fuel-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('449', '5', 'fuel_movements', '14', 'fuel-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('450', '5', 'fuel_movements', '15', 'fuel-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('451', '5', 'fuel_movements', '16', 'fuel-005', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('452', '5', 'inventory_transfers', '4', 'transfer-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('453', '5', 'internal_requests', '9', 'request-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('454', '5', 'internal_requests', '10', 'request-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('455', '5', 'internal_requests', '11', 'request-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('456', '5', 'internal_requests', '12', 'request-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('457', '5', 'internal_request_items', '9', 'request-item-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('458', '5', 'internal_request_items', '10', 'request-item-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('459', '5', 'internal_request_items', '11', 'request-item-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('460', '5', 'internal_request_items', '12', 'request-item-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('461', '5', 'documents', '12', 'document-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('462', '5', 'documents', '13', 'document-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('463', '5', 'documents', '14', 'document-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('464', '5', 'documents', '15', 'document-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('465', '5', 'documents', '16', 'document-005', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('466', '5', 'notifications', '12', 'notification-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('467', '5', 'notifications', '13', 'notification-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('468', '5', 'notifications', '14', 'notification-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('469', '5', 'notifications', '15', 'notification-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('470', '5', 'notifications', '16', 'notification-005', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('471', '5', 'calendar_events', '9', 'event-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('472', '5', 'calendar_events', '10', 'event-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('473', '5', 'calendar_events', '11', 'event-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('474', '5', 'calendar_events', '12', 'event-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('475', '5', 'tasks', '12', 'task-001', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('476', '5', 'tasks', '13', 'task-002', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('477', '5', 'tasks', '14', 'task-003', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('478', '5', 'tasks', '15', 'task-004', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('479', '5', 'tasks', '16', 'task-005', '2026-08-03 16:43:55');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('480', '6', 'farms', '11', 'campo-norte', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('481', '6', 'farms', '12', 'fundo-rio', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('482', '6', 'species', '16', 'cerezo', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('483', '6', 'species', '17', 'uva', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('484', '6', 'species', '18', 'manzano', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('485', '6', 'seasons', '11', 'temporada-2024-2025', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('486', '6', 'seasons', '12', 'temporada-2025-2026', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('487', '6', 'blocks', '31', 'norte-cerezo-01', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('488', '6', 'blocks', '32', 'norte-cerezo-02', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('489', '6', 'blocks', '33', 'norte-uva-01', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('490', '6', 'blocks', '34', 'rio-manzano-01', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('491', '6', 'blocks', '35', 'rio-manzano-02', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('492', '6', 'blocks', '36', 'rio-cerezo-01', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('493', '6', 'cost_centers', '25', 'produccion', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('494', '6', 'cost_centers', '26', 'riego', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('495', '6', 'cost_centers', '27', 'administracion', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('496', '6', 'cost_centers', '28', 'bodega', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('497', '6', 'workers', '29', 'ana', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('498', '6', 'workers', '30', 'carlos', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('499', '6', 'workers', '31', 'maria', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('500', '6', 'workers', '32', 'jorge', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('501', '6', 'workers', '33', 'patricia', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('502', '6', 'workers', '34', 'rodrigo', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('503', '6', 'workers', '35', 'fernando', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('504', '6', 'workers', '36', 'sofia', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('505', '6', 'inventory_items', '31', 'fertilizante', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('506', '6', 'inventory_items', '32', 'azufre', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('507', '6', 'inventory_items', '33', 'cinta-riego', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('508', '6', 'inventory_items', '34', 'caja-cosecha', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('509', '6', 'inventory_items', '35', 'guante', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('510', '6', 'inventory_items', '36', 'aceite', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('511', '6', 'inventory_items', '37', 'semillas-cerezo', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('512', '6', 'inventory_items', '38', 'herbicida', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('513', '6', 'inventory_items', '39', 'manguera', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('514', '6', 'warehouses', '9', 'bodega-norte', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('515', '6', 'warehouses', '10', 'bodega-rio', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('516', '6', 'warehouse_locations', '15', 'norte-insumos', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('517', '6', 'warehouse_locations', '16', 'norte-epp', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('518', '6', 'warehouse_locations', '17', 'rio-insumos', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('519', '6', 'warehouse_locations', '18', 'rio-pallets', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('520', '6', 'suppliers', '13', 'agro-sur', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('521', '6', 'suppliers', '14', 'riego-tec', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('522', '6', 'suppliers', '15', 'maquinaria-maule', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('523', '6', 'clients', '9', 'frutas-andinas', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('524', '6', 'clients', '10', 'exportadora-central', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('525', '6', 'contractors', '9', 'servicios-rurales', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('526', '6', 'contractors', '10', 'transporte-cosecha', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('527', '6', 'machinery', '13', 'tractor-01', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('528', '6', 'machinery', '14', 'tractor-02', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('529', '6', 'machinery', '15', 'pulverizador', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('530', '6', 'crews', '9', 'cuadrilla-norte', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('531', '6', 'crews', '10', 'cuadrilla-rio', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('532', '6', 'purchase_orders', '17', 'oc-2025-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('533', '6', 'purchase_orders', '18', 'oc-2025-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('534', '6', 'purchase_orders', '19', 'oc-2025-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('535', '6', 'purchase_orders', '20', 'oc-2025-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('536', '6', 'purchase_orders', '21', 'oc-2025-005', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('537', '6', 'purchase_order_items', '17', 'oci-fertilizante', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('538', '6', 'purchase_order_items', '18', 'oci-cinta', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('539', '6', 'purchase_order_items', '19', 'oci-caja', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('540', '6', 'purchase_order_items', '20', 'oci-herbicida', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('541', '6', 'purchase_order_items', '21', 'oci-manguera', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('542', '6', 'inventory_lots', '17', 'lote-npk', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('543', '6', 'inventory_lots', '18', 'lote-cinta', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('544', '6', 'inventory_lots', '19', 'lote-guantes', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('545', '6', 'inventory_lots', '20', 'lote-herbicida', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('546', '6', 'inventory_lots', '21', 'lote-manguera', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('547', '6', 'inventory_movements', '21', 'mov-npk', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('548', '6', 'inventory_movements', '22', 'mov-cinta', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('549', '6', 'inventory_movements', '23', 'mov-guantes', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('550', '6', 'inventory_movements', '24', 'mov-npk-salida', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('551', '6', 'inventory_movements', '25', 'mov-herbicida', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('552', '6', 'inventory_movements', '26', 'mov-manguera', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('553', '6', 'purchase_receptions', '11', 'recepcion-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('554', '6', 'purchase_receptions', '12', 'recepcion-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('555', '6', 'purchase_receptions', '13', 'recepcion-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('556', '6', 'purchase_reception_items', '11', 'recepcion-item-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('557', '6', 'purchase_reception_items', '12', 'recepcion-item-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('558', '6', 'purchase_reception_items', '13', 'recepcion-item-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('559', '6', 'labor_entries', '21', 'labor-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('560', '6', 'labor_entries', '22', 'labor-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('561', '6', 'labor_entries', '23', 'labor-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('562', '6', 'labor_entries', '24', 'labor-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('563', '6', 'labor_entries', '25', 'labor-005', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('564', '6', 'labor_entries', '26', 'labor-006', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('565', '6', 'production_entries', '17', 'prod-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('566', '6', 'production_entries', '18', 'prod-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('567', '6', 'production_entries', '19', 'prod-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('568', '6', 'production_entries', '20', 'prod-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('569', '6', 'production_entries', '21', 'prod-005', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('570', '6', 'expense_entries', '17', 'expense-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('571', '6', 'expense_entries', '18', 'expense-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('572', '6', 'expense_entries', '19', 'expense-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('573', '6', 'expense_entries', '20', 'expense-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('574', '6', 'expense_entries', '21', 'expense-005', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('575', '6', 'budgets', '13', 'budget-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('576', '6', 'budgets', '14', 'budget-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('577', '6', 'budgets', '15', 'budget-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('578', '6', 'budgets', '16', 'budget-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('579', '6', 'machinery_maintenance', '13', 'maint-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('580', '6', 'machinery_maintenance', '14', 'maint-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('581', '6', 'machinery_maintenance', '15', 'maint-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('582', '6', 'machinery_maintenance', '16', 'maint-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('583', '6', 'fuel_movements', '17', 'fuel-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('584', '6', 'fuel_movements', '18', 'fuel-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('585', '6', 'fuel_movements', '19', 'fuel-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('586', '6', 'fuel_movements', '20', 'fuel-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('587', '6', 'fuel_movements', '21', 'fuel-005', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('588', '6', 'inventory_transfers', '5', 'transfer-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('589', '6', 'internal_requests', '13', 'request-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('590', '6', 'internal_requests', '14', 'request-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('591', '6', 'internal_requests', '15', 'request-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('592', '6', 'internal_requests', '16', 'request-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('593', '6', 'internal_request_items', '13', 'request-item-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('594', '6', 'internal_request_items', '14', 'request-item-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('595', '6', 'internal_request_items', '15', 'request-item-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('596', '6', 'internal_request_items', '16', 'request-item-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('597', '6', 'documents', '17', 'document-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('598', '6', 'documents', '18', 'document-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('599', '6', 'documents', '19', 'document-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('600', '6', 'documents', '20', 'document-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('601', '6', 'documents', '21', 'document-005', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('602', '6', 'notifications', '17', 'notification-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('603', '6', 'notifications', '18', 'notification-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('604', '6', 'notifications', '19', 'notification-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('605', '6', 'notifications', '20', 'notification-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('606', '6', 'notifications', '21', 'notification-005', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('607', '6', 'calendar_events', '13', 'event-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('608', '6', 'calendar_events', '14', 'event-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('609', '6', 'calendar_events', '15', 'event-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('610', '6', 'calendar_events', '16', 'event-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('611', '6', 'tasks', '17', 'task-001', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('612', '6', 'tasks', '18', 'task-002', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('613', '6', 'tasks', '19', 'task-003', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('614', '6', 'tasks', '20', 'task-004', '2026-08-03 16:52:22');
INSERT INTO `demo_records` (`id`, `batch_id`, `table_name`, `record_id`, `record_key`, `created_at`) VALUES ('615', '6', 'tasks', '21', 'task-005', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `document_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `client_id` bigint unsigned DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_documents_supplier` (`supplier_id`),
  KEY `fk_documents_client` (`client_id`),
  KEY `fk_documents_user` (`created_by`),
  KEY `idx_documents_company_date` (`company_id`,`issue_date`,`document_type`),
  CONSTRAINT `fk_documents_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_documents_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_documents_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_documents_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `documents` WRITE;
INSERT INTO `documents` (`id`, `company_id`, `document_type`, `document_number`, `issue_date`, `supplier_id`, `client_id`, `status`, `created_by`, `created_at`) VALUES ('17', '1', 'FACTURA_COMPRA', 'FAC-DEMO-001', '2025-08-20', '13', NULL, 'POSTED', '1', '2026-08-03 16:52:22');
INSERT INTO `documents` (`id`, `company_id`, `document_type`, `document_number`, `issue_date`, `supplier_id`, `client_id`, `status`, `created_by`, `created_at`) VALUES ('18', '1', 'GUIA_DESPACHO', 'GD-DEMO-018', '2025-09-25', '14', NULL, 'POSTED', '1', '2026-08-03 16:52:22');
INSERT INTO `documents` (`id`, `company_id`, `document_type`, `document_number`, `issue_date`, `supplier_id`, `client_id`, `status`, `created_by`, `created_at`) VALUES ('19', '1', 'ORDEN_VENTA', 'OV-DEMO-004', '2025-03-20', NULL, '9', 'CONFIRMED', '1', '2026-08-03 16:52:22');
INSERT INTO `documents` (`id`, `company_id`, `document_type`, `document_number`, `issue_date`, `supplier_id`, `client_id`, `status`, `created_by`, `created_at`) VALUES ('20', '1', 'FACTURA_COMPRA', 'FAC-DEMO-006', '2025-10-12', '15', NULL, 'POSTED', '1', '2026-08-03 16:52:22');
INSERT INTO `documents` (`id`, `company_id`, `document_type`, `document_number`, `issue_date`, `supplier_id`, `client_id`, `status`, `created_by`, `created_at`) VALUES ('21', '1', 'NOTA_CREDITO', 'NC-DEMO-003', '2025-10-18', NULL, '10', 'PENDING', '1', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `expense_entries`;
CREATE TABLE `expense_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `season_id` bigint unsigned NOT NULL,
  `farm_id` bigint unsigned DEFAULT NULL,
  `block_id` bigint unsigned DEFAULT NULL,
  `cost_center_id` bigint unsigned NOT NULL,
  `entry_date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_number` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'POSTED',
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_expenses_season` (`season_id`),
  KEY `fk_expenses_farm` (`farm_id`),
  KEY `fk_expenses_block` (`block_id`),
  KEY `fk_expenses_center` (`cost_center_id`),
  KEY `fk_expenses_user` (`created_by`),
  KEY `idx_expenses_reporting` (`company_id`,`season_id`,`farm_id`,`block_id`,`entry_date`),
  KEY `idx_expenses_scope_date` (`company_id`,`entry_date`,`farm_id`,`block_id`,`status`),
  CONSTRAINT `fk_expenses_block` FOREIGN KEY (`block_id`) REFERENCES `blocks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_expenses_center` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`),
  CONSTRAINT `fk_expenses_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_expenses_farm` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_expenses_season` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`),
  CONSTRAINT `fk_expenses_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `expense_entries` WRITE;
INSERT INTO `expense_entries` (`id`, `company_id`, `season_id`, `farm_id`, `block_id`, `cost_center_id`, `entry_date`, `description`, `document_number`, `amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('17', '1', '12', '11', '31', '25', '2025-09-15', 'Aplicación de fertilizante', 'FAC-DEMO-001', '239200.00', 'POSTED', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `expense_entries` (`id`, `company_id`, `season_id`, `farm_id`, `block_id`, `cost_center_id`, `entry_date`, `description`, `document_number`, `amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('18', '1', '12', '12', NULL, '26', '2025-09-28', 'Reparación de bomba de riego', 'FAC-DEMO-002', '485000.00', 'POSTED', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `expense_entries` (`id`, `company_id`, `season_id`, `farm_id`, `block_id`, `cost_center_id`, `entry_date`, `description`, `document_number`, `amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('19', '1', '12', NULL, NULL, '27', '2025-10-01', 'Servicios contables mensuales', 'FAC-DEMO-003', '320000.00', 'POSTED', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `expense_entries` (`id`, `company_id`, `season_id`, `farm_id`, `block_id`, `cost_center_id`, `entry_date`, `description`, `document_number`, `amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('20', '1', '12', '11', '33', '28', '2025-10-08', 'Embalaje para exportación', 'FAC-DEMO-004', '164800.00', 'POSTED', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `expense_entries` (`id`, `company_id`, `season_id`, `farm_id`, `block_id`, `cost_center_id`, `entry_date`, `description`, `document_number`, `amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('21', '1', '12', '12', '35', '25', '2025-10-15', 'Manejo sanitario y laboratorio', 'FAC-DEMO-005', '128500.00', 'POSTED', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `farms`;
CREATE TABLE `farms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `name` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hectares` decimal(12,2) NOT NULL DEFAULT '0.00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_farms_company_code` (`company_id`,`code`),
  CONSTRAINT `fk_farms_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `farms` WRITE;
INSERT INTO `farms` (`id`, `company_id`, `name`, `code`, `location`, `hectares`, `active`, `created_at`, `updated_at`) VALUES ('11', '1', 'Campo Norte', 'NORTE', 'San Clemente', '118.50', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `farms` (`id`, `company_id`, `name`, `code`, `location`, `hectares`, `active`, `created_at`, `updated_at`) VALUES ('12', '1', 'Fundo Río Claro', 'RIO', 'Molina', '86.20', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `fuel_movements`;
CREATE TABLE `fuel_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `machinery_id` bigint unsigned NOT NULL,
  `farm_id` bigint unsigned DEFAULT NULL,
  `fuel_date` date NOT NULL,
  `liters` decimal(12,3) NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL,
  `meter` decimal(12,2) DEFAULT NULL,
  `reference` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_fuel_machinery` (`machinery_id`),
  KEY `fk_fuel_farm` (`farm_id`),
  KEY `fk_fuel_user` (`created_by`),
  KEY `idx_fuel_reporting` (`company_id`,`machinery_id`,`fuel_date`),
  CONSTRAINT `fk_fuel_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_fuel_farm` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fuel_machinery` FOREIGN KEY (`machinery_id`) REFERENCES `machinery` (`id`),
  CONSTRAINT `fk_fuel_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `fuel_movements` WRITE;
INSERT INTO `fuel_movements` (`id`, `company_id`, `machinery_id`, `farm_id`, `fuel_date`, `liters`, `unit_cost`, `meter`, `reference`, `created_by`, `created_at`) VALUES ('17', '1', '13', '11', '2025-08-30', '180.000', '1040.00', '1852.00', 'Carga semanal', '1', '2026-08-03 16:52:22');
INSERT INTO `fuel_movements` (`id`, `company_id`, `machinery_id`, `farm_id`, `fuel_date`, `liters`, `unit_cost`, `meter`, `reference`, `created_by`, `created_at`) VALUES ('18', '1', '14', '12', '2025-09-06', '220.000', '1060.00', '2324.00', 'Carga semanal', '1', '2026-08-03 16:52:22');
INSERT INTO `fuel_movements` (`id`, `company_id`, `machinery_id`, `farm_id`, `fuel_date`, `liters`, `unit_cost`, `meter`, `reference`, `created_by`, `created_at`) VALUES ('19', '1', '15', '11', '2025-09-18', '95.000', '1060.00', '945.00', 'Aplicación sanitaria', '1', '2026-08-03 16:52:22');
INSERT INTO `fuel_movements` (`id`, `company_id`, `machinery_id`, `farm_id`, `fuel_date`, `liters`, `unit_cost`, `meter`, `reference`, `created_by`, `created_at`) VALUES ('20', '1', '13', '11', '2025-10-11', '210.000', '1050.00', '1898.00', 'Carga de transporte', '1', '2026-08-03 16:52:22');
INSERT INTO `fuel_movements` (`id`, `company_id`, `machinery_id`, `farm_id`, `fuel_date`, `liters`, `unit_cost`, `meter`, `reference`, `created_by`, `created_at`) VALUES ('21', '1', '14', '12', '2025-10-16', '170.000', '1065.00', '2368.00', 'Carga de campo', '1', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `internal_request_items`;
CREATE TABLE `internal_request_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `quantity` decimal(15,3) NOT NULL,
  `fulfilled_quantity` decimal(15,3) NOT NULL DEFAULT '0.000',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_request_items_item` (`request_id`,`item_id`),
  KEY `fk_request_items_item` (`item_id`),
  CONSTRAINT `fk_request_items_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `fk_request_items_request` FOREIGN KEY (`request_id`) REFERENCES `internal_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `internal_request_items` WRITE;
INSERT INTO `internal_request_items` (`id`, `request_id`, `item_id`, `quantity`, `fulfilled_quantity`, `notes`) VALUES ('13', '13', '35', '20.000', '20.000', 'Guantes talla mixta');
INSERT INTO `internal_request_items` (`id`, `request_id`, `item_id`, `quantity`, `fulfilled_quantity`, `notes`) VALUES ('14', '14', '32', '60.000', '0.000', 'Tratamiento preventivo');
INSERT INTO `internal_request_items` (`id`, `request_id`, `item_id`, `quantity`, `fulfilled_quantity`, `notes`) VALUES ('15', '15', '34', '80.000', '0.000', 'Cajas de exportación');
INSERT INTO `internal_request_items` (`id`, `request_id`, `item_id`, `quantity`, `fulfilled_quantity`, `notes`) VALUES ('16', '16', '39', '40.000', '40.000', 'Mangueras para riego');
UNLOCK TABLES;

DROP TABLE IF EXISTS `internal_requests`;
CREATE TABLE `internal_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `requested_by` bigint unsigned NOT NULL,
  `farm_id` bigint unsigned DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `requested_on` date NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_requests_user` (`requested_by`),
  KEY `fk_requests_farm` (`farm_id`),
  KEY `idx_requests_status` (`company_id`,`status`,`requested_on`),
  CONSTRAINT `fk_requests_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_requests_farm` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_requests_user` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `internal_requests` WRITE;
INSERT INTO `internal_requests` (`id`, `company_id`, `requested_by`, `farm_id`, `status`, `requested_on`, `notes`, `created_at`) VALUES ('13', '1', '1', '11', 'FULFILLED', '2025-09-22', 'Materiales para cuadrilla de cosecha', '2026-08-03 16:52:22');
INSERT INTO `internal_requests` (`id`, `company_id`, `requested_by`, `farm_id`, `status`, `requested_on`, `notes`, `created_at`) VALUES ('14', '1', '1', '12', 'APPROVED', '2025-10-03', 'Insumos para labores de otoño', '2026-08-03 16:52:22');
INSERT INTO `internal_requests` (`id`, `company_id`, `requested_by`, `farm_id`, `status`, `requested_on`, `notes`, `created_at`) VALUES ('15', '1', '1', '11', 'PENDING', '2025-10-11', 'Materiales de postcosecha y embalaje', '2026-08-03 16:52:22');
INSERT INTO `internal_requests` (`id`, `company_id`, `requested_by`, `farm_id`, `status`, `requested_on`, `notes`, `created_at`) VALUES ('16', '1', '1', '12', 'FULFILLED', '2025-10-17', 'Reposición de equipos y mangueras', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `inventory_items`;
CREATE TABLE `inventory_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `minimum_stock` decimal(15,3) NOT NULL DEFAULT '0.000',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_inventory_items_company_sku` (`company_id`,`sku`),
  CONSTRAINT `fk_inventory_items_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `inventory_items` WRITE;
INSERT INTO `inventory_items` (`id`, `company_id`, `sku`, `name`, `category`, `unit`, `minimum_stock`, `active`) VALUES ('31', '1', 'INS-001', 'Fertilizante NPK 20-20-20', 'INSUMO', 'KG', '200.000', '1');
INSERT INTO `inventory_items` (`id`, `company_id`, `sku`, `name`, `category`, `unit`, `minimum_stock`, `active`) VALUES ('32', '1', 'INS-002', 'Azufre mojable', 'INSUMO', 'KG', '80.000', '1');
INSERT INTO `inventory_items` (`id`, `company_id`, `sku`, `name`, `category`, `unit`, `minimum_stock`, `active`) VALUES ('33', '1', 'RIE-001', 'Cinta de riego 16 mm', 'FERRETERIA', 'M', '500.000', '1');
INSERT INTO `inventory_items` (`id`, `company_id`, `sku`, `name`, `category`, `unit`, `minimum_stock`, `active`) VALUES ('34', '1', 'EMB-001', 'Caja plástica de cosecha', 'HERRAMIENTA', 'UN', '100.000', '1');
INSERT INTO `inventory_items` (`id`, `company_id`, `sku`, `name`, `category`, `unit`, `minimum_stock`, `active`) VALUES ('35', '1', 'EPP-001', 'Guantes de trabajo', 'HERRAMIENTA', 'PAR', '40.000', '1');
INSERT INTO `inventory_items` (`id`, `company_id`, `sku`, `name`, `category`, `unit`, `minimum_stock`, `active`) VALUES ('36', '1', 'COM-001', 'Aceite motor diésel', 'INSUMO', 'L', '60.000', '1');
INSERT INTO `inventory_items` (`id`, `company_id`, `sku`, `name`, `category`, `unit`, `minimum_stock`, `active`) VALUES ('37', '1', 'SEM-001', 'Semillas de cerezo certificadas', 'INSUMO', 'KG', '25.000', '1');
INSERT INTO `inventory_items` (`id`, `company_id`, `sku`, `name`, `category`, `unit`, `minimum_stock`, `active`) VALUES ('38', '1', 'HER-001', 'Herbicida selectivo', 'INSUMO', 'L', '120.000', '1');
INSERT INTO `inventory_items` (`id`, `company_id`, `sku`, `name`, `category`, `unit`, `minimum_stock`, `active`) VALUES ('39', '1', 'RIE-002', 'Manguera para riego', 'FERRETERIA', 'M', '220.000', '1');
UNLOCK TABLES;

DROP TABLE IF EXISTS `inventory_lots`;
CREATE TABLE `inventory_lots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `lot_number` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_on` date DEFAULT NULL,
  `quantity` decimal(15,3) NOT NULL DEFAULT '0.000',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_inventory_lots_item_number` (`item_id`,`lot_number`),
  KEY `fk_inventory_lots_company` (`company_id`),
  KEY `fk_inventory_lots_warehouse` (`warehouse_id`),
  CONSTRAINT `fk_inventory_lots_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inventory_lots_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `fk_inventory_lots_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `inventory_lots` WRITE;
INSERT INTO `inventory_lots` (`id`, `company_id`, `item_id`, `warehouse_id`, `lot_number`, `expires_on`, `quantity`, `created_at`) VALUES ('17', '1', '31', '9', 'NPK-2508', '2027-08-31', '1800.000', '2026-08-03 16:52:22');
INSERT INTO `inventory_lots` (`id`, `company_id`, `item_id`, `warehouse_id`, `lot_number`, `expires_on`, `quantity`, `created_at`) VALUES ('18', '1', '33', '10', 'RIE-2509', NULL, '1200.000', '2026-08-03 16:52:22');
INSERT INTO `inventory_lots` (`id`, `company_id`, `item_id`, `warehouse_id`, `lot_number`, `expires_on`, `quantity`, `created_at`) VALUES ('19', '1', '35', '9', 'EPP-2507', NULL, '120.000', '2026-08-03 16:52:22');
INSERT INTO `inventory_lots` (`id`, `company_id`, `item_id`, `warehouse_id`, `lot_number`, `expires_on`, `quantity`, `created_at`) VALUES ('20', '1', '38', '9', 'HER-2506', '2026-11-30', '150.000', '2026-08-03 16:52:22');
INSERT INTO `inventory_lots` (`id`, `company_id`, `item_id`, `warehouse_id`, `lot_number`, `expires_on`, `quantity`, `created_at`) VALUES ('21', '1', '39', '10', 'RIE-2510', NULL, '180.000', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `inventory_movements`;
CREATE TABLE `inventory_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `season_id` bigint unsigned DEFAULT NULL,
  `block_id` bigint unsigned DEFAULT NULL,
  `movement_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(15,3) NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `movement_date` date NOT NULL,
  `reference` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_inventory_movements_item` (`item_id`),
  KEY `fk_inventory_movements_warehouse` (`warehouse_id`),
  KEY `fk_inventory_movements_season` (`season_id`),
  KEY `fk_inventory_movements_block` (`block_id`),
  KEY `fk_inventory_movements_user` (`created_by`),
  KEY `idx_inventory_movements_reporting` (`company_id`,`item_id`,`movement_date`),
  CONSTRAINT `fk_inventory_movements_block` FOREIGN KEY (`block_id`) REFERENCES `blocks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inventory_movements_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_inventory_movements_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `fk_inventory_movements_season` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inventory_movements_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_inventory_movements_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `inventory_movements` WRITE;
INSERT INTO `inventory_movements` (`id`, `company_id`, `item_id`, `warehouse_id`, `season_id`, `block_id`, `movement_type`, `quantity`, `unit_cost`, `movement_date`, `reference`, `created_by`, `created_at`) VALUES ('21', '1', '31', '9', '12', NULL, 'IN', '1800.000', '920.00', '2025-08-20', 'RECEPCION-DEMO-001', '1', '2026-08-03 16:52:22');
INSERT INTO `inventory_movements` (`id`, `company_id`, `item_id`, `warehouse_id`, `season_id`, `block_id`, `movement_type`, `quantity`, `unit_cost`, `movement_date`, `reference`, `created_by`, `created_at`) VALUES ('22', '1', '33', '10', '12', NULL, 'IN', '1200.000', '185.00', '2025-09-25', 'RECEPCION-DEMO-002', '1', '2026-08-03 16:52:22');
INSERT INTO `inventory_movements` (`id`, `company_id`, `item_id`, `warehouse_id`, `season_id`, `block_id`, `movement_type`, `quantity`, `unit_cost`, `movement_date`, `reference`, `created_by`, `created_at`) VALUES ('23', '1', '35', '9', '12', NULL, 'IN', '120.000', '2800.00', '2025-08-12', 'COMPRA-DEMO-001', '1', '2026-08-03 16:52:22');
INSERT INTO `inventory_movements` (`id`, `company_id`, `item_id`, `warehouse_id`, `season_id`, `block_id`, `movement_type`, `quantity`, `unit_cost`, `movement_date`, `reference`, `created_by`, `created_at`) VALUES ('24', '1', '31', '9', '12', '31', 'OUT', '260.000', '920.00', '2025-09-15', 'APLICACION-CN-01', '1', '2026-08-03 16:52:22');
INSERT INTO `inventory_movements` (`id`, `company_id`, `item_id`, `warehouse_id`, `season_id`, `block_id`, `movement_type`, `quantity`, `unit_cost`, `movement_date`, `reference`, `created_by`, `created_at`) VALUES ('25', '1', '38', '9', '12', '34', 'OUT', '35.000', '6200.00', '2025-10-04', 'APLICACION-RM-01', '1', '2026-08-03 16:52:22');
INSERT INTO `inventory_movements` (`id`, `company_id`, `item_id`, `warehouse_id`, `season_id`, `block_id`, `movement_type`, `quantity`, `unit_cost`, `movement_date`, `reference`, `created_by`, `created_at`) VALUES ('26', '1', '39', '10', '12', NULL, 'IN', '180.000', '1900.00', '2025-10-08', 'COMPRA-DEMO-002', '1', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `inventory_transfers`;
CREATE TABLE `inventory_transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `from_warehouse_id` bigint unsigned NOT NULL,
  `to_warehouse_id` bigint unsigned NOT NULL,
  `quantity` decimal(15,3) NOT NULL,
  `transfer_date` date NOT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_transfers_item` (`item_id`),
  KEY `fk_transfers_from` (`from_warehouse_id`),
  KEY `fk_transfers_to` (`to_warehouse_id`),
  KEY `fk_transfers_user` (`created_by`),
  KEY `idx_transfers_status` (`company_id`,`status`,`transfer_date`),
  CONSTRAINT `fk_transfers_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_transfers_from` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`),
  CONSTRAINT `fk_transfers_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `fk_transfers_to` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`),
  CONSTRAINT `fk_transfers_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `inventory_transfers` WRITE;
INSERT INTO `inventory_transfers` (`id`, `company_id`, `item_id`, `from_warehouse_id`, `to_warehouse_id`, `quantity`, `transfer_date`, `status`, `created_by`, `created_at`) VALUES ('5', '1', '35', '9', '10', '30.000', '2025-09-20', 'RECEIVED', '1', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `labor_entries`;
CREATE TABLE `labor_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `worker_id` bigint unsigned NOT NULL,
  `season_id` bigint unsigned NOT NULL,
  `farm_id` bigint unsigned DEFAULT NULL,
  `block_id` bigint unsigned DEFAULT NULL,
  `labor_date` date NOT NULL,
  `labor_type` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `unit_rate` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) GENERATED ALWAYS AS ((`quantity` * `unit_rate`)) STORED,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'POSTED',
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_labor_worker` (`worker_id`),
  KEY `fk_labor_season` (`season_id`),
  KEY `fk_labor_farm` (`farm_id`),
  KEY `fk_labor_block` (`block_id`),
  KEY `fk_labor_user` (`created_by`),
  KEY `idx_labor_reporting` (`company_id`,`season_id`,`farm_id`,`block_id`,`labor_date`),
  KEY `idx_labor_scope_date` (`company_id`,`labor_date`,`farm_id`,`block_id`,`status`),
  CONSTRAINT `fk_labor_block` FOREIGN KEY (`block_id`) REFERENCES `blocks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_labor_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_labor_farm` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_labor_season` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`),
  CONSTRAINT `fk_labor_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_labor_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `labor_entries` WRITE;
INSERT INTO `labor_entries` (`id`, `company_id`, `worker_id`, `season_id`, `farm_id`, `block_id`, `labor_date`, `labor_type`, `quantity`, `unit_rate`, `total_amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('21', '1', '29', '12', '11', '31', '2025-09-08', 'Poda y conducción', '1.00', '42000.00', '42000.00', 'POSTED', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `labor_entries` (`id`, `company_id`, `worker_id`, `season_id`, `farm_id`, `block_id`, `labor_date`, `labor_type`, `quantity`, `unit_rate`, `total_amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('22', '1', '31', '12', '11', '32', '2025-09-10', 'Raleo de frutos', '1.00', '32000.00', '32000.00', 'POSTED', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `labor_entries` (`id`, `company_id`, `worker_id`, `season_id`, `farm_id`, `block_id`, `labor_date`, `labor_type`, `quantity`, `unit_rate`, `total_amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('23', '1', '34', '12', '12', '34', '2025-09-12', 'Manejo de suelo', '1.00', '45000.00', '45000.00', 'POSTED', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `labor_entries` (`id`, `company_id`, `worker_id`, `season_id`, `farm_id`, `block_id`, `labor_date`, `labor_type`, `quantity`, `unit_rate`, `total_amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('24', '1', '33', '11', '12', '36', '2025-02-18', 'Cosecha', '3.00', '31000.00', '93000.00', 'POSTED', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `labor_entries` (`id`, `company_id`, `worker_id`, `season_id`, `farm_id`, `block_id`, `labor_date`, `labor_type`, `quantity`, `unit_rate`, `total_amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('25', '1', '35', '12', '12', '35', '2025-10-07', 'Fertilización', '1.00', '38000.00', '38000.00', 'POSTED', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `labor_entries` (`id`, `company_id`, `worker_id`, `season_id`, `farm_id`, `block_id`, `labor_date`, `labor_type`, `quantity`, `unit_rate`, `total_amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES ('26', '1', '36', '12', '11', '33', '2025-10-09', 'Selección y embalaje', '1.00', '30000.00', '30000.00', 'POSTED', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `machinery`;
CREATE TABLE `machinery` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `farm_id` bigint unsigned DEFAULT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `machinery_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plate` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meter` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_machinery_company_code` (`company_id`,`code`),
  KEY `fk_machinery_farm` (`farm_id`),
  CONSTRAINT `fk_machinery_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_machinery_farm` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `machinery` WRITE;
INSERT INTO `machinery` (`id`, `company_id`, `farm_id`, `code`, `name`, `machinery_type`, `brand`, `model`, `plate`, `meter`, `status`, `created_at`, `updated_at`) VALUES ('13', '1', '11', 'TR-001', 'Tractor John Deere 5075E', 'TRACTOR', 'John Deere', '5075E', 'XX-AB-11', '1840.00', 'ACTIVE', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `machinery` (`id`, `company_id`, `farm_id`, `code`, `name`, `machinery_type`, `brand`, `model`, `plate`, `meter`, `status`, `created_at`, `updated_at`) VALUES ('14', '1', '12', 'TR-002', 'Tractor New Holland T6050', 'TRACTOR', 'New Holland', 'T6050', 'XX-CD-22', '2310.00', 'ACTIVE', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `machinery` (`id`, `company_id`, `farm_id`, `code`, `name`, `machinery_type`, `brand`, `model`, `plate`, `meter`, `status`, `created_at`, `updated_at`) VALUES ('15', '1', '11', 'PUL-001', 'Pulverizador arrastrado 2000 L', 'PULVERIZADOR', 'Jacto', 'Advance 2000', 'XX-EF-33', '920.00', 'ACTIVE', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `machinery_maintenance`;
CREATE TABLE `machinery_maintenance` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `machinery_id` bigint unsigned NOT NULL,
  `maintenance_date` date NOT NULL,
  `maintenance_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `next_date` date DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_maintenance_machinery` (`machinery_id`),
  KEY `fk_maintenance_user` (`created_by`),
  KEY `idx_maintenance_date` (`company_id`,`maintenance_date`,`next_date`),
  CONSTRAINT `fk_maintenance_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_maintenance_machinery` FOREIGN KEY (`machinery_id`) REFERENCES `machinery` (`id`),
  CONSTRAINT `fk_maintenance_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `machinery_maintenance` WRITE;
INSERT INTO `machinery_maintenance` (`id`, `company_id`, `machinery_id`, `maintenance_date`, `maintenance_type`, `description`, `cost`, `next_date`, `created_by`, `created_at`) VALUES ('13', '1', '13', '2025-08-14', 'PREVENTIVA', 'Cambio de aceite y filtros', '185000.00', '2025-11-14', '1', '2026-08-03 16:52:22');
INSERT INTO `machinery_maintenance` (`id`, `company_id`, `machinery_id`, `maintenance_date`, `maintenance_type`, `description`, `cost`, `next_date`, `created_by`, `created_at`) VALUES ('14', '1', '15', '2025-09-03', 'CORRECTIVA', 'Cambio de boquillas', '264000.00', '2026-03-03', '1', '2026-08-03 16:52:22');
INSERT INTO `machinery_maintenance` (`id`, `company_id`, `machinery_id`, `maintenance_date`, `maintenance_type`, `description`, `cost`, `next_date`, `created_by`, `created_at`) VALUES ('15', '1', '14', '2025-10-10', 'PREVENTIVA', 'Cambio de correas y revisión hidráulica', '315000.00', '2026-01-10', '1', '2026-08-03 16:52:22');
INSERT INTO `machinery_maintenance` (`id`, `company_id`, `machinery_id`, `maintenance_date`, `maintenance_type`, `description`, `cost`, `next_date`, `created_by`, `created_at`) VALUES ('16', '1', '13', '2025-10-20', 'CORRECTIVA', 'Ajuste de freno y sistema eléctrico', '149000.00', '2026-04-20', '1', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `notification_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notifications_company` (`company_id`),
  KEY `idx_notifications_user_read` (`user_id`,`read_at`,`created_at`),
  CONSTRAINT `fk_notifications_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `notifications` WRITE;
INSERT INTO `notifications` (`id`, `company_id`, `user_id`, `notification_type`, `title`, `message`, `read_at`, `created_at`) VALUES ('17', '1', '1', 'STOCK_LOW', 'Revisar stock de azufre', 'El stock de azufre está próximo al mínimo configurado.', NULL, '2026-08-03 16:52:22');
INSERT INTO `notifications` (`id`, `company_id`, `user_id`, `notification_type`, `title`, `message`, `read_at`, `created_at`) VALUES ('18', '1', '1', 'TASK_DUE', 'Mantención próxima', 'El tractor John Deere tiene una mantención programada.', NULL, '2026-08-03 16:52:22');
INSERT INTO `notifications` (`id`, `company_id`, `user_id`, `notification_type`, `title`, `message`, `read_at`, `created_at`) VALUES ('19', '1', '1', 'REQUEST_APPROVED', 'Solicitud aprobada', 'La solicitud de insumos del Fundo Río está lista para atención.', NULL, '2026-08-03 16:52:22');
INSERT INTO `notifications` (`id`, `company_id`, `user_id`, `notification_type`, `title`, `message`, `read_at`, `created_at`) VALUES ('20', '1', '1', 'INVENTORY_TRANSFER', 'Transferencia de bodega', 'Se recibió una transferencia de guantes entre bodegas.', NULL, '2026-08-03 16:52:22');
INSERT INTO `notifications` (`id`, `company_id`, `user_id`, `notification_type`, `title`, `message`, `read_at`, `created_at`) VALUES ('21', '1', '1', 'DOCUMENT_POSTED', 'Documento emitido', 'Se registró una factura de compra del proveedor de riego.', NULL, '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `permissions` WRITE;
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('1', 'dashboard.view', 'Ver dashboard', 'dashboard');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('2', 'setup.manage', 'Completar configuración inicial', 'setup');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('3', 'users.view', 'Ver usuarios', 'users');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('4', 'users.manage', 'Administrar usuarios', 'users');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('5', 'roles.manage', 'Administrar roles y permisos', 'users');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('6', 'masters.manage', 'Administrar maestros agrícolas', 'masters');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('7', 'costs.view', 'Ver costos', 'costs');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('8', 'costs.manage', 'Registrar y corregir costos', 'costs');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('9', 'labor.manage', 'Administrar mano de obra', 'labor');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('10', 'inventory.view', 'Ver bodega', 'inventory');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('11', 'inventory.manage', 'Administrar movimientos de bodega', 'inventory');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('12', 'reports.view', 'Ver informes', 'reports');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('13', 'reports.export', 'Exportar informes', 'reports');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('14', 'procurement.receive', 'Registrar recepción de compras', 'procurement');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('15', 'warehouse.view', 'Ver bodegas', 'warehouse');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('16', 'warehouse.create', 'Crear bodegas y ubicaciones', 'warehouse');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('17', 'warehouse.update', 'Actualizar bodegas y ubicaciones', 'warehouse');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('18', 'lot.create', 'Crear lotes', 'inventory');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('19', 'transfer.create', 'Crear transferencias', 'inventory');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('20', 'transfer.approve', 'Aprobar transferencias', 'inventory');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('21', 'requests.view', 'Ver solicitudes internas', 'requests');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('22', 'requests.create', 'Crear solicitudes internas', 'requests');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('23', 'requests.approve', 'Aprobar solicitudes internas', 'requests');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('24', 'requests.fulfill', 'Atender solicitudes internas', 'requests');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('25', 'notifications.view', 'Ver notificaciones', 'notifications');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('26', 'notifications.update', 'Actualizar notificaciones', 'notifications');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('27', 'tasks.view', 'Ver tareas', 'tasks');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('28', 'tasks.create', 'Crear tareas', 'tasks');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('29', 'tasks.update', 'Actualizar tareas', 'tasks');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('30', 'calendar.view', 'Ver calendario', 'calendar');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('31', 'calendar.create', 'Crear eventos de calendario', 'calendar');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('32', 'documents.view', 'Ver documentos', 'documents');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('33', 'documents.create', 'Crear documentos', 'documents');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('34', 'attachments.create', 'Adjuntar archivos', 'documents');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('35', 'api_tokens.manage', 'Administrar tokens API', 'api');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('36', 'masters.view', 'Ver maestros agrícolas', 'masters');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('37', 'masters.create', 'Crear maestros agrícolas', 'masters');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('38', 'production.view', 'Ver producción', 'production');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('39', 'production.create', 'Registrar producción', 'production');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('40', 'procurement.view', 'Ver compras', 'procurement');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('41', 'procurement.create', 'Crear compras', 'procurement');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('42', 'budgets.view', 'Ver presupuestos', 'budgets');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('43', 'budgets.create', 'Crear presupuestos', 'budgets');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('44', 'machinery.view', 'Ver maquinaria', 'machinery');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('45', 'machinery.create', 'Crear maquinaria', 'machinery');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('46', 'costs.create', 'Registrar costos', 'costs');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('47', 'inventory.create', 'Crear insumos y movimientos', 'inventory');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('48', 'labor.view', 'Ver mano de obra', 'labor');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('49', 'labor.create', 'Registrar mano de obra', 'labor');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('50', 'demo.manage', 'Administrar datos demo', 'demo');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('51', 'purchase_invoices.view', 'Ver facturas de compra', 'purchase_invoices');
INSERT INTO `permissions` (`id`, `code`, `name`, `module`) VALUES ('52', 'purchase_invoices.create', 'Registrar facturas de compra', 'purchase_invoices');
UNLOCK TABLES;

DROP TABLE IF EXISTS `production_entries`;
CREATE TABLE `production_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `season_id` bigint unsigned NOT NULL,
  `farm_id` bigint unsigned DEFAULT NULL,
  `block_id` bigint unsigned DEFAULT NULL,
  `species_id` bigint unsigned DEFAULT NULL,
  `production_date` date NOT NULL,
  `activity` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(15,3) NOT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quality` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_production_season` (`season_id`),
  KEY `fk_production_farm` (`farm_id`),
  KEY `fk_production_block` (`block_id`),
  KEY `fk_production_species` (`species_id`),
  KEY `fk_production_user` (`created_by`),
  KEY `idx_production_reporting` (`company_id`,`season_id`,`farm_id`,`block_id`,`production_date`),
  KEY `idx_production_scope_date` (`company_id`,`production_date`,`farm_id`,`block_id`),
  CONSTRAINT `fk_production_block` FOREIGN KEY (`block_id`) REFERENCES `blocks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_production_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_production_farm` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_production_season` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`),
  CONSTRAINT `fk_production_species` FOREIGN KEY (`species_id`) REFERENCES `species` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_production_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `production_entries` WRITE;
INSERT INTO `production_entries` (`id`, `company_id`, `season_id`, `farm_id`, `block_id`, `species_id`, `production_date`, `activity`, `quantity`, `unit`, `quality`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('17', '1', '11', '11', '31', '16', '2025-01-20', 'Cosecha de cereza', '18400.000', 'KG', 'EXPORTABLE', 'Calibre principal de exportación', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `production_entries` (`id`, `company_id`, `season_id`, `farm_id`, `block_id`, `species_id`, `production_date`, `activity`, `quantity`, `unit`, `quality`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('18', '1', '11', '12', '34', '18', '2025-03-14', 'Cosecha de manzana', '32600.000', 'KG', 'PRIMERA', NULL, '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `production_entries` (`id`, `company_id`, `season_id`, `farm_id`, `block_id`, `species_id`, `production_date`, `activity`, `quantity`, `unit`, `quality`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('19', '1', '12', '11', '33', '17', '2026-02-05', 'Proyección de cosecha', '24500.000', 'KG', 'ESTIMADA', NULL, '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `production_entries` (`id`, `company_id`, `season_id`, `farm_id`, `block_id`, `species_id`, `production_date`, `activity`, `quantity`, `unit`, `quality`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('20', '1', '12', '11', '32', '16', '2025-11-28', 'Cosecha de cereza secundaria', '12750.000', 'KG', 'SEGUNDA', 'Fruta destinada a mercado nacional', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `production_entries` (`id`, `company_id`, `season_id`, `farm_id`, `block_id`, `species_id`, `production_date`, `activity`, `quantity`, `unit`, `quality`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('21', '1', '12', '12', '35', '18', '2025-12-03', 'Control de calibre y madurez', '15420.000', 'KG', 'PRIMERA', NULL, '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `purchase_invoices`;
CREATE TABLE `purchase_invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `purchase_order_id` bigint unsigned DEFAULT NULL,
  `purchase_reception_id` bigint unsigned DEFAULT NULL,
  `document_id` bigint unsigned DEFAULT NULL,
  `invoice_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CLP',
  `net_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `notes` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_purchase_invoices_supplier_number` (`company_id`,`supplier_id`,`invoice_number`),
  KEY `fk_purchase_invoices_supplier` (`supplier_id`),
  KEY `fk_purchase_invoices_order` (`purchase_order_id`),
  KEY `fk_purchase_invoices_reception` (`purchase_reception_id`),
  KEY `fk_purchase_invoices_document` (`document_id`),
  KEY `fk_purchase_invoices_user` (`created_by`),
  KEY `idx_purchase_invoices_status_date` (`company_id`,`status`,`due_date`),
  CONSTRAINT `fk_purchase_invoices_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_purchase_invoices_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_purchase_invoices_order` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_purchase_invoices_reception` FOREIGN KEY (`purchase_reception_id`) REFERENCES `purchase_receptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_purchase_invoices_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `fk_purchase_invoices_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `purchase_order_items`;
CREATE TABLE `purchase_order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned DEFAULT NULL,
  `description` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(15,3) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `received_quantity` decimal(15,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`id`),
  KEY `fk_purchase_items_order` (`purchase_order_id`),
  KEY `fk_purchase_items_inventory` (`item_id`),
  CONSTRAINT `fk_purchase_items_inventory` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_purchase_items_order` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `purchase_order_items` WRITE;
INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `item_id`, `description`, `quantity`, `unit_price`, `received_quantity`) VALUES ('17', '17', '31', 'Fertilizante NPK 20-20-20', '1800.000', '920.00', '1800.000');
INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `item_id`, `description`, `quantity`, `unit_price`, `received_quantity`) VALUES ('18', '18', '33', 'Cinta de riego 16 mm', '2400.000', '185.00', '1200.000');
INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `item_id`, `description`, `quantity`, `unit_price`, `received_quantity`) VALUES ('19', '19', '34', 'Caja plástica de cosecha', '600.000', '6500.00', '0.000');
INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `item_id`, `description`, `quantity`, `unit_price`, `received_quantity`) VALUES ('20', '21', '38', 'Herbicida selectivo', '120.000', '6200.00', '0.000');
INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `item_id`, `description`, `quantity`, `unit_price`, `received_quantity`) VALUES ('21', '20', '39', 'Manguera para riego', '500.000', '1900.00', '0.000');
UNLOCK TABLES;

DROP TABLE IF EXISTS `purchase_orders`;
CREATE TABLE `purchase_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `season_id` bigint unsigned DEFAULT NULL,
  `farm_id` bigint unsigned DEFAULT NULL,
  `order_number` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_date` date NOT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_purchase_orders_company_number` (`company_id`,`order_number`),
  KEY `fk_purchase_orders_supplier` (`supplier_id`),
  KEY `fk_purchase_orders_season` (`season_id`),
  KEY `fk_purchase_orders_farm` (`farm_id`),
  KEY `fk_purchase_orders_user` (`created_by`),
  CONSTRAINT `fk_purchase_orders_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_purchase_orders_farm` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_purchase_orders_season` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_purchase_orders_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `fk_purchase_orders_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `purchase_orders` WRITE;
INSERT INTO `purchase_orders` (`id`, `company_id`, `supplier_id`, `season_id`, `farm_id`, `order_number`, `order_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('17', '1', '13', '12', '11', 'OC-2025-001', '2025-08-05', 'RECEIVED', 'Fertilizantes para inicio de temporada', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `purchase_orders` (`id`, `company_id`, `supplier_id`, `season_id`, `farm_id`, `order_number`, `order_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('18', '1', '14', '12', '12', 'OC-2025-002', '2025-09-12', 'PARTIAL', 'Reposición de materiales de riego', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `purchase_orders` (`id`, `company_id`, `supplier_id`, `season_id`, `farm_id`, `order_number`, `order_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('19', '1', '13', '12', '11', 'OC-2025-003', '2025-10-18', 'SENT', 'Elementos para cosecha', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `purchase_orders` (`id`, `company_id`, `supplier_id`, `season_id`, `farm_id`, `order_number`, `order_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('20', '1', '15', '12', '12', 'OC-2025-004', '2025-10-20', 'SENT', 'Repuestos para tractor y bomba', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `purchase_orders` (`id`, `company_id`, `supplier_id`, `season_id`, `farm_id`, `order_number`, `order_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('21', '1', '13', '12', '11', 'OC-2025-005', '2025-11-03', 'PENDING', 'Insumos para tratamiento foliar', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `purchase_reception_items`;
CREATE TABLE `purchase_reception_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reception_id` bigint unsigned NOT NULL,
  `purchase_order_item_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned DEFAULT NULL,
  `quantity` decimal(15,3) NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `fk_reception_items_reception` (`reception_id`),
  KEY `fk_reception_items_inventory` (`item_id`),
  KEY `idx_reception_items_order` (`purchase_order_item_id`),
  CONSTRAINT `fk_reception_items_inventory` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reception_items_order_item` FOREIGN KEY (`purchase_order_item_id`) REFERENCES `purchase_order_items` (`id`),
  CONSTRAINT `fk_reception_items_reception` FOREIGN KEY (`reception_id`) REFERENCES `purchase_receptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `purchase_reception_items` WRITE;
INSERT INTO `purchase_reception_items` (`id`, `reception_id`, `purchase_order_item_id`, `item_id`, `quantity`, `unit_cost`) VALUES ('11', '11', '17', '31', '1800.000', '920.00');
INSERT INTO `purchase_reception_items` (`id`, `reception_id`, `purchase_order_item_id`, `item_id`, `quantity`, `unit_cost`) VALUES ('12', '12', '18', '33', '1200.000', '185.00');
INSERT INTO `purchase_reception_items` (`id`, `reception_id`, `purchase_order_item_id`, `item_id`, `quantity`, `unit_cost`) VALUES ('13', '13', '21', '39', '500.000', '1900.00');
UNLOCK TABLES;

DROP TABLE IF EXISTS `purchase_receptions`;
CREATE TABLE `purchase_receptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `purchase_order_id` bigint unsigned NOT NULL,
  `document_id` bigint unsigned DEFAULT NULL,
  `received_on` date NOT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'POSTED',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_receptions_order` (`purchase_order_id`),
  KEY `fk_receptions_document` (`document_id`),
  KEY `fk_receptions_user` (`created_by`),
  KEY `idx_receptions_company_date` (`company_id`,`received_on`,`status`),
  CONSTRAINT `fk_receptions_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_receptions_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_receptions_order` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`),
  CONSTRAINT `fk_receptions_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `purchase_receptions` WRITE;
INSERT INTO `purchase_receptions` (`id`, `company_id`, `purchase_order_id`, `document_id`, `received_on`, `status`, `notes`, `created_by`, `created_at`) VALUES ('11', '1', '17', NULL, '2025-08-20', 'POSTED', 'Recepción completa', '1', '2026-08-03 16:52:22');
INSERT INTO `purchase_receptions` (`id`, `company_id`, `purchase_order_id`, `document_id`, `received_on`, `status`, `notes`, `created_by`, `created_at`) VALUES ('12', '1', '18', NULL, '2025-09-25', 'POSTED', 'Recepción parcial', '1', '2026-08-03 16:52:22');
INSERT INTO `purchase_receptions` (`id`, `company_id`, `purchase_order_id`, `document_id`, `received_on`, `status`, `notes`, `created_by`, `created_at`) VALUES ('13', '1', '20', NULL, '2025-10-25', 'POSTED', 'Repuestos de maquinaria', '1', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `restore_records`;
CREATE TABLE `restore_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `backup_id` bigint unsigned DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'STARTED',
  `error_message` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_restores_company` (`company_id`),
  KEY `fk_restores_backup` (`backup_id`),
  KEY `fk_restores_user` (`created_by`),
  CONSTRAINT `fk_restores_backup` FOREIGN KEY (`backup_id`) REFERENCES `backup_records` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_restores_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_restores_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_role_permissions_permission` (`permission_id`),
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `role_permissions` WRITE;
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '1');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '1');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '1');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '1');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '1');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '2');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '2');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '2');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '3');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '3');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '3');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '4');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '4');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '4');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '5');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '5');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '5');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '6');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '6');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '6');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '7');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '7');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '7');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '7');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '8');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '8');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '8');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '8');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '9');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '9');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '9');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '9');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '9');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '10');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '10');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '10');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '10');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '11');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '11');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '11');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '11');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '12');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '12');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '12');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '13');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '13');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '13');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '14');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '14');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '14');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '15');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '15');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '15');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '16');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '16');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '16');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '17');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '17');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '17');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '18');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '18');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '18');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '18');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '19');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '19');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '19');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '19');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '20');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '20');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '20');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '20');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '21');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '21');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '21');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '22');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '22');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '22');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '23');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '23');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '23');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '24');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '24');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '24');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '25');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '25');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '25');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '26');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '26');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '26');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '27');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '27');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '27');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '28');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '28');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '28');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '29');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '29');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '29');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '30');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '30');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '30');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '30');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '30');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '31');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '31');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '31');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '31');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '31');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '32');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '32');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '32');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '32');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '32');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '33');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '33');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '33');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '33');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '33');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '34');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '34');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '34');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '34');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '34');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '35');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '35');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '35');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '36');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '36');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '36');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '37');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '37');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '37');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '38');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '38');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '38');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '39');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '39');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '39');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '40');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '40');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '40');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '41');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '41');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '41');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '42');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '42');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '42');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '42');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '43');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '43');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '43');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '43');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '44');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '44');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '44');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '45');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '45');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '45');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '46');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '46');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '46');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '46');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '47');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('3', '47');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '47');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '47');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '48');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '48');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '48');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '48');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '49');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('2', '49');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '49');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '49');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '50');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '50');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '50');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '51');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '51');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '51');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('1', '52');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('4', '52');
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES ('5', '52');
UNLOCK TABLES;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_company_name` (`company_id`,`name`),
  CONSTRAINT `fk_roles_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `roles` WRITE;
INSERT INTO `roles` (`id`, `company_id`, `name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES ('1', '1', 'Administrador', 'Acceso completo a la gestión de la agrícola', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `roles` (`id`, `company_id`, `name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES ('2', '1', 'RRHH', 'Gestiona trabajadores, asistencia, contratos, productividad y administración del personal.', '0', '2026-08-03 17:25:56', '2026-08-03 17:44:00');
INSERT INTO `roles` (`id`, `company_id`, `name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES ('3', '1', 'Contabilidad', 'Controla ingresos, egresos, presupuestos, costos, pagos e indicadores financieros.', '0', '2026-08-03 17:46:39', '2026-08-03 17:46:39');
INSERT INTO `roles` (`id`, `company_id`, `name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES ('4', '1', 'Gerencia', 'Visualiza indicadores estratégicos, KPIs, rentabilidad y el desempeño global de la empresa para apoyar la toma de decisiones.', '0', '2026-08-03 17:47:25', '2026-08-03 17:47:25');
INSERT INTO `roles` (`id`, `company_id`, `name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES ('5', '1', 'Super Administrador', 'Acceso Total al Sistema', '0', '2026-08-04 12:11:30', '2026-08-04 12:11:30');
UNLOCK TABLES;

DROP TABLE IF EXISTS `schema_migrations`;
CREATE TABLE `schema_migrations` (
  `version` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applied_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `schema_migrations` WRITE;
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('001_initial_schema', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('002_labor_schema', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('003_production_schema', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('004_procurement_schema', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('005_budget_schema', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('006_machinery_schema', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('007_module_permissions', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('008_platform_entities', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('009_system_logs', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('010_system_catalogs', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('011_catalog_backed_values', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('012_purchase_receptions', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('013_procurement_reception_permission', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('014_inventory_warehouse_scope', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('015_warehouse_permissions', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('016_internal_request_items', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('017_internal_request_permissions', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('018_notification_permissions', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('019_tasks_calendar_permissions', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('020_document_permissions', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('021_api_token_permissions', '2026-07-28 02:51:37');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('022_complete_module_permissions', '2026-07-28 03:02:48');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('023_demo_data_manager', '2026-07-28 03:46:27');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('024_purchase_invoices', '2026-07-30 18:04:38');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('024_reporting_indexes', '2026-08-03 14:06:41');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('025_reporting_indexes', '2026-08-03 14:22:52');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('026_user_permissions', '2026-08-03 18:05:39');
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('027_worker_profile_schema', '2026-08-04 14:28:59');
UNLOCK TABLES;

DROP TABLE IF EXISTS `seasons`;
CREATE TABLE `seasons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `starts_on` date NOT NULL,
  `ends_on` date NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seasons_company_name` (`company_id`,`name`),
  CONSTRAINT `fk_seasons_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `seasons` WRITE;
INSERT INTO `seasons` (`id`, `company_id`, `name`, `starts_on`, `ends_on`, `active`) VALUES ('11', '1', '2024-2025', '2024-07-01', '2025-06-30', '1');
INSERT INTO `seasons` (`id`, `company_id`, `name`, `starts_on`, `ends_on`, `active`) VALUES ('12', '1', '2025-2026', '2025-07-01', '2026-06-30', '1');
UNLOCK TABLES;

DROP TABLE IF EXISTS `species`;
CREATE TABLE `species` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variety` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_species_company_name_variety` (`company_id`,`name`,`variety`),
  CONSTRAINT `fk_species_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `species` WRITE;
INSERT INTO `species` (`id`, `company_id`, `name`, `variety`, `active`) VALUES ('16', '1', 'Cerezo', 'Santina', '1');
INSERT INTO `species` (`id`, `company_id`, `name`, `variety`, `active`) VALUES ('17', '1', 'Uva de mesa', 'Red Globe', '1');
INSERT INTO `species` (`id`, `company_id`, `name`, `variety`, `active`) VALUES ('18', '1', 'Manzano', 'Gala', '1');
UNLOCK TABLES;

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `tax_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_name` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_suppliers_company_tax_id` (`company_id`,`tax_id`),
  CONSTRAINT `fk_suppliers_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `suppliers` WRITE;
INSERT INTO `suppliers` (`id`, `company_id`, `tax_id`, `business_name`, `contact_name`, `email`, `phone`, `address`, `active`, `created_at`, `updated_at`) VALUES ('13', '1', '76.111.222-3', 'Agroinsumos del Sur SpA', 'Felipe Contreras', 'ventas@agroinsumosur.cl', '+56991234567', 'Av. Central 450, Talca', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `suppliers` (`id`, `company_id`, `tax_id`, `business_name`, `contact_name`, `email`, `phone`, `address`, `active`, `created_at`, `updated_at`) VALUES ('14', '1', '77.222.333-4', 'RiegoTec Limitada', 'Laura Pérez', 'contacto@riegotec.cl', '+56992345678', 'Ruta 5 Sur Km 250', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `suppliers` (`id`, `company_id`, `tax_id`, `business_name`, `contact_name`, `email`, `phone`, `address`, `active`, `created_at`, `updated_at`) VALUES ('15', '1', '78.333.444-5', 'Maquinaria Maule SpA', 'Andrés Vera', 'servicio@maqmaule.cl', '+56993456789', 'Camino Las Rastras 120', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `system_catalog_values`;
CREATE TABLE `system_catalog_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `catalog_id` bigint unsigned NOT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `code` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `metadata_json` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_catalog_values_scope_code` (`catalog_id`,`company_id`,`code`),
  KEY `fk_catalog_values_company` (`company_id`),
  KEY `idx_catalog_values_lookup` (`catalog_id`,`company_id`,`active`,`sort_order`),
  CONSTRAINT `fk_catalog_values_catalog` FOREIGN KEY (`catalog_id`) REFERENCES `system_catalogs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_catalog_values_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `system_catalog_values` WRITE;
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('1', '4', NULL, 'CLP', 'Peso chileno', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('2', '3', NULL, 'UN', 'Unidad', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('3', '3', NULL, 'KG', 'Kilogramo', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('4', '3', NULL, 'L', 'Litro', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('5', '11', NULL, 'LOW', 'Baja', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('6', '11', NULL, 'NORMAL', 'Normal', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('7', '11', NULL, 'HIGH', 'Alta', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('8', '11', NULL, 'URGENT', 'Urgente', '40', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('9', '12', NULL, 'DRAFT', 'Borrador', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('10', '12', NULL, 'POSTED', 'Registrado', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('11', '12', NULL, 'VOID', 'Anulado', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('12', '13', NULL, 'ADMINISTRACION', 'Administración', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('13', '13', NULL, 'MANO_DE_OBRA', 'Mano de obra', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('14', '13', NULL, 'INVERSION', 'Inversión', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('15', '13', NULL, 'SERVICIOS_GASTOS', 'Servicios y gastos', '40', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('16', '13', NULL, 'BODEGA', 'Bodega', '50', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('17', '14', NULL, 'INSUMO', 'Insumo', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('18', '14', NULL, 'FERRETERIA', 'Ferretería', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('19', '14', NULL, 'MAQUINARIA', 'Maquinaria', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('20', '14', NULL, 'HERRAMIENTA', 'Herramienta', '40', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('21', '14', NULL, 'OTRO', 'Otro', '50', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('22', '15', NULL, 'IN', 'Entrada', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('23', '15', NULL, 'OUT', 'Salida', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('24', '15', NULL, 'ADJUSTMENT', 'Ajuste', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('25', '16', NULL, 'PERMANENTE', 'Permanente', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('26', '16', NULL, 'TEMPORAL', 'Temporal', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('27', '16', NULL, 'CONTRATISTA', 'Contratista', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('28', '17', NULL, 'PREVENTIVE', 'Preventiva', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('29', '17', NULL, 'CORRECTIVE', 'Correctiva', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('30', '6', NULL, 'TRACTOR', 'Tractor', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('31', '6', NULL, 'IMPLEMENT', 'Implemento', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('32', '8', NULL, 'GENERAL', 'Labor general', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('33', '8', NULL, 'HARVEST', 'Cosecha', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('34', '9', NULL, 'PREMIUM', 'Premium', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('35', '9', NULL, 'STANDARD', 'Estándar', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('36', '18', NULL, 'DRAFT', 'Borrador', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('37', '18', NULL, 'SENT', 'Enviada', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('38', '18', NULL, 'PARTIAL', 'Parcial', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('39', '18', NULL, 'RECEIVED', 'Recibida', '40', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('40', '18', NULL, 'CANCELLED', 'Cancelada', '50', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('41', '19', NULL, 'DRAFT', 'Borrador', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('42', '19', NULL, 'REQUESTED', 'Solicitada', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('43', '19', NULL, 'APPROVED', 'Aprobada', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('44', '19', NULL, 'FULFILLED', 'Atendida', '40', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('45', '19', NULL, 'REJECTED', 'Rechazada', '50', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('46', '20', NULL, 'DRAFT', 'Borrador', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('47', '20', NULL, 'VALID', 'Válido', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('48', '20', NULL, 'VOID', 'Anulado', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('49', '21', NULL, 'ACTIVE', 'Activa', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('50', '21', NULL, 'MAINTENANCE', 'En mantención', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('51', '21', NULL, 'INACTIVE', 'Inactiva', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('52', '22', NULL, 'STARTED', 'Iniciado', '10', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('53', '22', NULL, 'COMPLETED', 'Completado', '20', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalog_values` (`id`, `catalog_id`, `company_id`, `code`, `label`, `sort_order`, `active`, `metadata_json`, `created_at`, `updated_at`) VALUES ('54', '22', NULL, 'FAILED', 'Fallido', '30', '1', NULL, '2026-07-28 02:51:37', '2026-07-28 02:51:37');
UNLOCK TABLES;

DROP TABLE IF EXISTS `system_catalogs`;
CREATE TABLE `system_catalogs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scope` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'COMPANY',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_system_catalogs_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `system_catalogs` WRITE;
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('1', 'COST_CENTER_CATEGORY', 'Categorías de centros de costo', 'COMPANY', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('2', 'DOCUMENT_TYPE', 'Tipos de documento', 'COMPANY', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('3', 'MEASUREMENT_UNIT', 'Unidades de medida', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('4', 'CURRENCY', 'Monedas', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('5', 'PAYMENT_METHOD', 'Formas de pago', 'COMPANY', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('6', 'MACHINERY_TYPE', 'Tipos de maquinaria', 'COMPANY', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('7', 'FUEL_TYPE', 'Tipos de combustible', 'COMPANY', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('8', 'LABOR_TYPE', 'Tipos de labor', 'COMPANY', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('9', 'PRODUCTION_QUALITY', 'Calidades de producción', 'COMPANY', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('10', 'CANCELLATION_REASON', 'Motivos de anulación', 'COMPANY', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('11', 'TASK_PRIORITY', 'Prioridades de tareas', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('12', 'RECORD_STATUS', 'Estados generales', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('13', 'COST_CATEGORY', 'Categorías de costos', 'COMPANY', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('14', 'INVENTORY_CATEGORY', 'Categorías de inventario', 'COMPANY', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('15', 'INVENTORY_MOVEMENT_TYPE', 'Tipos de movimiento de inventario', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('16', 'WORKER_TYPE', 'Tipos de trabajador', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('17', 'MAINTENANCE_TYPE', 'Tipos de mantención', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('18', 'PURCHASE_ORDER_STATUS', 'Estados de órdenes de compra', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('19', 'REQUEST_STATUS', 'Estados de solicitudes internas', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('20', 'DOCUMENT_STATUS', 'Estados de documentos', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('21', 'MACHINERY_STATUS', 'Estados de maquinaria', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
INSERT INTO `system_catalogs` (`id`, `code`, `name`, `scope`, `active`, `created_at`, `updated_at`) VALUES ('22', 'BACKUP_STATUS', 'Estados de respaldos', 'SYSTEM', '1', '2026-07-28 02:51:37', '2026-07-28 02:51:37');
UNLOCK TABLES;

DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `level` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `context_json` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_system_logs_user` (`user_id`),
  KEY `idx_system_logs_lookup` (`company_id`,`level`,`channel`,`created_at`),
  CONSTRAINT `fk_system_logs_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_system_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `system_logs` WRITE;
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('1', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-03 14:59:36');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('2', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-03 14:59:36');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('3', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 14:59:50');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('4', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:00:20');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('5', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:00:31');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('6', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:00:35');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('7', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:00:48');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('8', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:01:20');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('9', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:01:28');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('10', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:01:35');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('11', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:01:39');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('12', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:01:41');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('13', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:01:52');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('14', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:02:19');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('15', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:02:38');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('16', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:02:41');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('17', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:02:57');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('18', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:02:59');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('19', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:03:12');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('20', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:03:20');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('21', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:03:21');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('22', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:03:22');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('23', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:03:30');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('24', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-03 15:04:00');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('25', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-03 15:04:00');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('26', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-03 15:04:06');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('27', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-03 15:04:06');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('28', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-03 15:04:08');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('29', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-03 15:04:08');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('30', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-03 15:04:28');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('31', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-03 15:04:28');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('32', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:04:38');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('33', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:05:18');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('34', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:05:30');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('35', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:06:06');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('36', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:06:07');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('37', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:06:08');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('38', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:06:08');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('39', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:06:21');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('40', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:06:22');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('41', '1', '1', 'INFO', 'tools.repair', 'Reparación de sistema iniciada', NULL, '2026-08-03 15:06:23');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('42', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-03 15:06:26');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('43', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-03 15:06:26');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('44', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-03 15:06:28');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('45', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-03 15:06:28');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('46', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-03 15:09:03');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('47', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-03 15:09:03');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('48', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-03 15:11:36');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('49', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-03 15:11:36');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('50', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-03 15:11:42');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('51', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-03 15:11:42');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('52', '1', '1', 'WARNING', 'tools.backup', 'No se encontró mysqldump; usando respaldo PHP interno', NULL, '2026-08-03 18:07:25');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('53', '1', '1', 'INFO', 'tools.backup', 'Respaldo creado', '{"backup_id": 8, "file_path": "storage/backups/backup_20260803_180725.sql", "file_size": 263073}', '2026-08-03 18:07:25');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('54', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-03 18:07:40');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('55', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-03 18:07:40');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('56', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema iniciada', NULL, '2026-08-04 14:30:04');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('57', '1', '1', 'INFO', 'tools.schema', 'Sincronización de esquema completada', NULL, '2026-08-04 14:30:04');
INSERT INTO `system_logs` (`id`, `company_id`, `user_id`, `level`, `channel`, `message`, `context_json`, `created_at`) VALUES ('58', '1', '1', 'WARNING', 'tools.backup', 'No se encontró mysqldump; usando respaldo PHP interno', NULL, '2026-08-04 17:37:09');
UNLOCK TABLES;

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `priority` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NORMAL',
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_tasks_assigned` (`assigned_to`),
  KEY `fk_tasks_creator` (`created_by`),
  KEY `idx_tasks_status_date` (`company_id`,`status`,`due_date`),
  CONSTRAINT `fk_tasks_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tasks_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tasks_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `tasks` WRITE;
INSERT INTO `tasks` (`id`, `company_id`, `assigned_to`, `created_by`, `title`, `description`, `due_date`, `priority`, `status`, `created_at`, `updated_at`) VALUES ('17', '1', '1', '1', 'Confirmar programa de fertilización', 'Validar cantidades y fechas con el encargado de producción.', '2025-10-15', 'HIGH', 'OPEN', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `tasks` (`id`, `company_id`, `assigned_to`, `created_by`, `title`, `description`, `due_date`, `priority`, `status`, `created_at`, `updated_at`) VALUES ('18', '1', '1', '1', 'Coordinar mantención del tractor', 'Agendar servicio preventivo con el proveedor.', '2025-11-10', 'NORMAL', 'IN_PROGRESS', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `tasks` (`id`, `company_id`, `assigned_to`, `created_by`, `title`, `description`, `due_date`, `priority`, `status`, `created_at`, `updated_at`) VALUES ('19', '1', '1', '1', 'Preparar informe de temporada', 'Revisar costos y producción acumulada.', '2025-12-20', 'NORMAL', 'DONE', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `tasks` (`id`, `company_id`, `assigned_to`, `created_by`, `title`, `description`, `due_date`, `priority`, `status`, `created_at`, `updated_at`) VALUES ('20', '1', '1', '1', 'Revisar inventario crítico', 'Confirmar niveles de manguera y herbicida.', '2025-10-18', 'HIGH', 'OPEN', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `tasks` (`id`, `company_id`, `assigned_to`, `created_by`, `title`, `description`, `due_date`, `priority`, `status`, `created_at`, `updated_at`) VALUES ('21', '1', '1', '1', 'Actualizar plan de exportación', 'Alinear cortes y embalajes con la demanda del cliente.', '2025-10-25', 'NORMAL', 'IN_PROGRESS', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE `user_permissions` (
  `user_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `fk_user_permissions_permission` (`permission_id`),
  CONSTRAINT `fk_user_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `user_permissions` WRITE;
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '1');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '7');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '8');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '9');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '10');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '11');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '18');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '19');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '20');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '30');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '31');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '32');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '33');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '34');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '42');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '43');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '46');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '47');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '48');
INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES ('2', '49');
UNLOCK TABLES;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `full_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_company_email` (`company_id`,`email`),
  KEY `fk_users_role` (`role_id`),
  KEY `idx_users_active` (`company_id`,`active`),
  CONSTRAINT `fk_users_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `users` WRITE;
INSERT INTO `users` (`id`, `company_id`, `role_id`, `full_name`, `email`, `password_hash`, `phone`, `active`, `last_login_at`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'JCARES', 'jcares@pccurico.cl', '$2y$10$yAf3t.XlDoTbzKMJ39Izp.j2Zhw5qdNjnrh11rPF/Wwij1Y0/EvpO', '+569984744424', '1', '2026-08-03 23:51:58', '2026-07-28 02:51:37', '2026-08-03 23:51:58');
INSERT INTO `users` (`id`, `company_id`, `role_id`, `full_name`, `email`, `password_hash`, `phone`, `active`, `last_login_at`, `created_at`, `updated_at`) VALUES ('2', '1', '4', 'jose', 'jose.cares.a@gmail.com', '$2y$10$yAf3t.XlDoTbzKMJ39Izp.j2Zhw5qdNjnrh11rPF/Wwij1Y0/EvpO', NULL, '1', '2026-08-03 21:32:56', '2026-08-03 17:24:28', '2026-08-03 21:32:56');
UNLOCK TABLES;

DROP TABLE IF EXISTS `warehouse_locations`;
CREATE TABLE `warehouse_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_locations_warehouse_code` (`warehouse_id`,`code`),
  KEY `fk_locations_company` (`company_id`),
  CONSTRAINT `fk_locations_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_locations_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `warehouse_locations` WRITE;
INSERT INTO `warehouse_locations` (`id`, `company_id`, `warehouse_id`, `code`, `name`) VALUES ('15', '1', '9', 'INS', 'Insumos agrícolas');
INSERT INTO `warehouse_locations` (`id`, `company_id`, `warehouse_id`, `code`, `name`) VALUES ('16', '1', '9', 'EPP', 'Elementos de protección');
INSERT INTO `warehouse_locations` (`id`, `company_id`, `warehouse_id`, `code`, `name`) VALUES ('17', '1', '10', 'INS', 'Insumos agrícolas');
INSERT INTO `warehouse_locations` (`id`, `company_id`, `warehouse_id`, `code`, `name`) VALUES ('18', '1', '10', 'PAL', 'Pallets y embalaje');
UNLOCK TABLES;

DROP TABLE IF EXISTS `warehouses`;
CREATE TABLE `warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `farm_id` bigint unsigned DEFAULT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_warehouses_company_code` (`company_id`,`code`),
  KEY `fk_warehouses_farm` (`farm_id`),
  CONSTRAINT `fk_warehouses_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_warehouses_farm` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `warehouses` WRITE;
INSERT INTO `warehouses` (`id`, `company_id`, `farm_id`, `code`, `name`, `active`, `created_at`) VALUES ('9', '1', '11', 'BOD-NORTE', 'Bodega Campo Norte', '1', '2026-08-03 16:52:22');
INSERT INTO `warehouses` (`id`, `company_id`, `farm_id`, `code`, `name`, `active`, `created_at`) VALUES ('10', '1', '12', 'BOD-RIO', 'Bodega Fundo Río', '1', '2026-08-03 16:52:22');
UNLOCK TABLES;

DROP TABLE IF EXISTS `worker_assignments`;
CREATE TABLE `worker_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `farm_id` bigint unsigned DEFAULT NULL,
  `block_id` bigint unsigned DEFAULT NULL,
  `department` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_worker_assignments_farm` (`farm_id`),
  KEY `fk_worker_assignments_block` (`block_id`),
  KEY `idx_worker_assignments_worker` (`worker_id`,`start_date`),
  CONSTRAINT `fk_worker_assignments_block` FOREIGN KEY (`block_id`) REFERENCES `blocks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_worker_assignments_farm` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_worker_assignments_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_bank_accounts`;
CREATE TABLE `worker_bank_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `bank_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_type` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `swift_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_worker_bank_primary` (`worker_id`,`is_primary`),
  KEY `idx_worker_bank_accounts_worker` (`worker_id`),
  CONSTRAINT `fk_worker_bank_accounts_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_benefits`;
CREATE TABLE `worker_benefits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `health_system` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `afp_name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pension_type` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra_benefit` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `health_plan` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_worker_benefits_worker` (`worker_id`),
  CONSTRAINT `fk_worker_benefits_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_contracts`;
CREATE TABLE `worker_contracts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `contract_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `weekly_hours` decimal(6,2) NOT NULL DEFAULT '45.00',
  `base_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CLP',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_worker_contracts_active` (`worker_id`,`status`),
  KEY `idx_worker_contracts_worker` (`worker_id`,`start_date`),
  CONSTRAINT `fk_worker_contracts_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_documents`;
CREATE TABLE `worker_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `document_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_number` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VALID',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_worker_documents_worker` (`worker_id`,`document_type`),
  CONSTRAINT `fk_worker_documents_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_epps`;
CREATE TABLE `worker_epps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `item_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_number` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_on` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ASSIGNED',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_worker_epps_worker` (`worker_id`,`assigned_on`),
  CONSTRAINT `fk_worker_epps_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_evaluations`;
CREATE TABLE `worker_evaluations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `evaluation_date` date NOT NULL,
  `competency` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `evaluator` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_worker_evaluations_worker` (`worker_id`,`evaluation_date`),
  CONSTRAINT `fk_worker_evaluations_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_family_members`;
CREATE TABLE `worker_family_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `full_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `relationship` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date DEFAULT NULL,
  `depends_on_income` tinyint(1) NOT NULL DEFAULT '0',
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_worker_family_members_worker` (`worker_id`),
  CONSTRAINT `fk_worker_family_members_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_history`;
CREATE TABLE `worker_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `event_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_worker_history_worker` (`worker_id`,`event_date`),
  CONSTRAINT `fk_worker_history_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_leave_requests`;
CREATE TABLE `worker_leave_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `leave_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_count` decimal(7,2) NOT NULL DEFAULT '0.00',
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_worker_leave_requests_worker` (`worker_id`,`start_date`),
  CONSTRAINT `fk_worker_leave_requests_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_profiles`;
CREATE TABLE `worker_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commune` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_number` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `contract_type` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `base_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CLP',
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_worker_profiles_worker` (`worker_id`),
  CONSTRAINT `fk_worker_profiles_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_system_access`;
CREATE TABLE `worker_system_access` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `access_level` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'READ',
  `last_login_at` datetime DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_worker_system_access_worker` (`worker_id`),
  KEY `fk_worker_system_access_user` (`user_id`),
  CONSTRAINT `fk_worker_system_access_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_worker_system_access_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `worker_trainings`;
CREATE TABLE `worker_trainings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` bigint unsigned NOT NULL,
  `course_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `certificate_number` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hours` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_worker_trainings_worker` (`worker_id`,`completion_date`),
  CONSTRAINT `fk_worker_trainings_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `workers`;
CREATE TABLE `workers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `full_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `worker_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TEMPORAL',
  `default_rate` decimal(15,2) NOT NULL DEFAULT '0.00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_workers_company_tax_id` (`company_id`,`tax_id`),
  CONSTRAINT `fk_workers_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `workers` WRITE;
INSERT INTO `workers` (`id`, `company_id`, `full_name`, `tax_id`, `worker_type`, `default_rate`, `active`, `created_at`, `updated_at`) VALUES ('29', '1', 'Ana Muñoz', '12.345.678-5', 'PERMANENTE', '42000.00', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `workers` (`id`, `company_id`, `full_name`, `tax_id`, `worker_type`, `default_rate`, `active`, `created_at`, `updated_at`) VALUES ('30', '1', 'Carlos González', '13.456.789-2', 'PERMANENTE', '40000.00', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `workers` (`id`, `company_id`, `full_name`, `tax_id`, `worker_type`, `default_rate`, `active`, `created_at`, `updated_at`) VALUES ('31', '1', 'María Soto', '14.567.890-1', 'TEMPORAL', '32000.00', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `workers` (`id`, `company_id`, `full_name`, `tax_id`, `worker_type`, `default_rate`, `active`, `created_at`, `updated_at`) VALUES ('32', '1', 'Jorge Pérez', '15.678.901-8', 'TEMPORAL', '33000.00', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `workers` (`id`, `company_id`, `full_name`, `tax_id`, `worker_type`, `default_rate`, `active`, `created_at`, `updated_at`) VALUES ('33', '1', 'Patricia Rojas', '16.789.012-6', 'TEMPORAL', '31000.00', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `workers` (`id`, `company_id`, `full_name`, `tax_id`, `worker_type`, `default_rate`, `active`, `created_at`, `updated_at`) VALUES ('34', '1', 'Rodrigo Silva', '17.890.123-4', 'PERMANENTE', '45000.00', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `workers` (`id`, `company_id`, `full_name`, `tax_id`, `worker_type`, `default_rate`, `active`, `created_at`, `updated_at`) VALUES ('35', '1', 'Fernando Álvarez', '18.901.234-3', 'PERMANENTE', '38000.00', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
INSERT INTO `workers` (`id`, `company_id`, `full_name`, `tax_id`, `worker_type`, `default_rate`, `active`, `created_at`, `updated_at`) VALUES ('36', '1', 'Sofía Morales', '19.012.345-0', 'TEMPORAL', '30000.00', '1', '2026-08-03 16:52:22', '2026-08-03 16:52:22');
UNLOCK TABLES;

SET FOREIGN_KEY_CHECKS=1;
