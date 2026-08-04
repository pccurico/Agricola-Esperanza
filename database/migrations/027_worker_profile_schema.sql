CREATE TABLE IF NOT EXISTS worker_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    birth_date DATE NULL,
    gender VARCHAR(20) NULL,
    marital_status VARCHAR(30) NULL,
    nationality VARCHAR(80) NULL,
    address VARCHAR(255) NULL,
    commune VARCHAR(100) NULL,
    region VARCHAR(100) NULL,
    email VARCHAR(160) NULL,
    phone VARCHAR(40) NULL,
    emergency_contact_name VARCHAR(160) NULL,
    emergency_contact_phone VARCHAR(40) NULL,
    employee_number VARCHAR(40) NULL,
    department VARCHAR(80) NULL,
    position VARCHAR(120) NULL,
    hire_date DATE NULL,
    contract_type VARCHAR(40) NULL,
    base_salary DECIMAL(15,2) NOT NULL DEFAULT 0,
    currency VARCHAR(10) NOT NULL DEFAULT 'CLP',
    avatar_path VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_profiles_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    UNIQUE KEY uq_worker_profiles_worker (worker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_contracts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    contract_type VARCHAR(50) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'ACTIVE',
    start_date DATE NOT NULL,
    end_date DATE NULL,
    weekly_hours DECIMAL(6,2) NOT NULL DEFAULT 45,
    base_salary DECIMAL(15,2) NOT NULL DEFAULT 0,
    currency VARCHAR(10) NOT NULL DEFAULT 'CLP',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_contracts_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    UNIQUE KEY uq_worker_contracts_active (worker_id, status),
    KEY idx_worker_contracts_worker (worker_id, start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_benefits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    health_system VARCHAR(80) NULL,
    afp_name VARCHAR(80) NULL,
    pension_type VARCHAR(80) NULL,
    extra_benefit VARCHAR(160) NULL,
    health_plan VARCHAR(160) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_benefits_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    UNIQUE KEY uq_worker_benefits_worker (worker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_bank_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    bank_name VARCHAR(120) NULL,
    account_type VARCHAR(40) NULL,
    account_number VARCHAR(80) NULL,
    swift_code VARCHAR(30) NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_bank_accounts_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    UNIQUE KEY uq_worker_bank_primary (worker_id, is_primary),
    KEY idx_worker_bank_accounts_worker (worker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_family_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    relationship VARCHAR(40) NOT NULL,
    birth_date DATE NULL,
    depends_on_income TINYINT(1) NOT NULL DEFAULT 0,
    phone VARCHAR(40) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_family_members_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    KEY idx_worker_family_members_worker (worker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    document_type VARCHAR(80) NOT NULL,
    document_number VARCHAR(120) NULL,
    issue_date DATE NULL,
    expiry_date DATE NULL,
    file_path VARCHAR(255) NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'VALID',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_documents_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    KEY idx_worker_documents_worker (worker_id, document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_trainings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    course_name VARCHAR(180) NOT NULL,
    institution VARCHAR(160) NULL,
    completion_date DATE NULL,
    certificate_number VARCHAR(80) NULL,
    hours DECIMAL(8,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_trainings_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    KEY idx_worker_trainings_worker (worker_id, completion_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_epps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    item_name VARCHAR(160) NOT NULL,
    size VARCHAR(30) NULL,
    serial_number VARCHAR(80) NULL,
    assigned_on DATE NOT NULL,
    due_date DATE NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'ASSIGNED',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_epps_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    KEY idx_worker_epps_worker (worker_id, assigned_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    farm_id BIGINT UNSIGNED NULL,
    block_id BIGINT UNSIGNED NULL,
    department VARCHAR(80) NULL,
    position VARCHAR(120) NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_assignments_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    CONSTRAINT fk_worker_assignments_farm FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE SET NULL,
    CONSTRAINT fk_worker_assignments_block FOREIGN KEY (block_id) REFERENCES blocks(id) ON DELETE SET NULL,
    KEY idx_worker_assignments_worker (worker_id, start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_leave_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    leave_type VARCHAR(60) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days_count DECIMAL(7,2) NOT NULL DEFAULT 0,
    status VARCHAR(40) NOT NULL DEFAULT 'PENDING',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_leave_requests_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    KEY idx_worker_leave_requests_worker (worker_id, start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_evaluations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    evaluation_date DATE NOT NULL,
    competency VARCHAR(120) NOT NULL,
    score DECIMAL(5,2) NOT NULL DEFAULT 0,
    evaluator VARCHAR(160) NULL,
    comments TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_evaluations_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    KEY idx_worker_evaluations_worker (worker_id, evaluation_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(60) NOT NULL,
    event_date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_history_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    KEY idx_worker_history_worker (worker_id, event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS worker_system_access (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    access_level VARCHAR(60) NOT NULL DEFAULT 'READ',
    last_login_at DATETIME NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_worker_system_access_worker FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    CONSTRAINT fk_worker_system_access_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_worker_system_access_worker (worker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
