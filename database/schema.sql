-- ============================================================================
-- Mika - Schema de Base de Datos PostgreSQL
-- Ejecutar en: psql -h 100.115.234.125 -U mika -d mika -f schema.sql
-- ============================================================================

-- Tabla de migraciones de Laravel
CREATE TABLE IF NOT EXISTS migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

-- ============================================================================
-- USERS (usuarios con campos de negocio)
-- ============================================================================
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    business_name VARCHAR(255) NULL,
    business_type VARCHAR(255) NULL,
    currency VARCHAR(3) DEFAULT 'MXN',
    preferences JSONB NULL,
    categories JSONB NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ============================================================================
-- PASSWORD RESET TOKENS
-- ============================================================================
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

-- ============================================================================
-- SESSIONS
-- ============================================================================
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions(user_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON sessions(last_activity);

-- ============================================================================
-- CACHE
-- ============================================================================
CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

-- ============================================================================
-- JOBS (queue)
-- ============================================================================
CREATE TABLE IF NOT EXISTS jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS jobs_queue_index ON jobs(queue);

CREATE TABLE IF NOT EXISTS job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT NULL,
    cancelled_at INTEGER NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER NULL
);

CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- TRANSACTIONS (transacciones financieras)
-- ============================================================================
CREATE TABLE IF NOT EXISTS transactions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(15, 2) NOT NULL,
    type VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL,
    transaction_date DATE NOT NULL,
    description TEXT NULL,
    receipt_image_path VARCHAR(255) NULL,
    status VARCHAR(255) DEFAULT 'approved',
    meta_data JSONB NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);
CREATE INDEX IF NOT EXISTS transactions_user_date_index ON transactions(user_id, transaction_date);
CREATE INDEX IF NOT EXISTS transactions_user_type_category_index ON transactions(user_id, type, category);
CREATE INDEX IF NOT EXISTS transactions_user_status_index ON transactions(user_id, status);
CREATE INDEX IF NOT EXISTS transactions_user_type_date_index ON transactions(user_id, type, transaction_date);

-- ============================================================================
-- CONVERSATIONS (conversaciones del chat)
-- ============================================================================
CREATE TABLE IF NOT EXISTS conversations (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NULL,
    context JSONB NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_activity_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE INDEX IF NOT EXISTS conversations_user_active_index ON conversations(user_id, is_active);
CREATE INDEX IF NOT EXISTS conversations_user_activity_index ON conversations(user_id, last_activity_at);

-- ============================================================================
-- MESSAGES (mensajes del chat)
-- ============================================================================
CREATE TABLE IF NOT EXISTS messages (
    id BIGSERIAL PRIMARY KEY,
    conversation_id BIGINT NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    sender VARCHAR(255) NOT NULL,
    type VARCHAR(255) DEFAULT 'text',
    meta_data JSONB NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE INDEX IF NOT EXISTS messages_conversation_created_index ON messages(conversation_id, created_at);

-- ============================================================================
-- Insertar registros de migraciones
-- ============================================================================
INSERT INTO migrations (migration, batch) VALUES
    ('0001_01_01_000000_create_users_table', 1),
    ('0001_01_01_000001_create_cache_table', 1),
    ('0001_01_01_000002_create_jobs_table', 1),
    ('2024_01_01_000003_create_transactions_table', 1),
    ('2024_01_01_000004_create_conversations_table', 1),
    ('2024_01_01_000005_create_messages_table', 1)
ON CONFLICT DO NOTHING;

-- ============================================================================
-- Verificar tablas creadas
-- ============================================================================
SELECT table_name FROM information_schema.tables
WHERE table_schema = 'public'
ORDER BY table_name;
