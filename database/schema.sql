-- ============================================================================
-- SHALOM DENTAL - SISTEMA DE GESTIÓN ODONTOLÓGICA
-- Script de Base de Datos - MySQL 8.0+ / MariaDB 10.5+
-- Versión: 1.0.0
-- Fecha: 2025-01-15
-- ============================================================================
-- 
-- INSTRUCCIONES DE USO:
-- 1. Crear la base de datos: mysql -u root -p < shalom_dental_database.sql
-- 2. O importar desde phpMyAdmin/HeidiSQL
-- 
-- ============================================================================

-- Configuración inicial
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ============================================================================
-- CREAR BASE DE DATOS
-- ============================================================================

DROP DATABASE IF EXISTS shalom_dental;
CREATE DATABASE shalom_dental 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE shalom_dental;


-- ============================================================================
-- SECCIÓN 1: NÚCLEO ORGANIZACIONAL
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: organizations
-- Descripción: Datos de la organización/clínica principal
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS organizations;
CREATE TABLE organizations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identificación
    ruc VARCHAR(13) NOT NULL COMMENT 'RUC de la organización',
    business_name VARCHAR(300) NOT NULL COMMENT 'Razón social',
    trade_name VARCHAR(300) NULL COMMENT 'Nombre comercial',
    
    -- Contacto
    address VARCHAR(500) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    website VARCHAR(255) NULL,
    
    -- Branding
    logo_path VARCHAR(500) NULL COMMENT 'Ruta al logo',
    
    -- Configuración general
    timezone VARCHAR(50) DEFAULT 'America/Guayaquil',
    date_format VARCHAR(20) DEFAULT 'd/m/Y',
    time_format VARCHAR(20) DEFAULT 'H:i',
    currency_code VARCHAR(3) DEFAULT 'USD',
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_organizations_ruc (ruc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Datos de la organización principal';


-- ----------------------------------------------------------------------------
-- Tabla: locations
-- Descripción: Sedes o sucursales de la organización
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS locations;
CREATE TABLE locations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    
    -- Identificación
    code VARCHAR(10) NOT NULL COMMENT 'Código interno de la sede',
    name VARCHAR(100) NOT NULL COMMENT 'Nombre de la sede',
    
    -- Datos fiscales SRI
    sri_establishment_code VARCHAR(3) NOT NULL COMMENT 'Código establecimiento SRI (001, 002...)',
    
    -- Ubicación
    address VARCHAR(500) NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    
    -- Contacto
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    
    -- Horario general
    opening_time TIME DEFAULT '08:00:00',
    closing_time TIME DEFAULT '18:00:00',
    
    -- Estado
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_locations_code (organization_id, code),
    UNIQUE INDEX idx_locations_sri (organization_id, sri_establishment_code),
    INDEX idx_locations_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Sedes o sucursales';


-- ----------------------------------------------------------------------------
-- Tabla: resource_types
-- Descripción: Tipos de recursos (sillón, sala RX, equipo láser, etc.)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS resource_types;
CREATE TABLE resource_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    code VARCHAR(20) NOT NULL COMMENT 'Código único del tipo',
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(50) NULL COMMENT 'Nombre del icono para UI',
    color_hex VARCHAR(7) DEFAULT '#6B7280',
    
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_resource_types_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de tipos de recursos';


-- ----------------------------------------------------------------------------
-- Tabla: resources
-- Descripción: Recursos físicos de cada sede
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS resources;
CREATE TABLE resources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    location_id INT UNSIGNED NOT NULL,
    resource_type_id INT UNSIGNED NOT NULL,
    
    code VARCHAR(20) NOT NULL COMMENT 'Código interno',
    name VARCHAR(100) NOT NULL COMMENT 'Nombre descriptivo',
    description TEXT NULL,
    
    -- Estado
    is_active BOOLEAN DEFAULT TRUE,
    is_available BOOLEAN DEFAULT TRUE COMMENT 'Disponible (false = mantenimiento)',
    unavailable_reason VARCHAR(255) NULL,
    unavailable_until DATE NULL,
    
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_resources_code (location_id, code),
    INDEX idx_resources_type (resource_type_id),
    INDEX idx_resources_available (is_active, is_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Recursos físicos por sede';


-- ----------------------------------------------------------------------------
-- Tabla: emission_points
-- Descripción: Puntos de emisión para facturación SRI
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS emission_points;
CREATE TABLE emission_points (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    location_id INT UNSIGNED NOT NULL,
    
    code VARCHAR(3) NOT NULL COMMENT 'Código punto emisión SRI (001, 002...)',
    description VARCHAR(100) NULL,
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_emission_points_code (location_id, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Puntos de emisión SRI';


-- ============================================================================
-- SECCIÓN 2: USUARIOS, ROLES Y PERMISOS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: roles
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    code VARCHAR(30) NOT NULL COMMENT 'Código único',
    name VARCHAR(100) NOT NULL COMMENT 'Nombre visible',
    description TEXT NULL,
    
    is_system BOOLEAN DEFAULT FALSE COMMENT 'Rol de sistema, no eliminable',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_roles_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Roles del sistema';


-- ----------------------------------------------------------------------------
-- Tabla: permissions
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS permissions;
CREATE TABLE permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    module VARCHAR(50) NOT NULL COMMENT 'Módulo (agenda, patients, billing)',
    resource VARCHAR(50) NOT NULL COMMENT 'Recurso (appointments, invoices)',
    action VARCHAR(50) NOT NULL COMMENT 'Acción (create, read, update, delete)',
    
    description VARCHAR(255) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_permissions_unique (module, resource, action),
    INDEX idx_permissions_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Permisos del sistema';


-- ----------------------------------------------------------------------------
-- Tabla: role_permissions
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS role_permissions;
CREATE TABLE role_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_role_permissions_unique (role_id, permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Asignación de permisos a roles';


-- ----------------------------------------------------------------------------
-- Tabla: users
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    
    -- Autenticación
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    
    -- Datos personales
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    avatar_path VARCHAR(500) NULL,
    
    -- Datos profesionales
    is_professional BOOLEAN DEFAULT FALSE COMMENT 'Es profesional de salud',
    professional_title VARCHAR(100) NULL COMMENT 'Dr., Dra., Lic.',
    professional_registration VARCHAR(50) NULL COMMENT 'Número registro profesional',
    specialty VARCHAR(100) NULL,
    signature_path VARCHAR(500) NULL COMMENT 'Firma digital',
    
    -- Estado
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    
    -- Control de acceso
    failed_login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,
    
    -- Preferencias
    preferences JSON NULL,
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_users_email (email),
    INDEX idx_users_role (role_id),
    INDEX idx_users_professional (is_professional),
    INDEX idx_users_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Usuarios del sistema';


-- ----------------------------------------------------------------------------
-- Tabla: location_users
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS location_users;
CREATE TABLE location_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    location_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    
    is_primary BOOLEAN DEFAULT FALSE COMMENT 'Sede principal',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_location_users_unique (location_id, user_id),
    INDEX idx_location_users_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Asignación de usuarios a sedes';


-- ----------------------------------------------------------------------------
-- Tabla: user_sessions
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS user_sessions;
CREATE TABLE user_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    location_id INT UNSIGNED NULL COMMENT 'Sede activa',
    
    session_id VARCHAR(128) NOT NULL,
    token_hash VARCHAR(255) NULL COMMENT 'Token remember me',
    
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500) NULL,
    
    last_activity_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_sessions_session_id (session_id),
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Sesiones activas';


-- ============================================================================
-- SECCIÓN 3: PACIENTES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: patients
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS patients;
CREATE TABLE patients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    
    -- Identificación
    id_type ENUM('cedula', 'ruc', 'pasaporte', 'otro') NOT NULL DEFAULT 'cedula',
    id_number VARCHAR(20) NOT NULL,
    
    -- Datos personales
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NOT NULL,
    phone_secondary VARCHAR(20) NULL,
    
    birth_date DATE NULL,
    gender ENUM('M', 'F', 'O') NULL COMMENT 'M=Masculino, F=Femenino, O=Otro',
    marital_status ENUM('soltero', 'casado', 'divorciado', 'viudo', 'union_libre') NULL,
    occupation VARCHAR(100) NULL,
    
    -- Dirección
    address VARCHAR(500) NULL,
    city VARCHAR(100) NULL,
    province VARCHAR(100) NULL,
    postal_code VARCHAR(10) NULL,
    
    -- Contacto emergencia
    emergency_contact_name VARCHAR(200) NULL,
    emergency_contact_phone VARCHAR(20) NULL,
    emergency_contact_relation VARCHAR(50) NULL,
    
    -- Info médica básica
    blood_type VARCHAR(5) NULL COMMENT 'A+, A-, B+, B-, AB+, AB-, O+, O-',
    allergies TEXT NULL,
    current_medications TEXT NULL,
    medical_conditions TEXT NULL,
    
    -- Notas
    notes TEXT NULL,
    internal_notes TEXT NULL COMMENT 'Solo staff',
    
    -- Preferencias comunicación
    preferred_contact_method ENUM('phone', 'email', 'sms', 'whatsapp') DEFAULT 'whatsapp',
    accepts_marketing BOOLEAN DEFAULT FALSE,
    
    -- Historial asistencia
    no_show_count INT DEFAULT 0,
    late_count INT DEFAULT 0,
    cancellation_count INT DEFAULT 0,
    
    -- Estado
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NULL,
    
    UNIQUE INDEX idx_patients_id_number (organization_id, id_type, id_number),
    INDEX idx_patients_name (organization_id, last_name, first_name),
    INDEX idx_patients_phone (phone),
    INDEX idx_patients_email (email),
    INDEX idx_patients_active (organization_id, is_active),
    FULLTEXT INDEX idx_patients_search (first_name, last_name, id_number, phone, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Datos de pacientes';


-- ----------------------------------------------------------------------------
-- Tabla: patient_files
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS patient_files;
CREATE TABLE patient_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    uploaded_by_user_id INT UNSIGNED NOT NULL,
    
    -- Datos archivo
    file_name VARCHAR(255) NOT NULL COMMENT 'Nombre en sistema',
    original_name VARCHAR(255) NOT NULL COMMENT 'Nombre original',
    file_path VARCHAR(500) NOT NULL,
    file_size INT UNSIGNED NOT NULL COMMENT 'Bytes',
    mime_type VARCHAR(100) NOT NULL,
    
    -- Categorización
    category ENUM('photo', 'xray', 'document', 'consent', 'lab_result', 'other') DEFAULT 'other',
    description VARCHAR(500) NULL,
    
    -- Metadatos
    appointment_id INT UNSIGNED NULL,
    
    -- Visibilidad
    is_public BOOLEAN DEFAULT FALSE COMMENT 'Visible para paciente',
    
    -- Estado
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,
    deleted_by_user_id INT UNSIGNED NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_patient_files_patient (patient_id),
    INDEX idx_patient_files_category (patient_id, category),
    INDEX idx_patient_files_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Archivos de pacientes';


-- ============================================================================
-- SECCIÓN 4: AGENDA Y CITAS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: appointment_types
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS appointment_types;
CREATE TABLE appointment_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    
    code VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    
    -- Duración
    default_duration_minutes INT NOT NULL DEFAULT 30,
    buffer_before_minutes INT DEFAULT 0,
    buffer_after_minutes INT DEFAULT 5,
    
    -- Visual
    color_hex VARCHAR(7) NOT NULL DEFAULT '#1E4D3A',
    icon VARCHAR(50) NULL,
    
    -- Recurrencia
    is_recurring BOOLEAN DEFAULT FALSE,
    default_recurring_interval_days INT NULL,
    
    -- Confirmación
    requires_confirmation BOOLEAN DEFAULT TRUE,
    confirmation_hours_before INT DEFAULT 48,
    reminder_hours_before INT DEFAULT 24,
    
    -- Precio
    price_default DECIMAL(10,2) NULL,
    
    -- Estado
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_appointment_types_code (organization_id, code),
    INDEX idx_appointment_types_active (organization_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tipos de cita';


-- ----------------------------------------------------------------------------
-- Tabla: appointment_type_resources
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS appointment_type_resources;
CREATE TABLE appointment_type_resources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_type_id INT UNSIGNED NOT NULL,
    resource_type_id INT UNSIGNED NOT NULL,
    
    is_required BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_atr_unique (appointment_type_id, resource_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Recursos por tipo de cita';


-- ----------------------------------------------------------------------------
-- Tabla: recurring_series
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS recurring_series;
CREATE TABLE recurring_series (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    patient_id INT UNSIGNED NOT NULL,
    appointment_type_id INT UNSIGNED NOT NULL,
    professional_id INT UNSIGNED NOT NULL,
    location_id INT UNSIGNED NOT NULL,
    
    -- Tratamiento
    treatment_type VARCHAR(100) NULL,
    treatment_description TEXT NULL,
    
    -- Recurrencia
    interval_days INT NOT NULL DEFAULT 28,
    preferred_day_of_week TINYINT NULL COMMENT '0=Dom, 6=Sáb',
    preferred_time TIME NULL,
    
    -- Control sesiones
    total_sessions INT NULL COMMENT 'NULL=indefinido',
    sessions_completed INT DEFAULT 0,
    sessions_scheduled INT DEFAULT 0,
    
    -- Fechas
    start_date DATE NOT NULL,
    estimated_end_date DATE NULL,
    actual_end_date DATE NULL,
    
    -- Estado
    status ENUM('active', 'paused', 'completed', 'cancelled') DEFAULT 'active',
    pause_reason VARCHAR(255) NULL,
    cancellation_reason VARCHAR(255) NULL,
    
    notes TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NULL,
    
    INDEX idx_recurring_series_patient (patient_id),
    INDEX idx_recurring_series_status (status),
    INDEX idx_recurring_series_professional (professional_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Series de citas recurrentes';


-- ----------------------------------------------------------------------------
-- Tabla: appointments
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS appointments;
CREATE TABLE appointments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    location_id INT UNSIGNED NOT NULL,
    
    -- Relaciones
    patient_id INT UNSIGNED NOT NULL,
    professional_id INT UNSIGNED NOT NULL,
    appointment_type_id INT UNSIGNED NOT NULL,
    
    -- Serie recurrente
    recurring_series_id INT UNSIGNED NULL,
    series_sequence_number INT NULL,
    
    -- Reprogramación
    rescheduled_from_id INT UNSIGNED NULL,
    rescheduled_to_id INT UNSIGNED NULL,
    
    -- Programación
    scheduled_date DATE NOT NULL,
    scheduled_start_time TIME NOT NULL,
    scheduled_end_time TIME NOT NULL,
    duration_minutes INT NOT NULL,
    
    -- Estado
    status ENUM(
        'scheduled',
        'confirmed',
        'checked_in',
        'in_progress',
        'completed',
        'cancelled',
        'no_show',
        'rescheduled',
        'late'
    ) DEFAULT 'scheduled',
    
    -- Confirmación
    confirmation_sent_at TIMESTAMP NULL,
    reminder_sent_at TIMESTAMP NULL,
    confirmed_at TIMESTAMP NULL,
    confirmed_via ENUM('email', 'sms', 'whatsapp', 'phone', 'in_person') NULL,
    
    -- Tiempos reales
    checked_in_at TIMESTAMP NULL,
    called_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    finished_at TIMESTAMP NULL,
    
    -- Emergencias
    is_emergency BOOLEAN DEFAULT FALSE,
    emergency_reason VARCHAR(500) NULL,
    
    -- Motivos cambio estado
    cancellation_reason VARCHAR(500) NULL,
    cancellation_source ENUM('patient', 'clinic', 'professional', 'system') NULL,
    no_show_reason VARCHAR(500) NULL,
    reschedule_reason VARCHAR(500) NULL,
    incomplete_reason VARCHAR(500) NULL,
    
    -- Notas
    notes TEXT NULL,
    internal_notes TEXT NULL,
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NOT NULL,
    cancelled_by_user_id INT UNSIGNED NULL,
    cancelled_at TIMESTAMP NULL,
    
    INDEX idx_appointments_date_location (scheduled_date, location_id),
    INDEX idx_appointments_professional_date (professional_id, scheduled_date),
    INDEX idx_appointments_patient (patient_id),
    INDEX idx_appointments_status (status),
    INDEX idx_appointments_series (recurring_series_id),
    INDEX idx_appointments_pending (scheduled_date, status, location_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Citas agendadas';


-- ----------------------------------------------------------------------------
-- Tabla: appointment_resources
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS appointment_resources;
CREATE TABLE appointment_resources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_appointment_resources_unique (appointment_id, resource_id),
    INDEX idx_appointment_resources_resource (resource_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Recursos asignados a citas';


-- ============================================================================
-- SECCIÓN 5: DISPONIBILIDAD Y HORARIOS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: professional_schedules
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS professional_schedules;
CREATE TABLE professional_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL COMMENT 'Profesional',
    location_id INT UNSIGNED NOT NULL,
    
    day_of_week TINYINT NOT NULL COMMENT '0=Dom, 6=Sáb',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    
    default_resource_id INT UNSIGNED NULL,
    
    valid_from DATE NULL,
    valid_until DATE NULL,
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_schedules_user_day (user_id, day_of_week, is_active),
    INDEX idx_schedules_location (location_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Horarios semanales profesionales';


-- ----------------------------------------------------------------------------
-- Tabla: schedule_exceptions
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS schedule_exceptions;
CREATE TABLE schedule_exceptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    location_id INT UNSIGNED NULL,
    
    exception_date DATE NOT NULL,
    exception_type ENUM('block', 'extend', 'modify') NOT NULL,
    
    start_time TIME NULL,
    end_time TIME NULL,
    is_all_day BOOLEAN DEFAULT FALSE,
    
    reason VARCHAR(255) NULL,
    resource_id INT UNSIGNED NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NOT NULL,
    
    INDEX idx_exceptions_user_date (user_id, exception_date),
    INDEX idx_exceptions_date (exception_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Excepciones de horario';


-- ----------------------------------------------------------------------------
-- Tabla: resource_schedules
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS resource_schedules;
CREATE TABLE resource_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resource_id INT UNSIGNED NOT NULL,
    
    day_of_week TINYINT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_resource_schedules (resource_id, day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Horarios de recursos';


-- ----------------------------------------------------------------------------
-- Tabla: holidays
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS holidays;
CREATE TABLE holidays (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    location_id INT UNSIGNED NULL COMMENT 'NULL = todas las sedes',
    
    holiday_date DATE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    
    is_recurring BOOLEAN DEFAULT FALSE COMMENT 'Repite cada año',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NULL,
    
    UNIQUE INDEX idx_holidays_unique (organization_id, location_id, holiday_date),
    INDEX idx_holidays_date (holiday_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Feriados';


-- ============================================================================
-- SECCIÓN 6: LISTA DE ESPERA
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: waiting_list
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS waiting_list;
CREATE TABLE waiting_list (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    location_id INT UNSIGNED NOT NULL,
    patient_id INT UNSIGNED NOT NULL,
    
    -- Preferencias
    appointment_type_id INT UNSIGNED NULL,
    preferred_professional_id INT UNSIGNED NULL,
    
    -- Fechas
    date_from DATE NOT NULL,
    date_until DATE NULL,
    
    -- Horarios preferidos
    preferred_days_of_week JSON NULL COMMENT '[1,3,5] = Lun,Mié,Vie',
    preferred_time_slot ENUM('morning', 'afternoon', 'any') DEFAULT 'any',
    preferred_time_from TIME NULL,
    preferred_time_until TIME NULL,
    
    -- Prioridad
    priority ENUM('normal', 'high', 'urgent') DEFAULT 'normal',
    
    -- Seguimiento
    status ENUM('waiting', 'contacted', 'scheduled', 'expired', 'cancelled') DEFAULT 'waiting',
    contacted_at TIMESTAMP NULL,
    contacted_via ENUM('email', 'sms', 'whatsapp', 'phone') NULL,
    response_deadline TIMESTAMP NULL,
    
    scheduled_appointment_id INT UNSIGNED NULL,
    
    notes TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NOT NULL,
    
    INDEX idx_waiting_list_location (location_id, status),
    INDEX idx_waiting_list_priority (priority, created_at),
    INDEX idx_waiting_list_patient (patient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Lista de espera';


-- ============================================================================
-- SECCIÓN 7: NOTIFICACIONES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: notification_templates
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS notification_templates;
CREATE TABLE notification_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    
    code VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    
    channel ENUM('email', 'sms', 'whatsapp') NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    
    subject_template VARCHAR(500) NULL,
    body_template TEXT NOT NULL,
    
    available_variables JSON NULL,
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_templates_code (organization_id, code),
    INDEX idx_templates_event (organization_id, event_type, channel, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Plantillas de notificación';


-- ----------------------------------------------------------------------------
-- Tabla: notification_configs
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS notification_configs;
CREATE TABLE notification_configs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    
    channel ENUM('email', 'sms', 'whatsapp') NOT NULL,
    provider VARCHAR(50) NOT NULL COMMENT 'smtp, twilio, ultramsg',
    
    config_encrypted TEXT NOT NULL,
    
    from_address VARCHAR(255) NULL,
    from_name VARCHAR(100) NULL,
    
    is_active BOOLEAN DEFAULT TRUE,
    last_test_at TIMESTAMP NULL,
    last_test_result BOOLEAN NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_notification_configs (organization_id, channel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Configuración canales notificación';


-- ----------------------------------------------------------------------------
-- Tabla: notifications
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    
    template_id INT UNSIGNED NULL,
    patient_id INT UNSIGNED NULL,
    appointment_id INT UNSIGNED NULL,
    invoice_id INT UNSIGNED NULL,
    
    channel ENUM('email', 'sms', 'whatsapp') NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    
    subject VARCHAR(500) NULL,
    body TEXT NOT NULL,
    
    variables_used JSON NULL,
    
    status ENUM('pending', 'processing', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
    
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    
    retry_count INT DEFAULT 0,
    max_retries INT DEFAULT 3,
    next_retry_at TIMESTAMP NULL,
    
    error_message TEXT NULL,
    provider_message_id VARCHAR(255) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_notifications_pending (status, next_retry_at),
    INDEX idx_notifications_patient (patient_id),
    INDEX idx_notifications_appointment (appointment_id),
    INDEX idx_notifications_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cola de notificaciones';


-- ============================================================================
-- SECCIÓN 8: FACTURACIÓN SRI ECUADOR
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: sri_configurations
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS sri_configurations;
CREATE TABLE sri_configurations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    
    environment ENUM('1', '2') NOT NULL DEFAULT '1' COMMENT '1=Pruebas, 2=Producción',
    
    -- Certificado
    certificate_path VARCHAR(500) NULL,
    certificate_password_encrypted VARCHAR(500) NULL,
    certificate_subject VARCHAR(500) NULL,
    certificate_issuer VARCHAR(500) NULL,
    certificate_valid_from DATE NULL,
    certificate_valid_until DATE NULL,
    
    -- Contribuyente
    forced_accounting BOOLEAN DEFAULT FALSE,
    special_taxpayer_code VARCHAR(20) NULL,
    withholding_agent_code VARCHAR(20) NULL,
    rimpe_taxpayer BOOLEAN DEFAULT FALSE,
    
    -- Contingencia
    contingency_mode BOOLEAN DEFAULT FALSE,
    contingency_reason VARCHAR(255) NULL,
    contingency_started_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_sri_config_org (organization_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Configuración SRI';


-- ----------------------------------------------------------------------------
-- Tabla: invoice_sequentials
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS invoice_sequentials;
CREATE TABLE invoice_sequentials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    emission_point_id INT UNSIGNED NOT NULL,
    
    document_type ENUM('01', '04', '05', '06', '07') NOT NULL 
        COMMENT '01=Fact, 04=NC, 05=ND, 06=Guía, 07=Ret',
    
    current_sequential INT UNSIGNED NOT NULL DEFAULT 0,
    last_used_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE INDEX idx_sequentials_unique (emission_point_id, document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Secuenciales SRI';


-- ----------------------------------------------------------------------------
-- Tabla: invoices
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS invoices;
CREATE TABLE invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    location_id INT UNSIGNED NOT NULL,
    emission_point_id INT UNSIGNED NOT NULL,
    
    patient_id INT UNSIGNED NULL,
    appointment_id INT UNSIGNED NULL,
    
    -- Identificación documento
    document_type ENUM('01', '04') NOT NULL DEFAULT '01' COMMENT '01=Factura, 04=NC',
    establishment_code VARCHAR(3) NOT NULL,
    emission_point_code VARCHAR(3) NOT NULL,
    sequential INT UNSIGNED NOT NULL,
    access_key VARCHAR(49) NULL,
    
    -- Fechas
    issue_date DATE NOT NULL,
    issue_time TIME NOT NULL,
    
    -- Comprador
    buyer_id_type ENUM('04', '05', '06', '07', '08') NOT NULL,
    buyer_id_number VARCHAR(20) NOT NULL,
    buyer_name VARCHAR(300) NOT NULL,
    buyer_address VARCHAR(500) NULL,
    buyer_email VARCHAR(255) NULL,
    buyer_phone VARCHAR(20) NULL,
    
    -- Totales
    subtotal_no_tax DECIMAL(14,2) DEFAULT 0,
    subtotal_0 DECIMAL(14,2) DEFAULT 0,
    subtotal_12 DECIMAL(14,2) DEFAULT 0,
    subtotal_15 DECIMAL(14,2) DEFAULT 0,
    subtotal_not_subject DECIMAL(14,2) DEFAULT 0,
    subtotal_exempt DECIMAL(14,2) DEFAULT 0,
    
    total_discount DECIMAL(14,2) DEFAULT 0,
    subtotal DECIMAL(14,2) NOT NULL,
    total_tax DECIMAL(14,2) DEFAULT 0,
    tip DECIMAL(14,2) DEFAULT 0,
    total DECIMAL(14,2) NOT NULL,
    
    -- Estado SRI
    status ENUM(
        'draft',
        'pending',
        'sent',
        'authorized',
        'rejected',
        'voided',
        'contingency'
    ) DEFAULT 'draft',
    
    -- Respuesta SRI
    sri_received_at TIMESTAMP NULL,
    sri_authorization_number VARCHAR(49) NULL,
    sri_authorization_date TIMESTAMP NULL,
    sri_receipt_number VARCHAR(50) NULL,
    sri_error_messages JSON NULL,
    
    -- Archivos
    xml_content MEDIUMTEXT NULL,
    xml_path VARCHAR(500) NULL,
    ride_path VARCHAR(500) NULL,
    
    -- NC referencia
    modified_document_id INT UNSIGNED NULL,
    modification_reason VARCHAR(500) NULL,
    
    -- Anulación
    voided_at TIMESTAMP NULL,
    voided_by_user_id INT UNSIGNED NULL,
    credit_note_id INT UNSIGNED NULL,
    
    additional_info JSON NULL,
    internal_notes TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NOT NULL,
    
    UNIQUE INDEX idx_invoices_access_key (access_key),
    UNIQUE INDEX idx_invoices_sequential (establishment_code, emission_point_code, sequential, document_type),
    INDEX idx_invoices_patient (patient_id),
    INDEX idx_invoices_status (status),
    INDEX idx_invoices_date (issue_date),
    INDEX idx_invoices_pending (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Facturas electrónicas';


-- ----------------------------------------------------------------------------
-- Tabla: invoice_items
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS invoice_items;
CREATE TABLE invoice_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT UNSIGNED NOT NULL,
    
    sequence INT NOT NULL,
    
    main_code VARCHAR(25) NOT NULL,
    aux_code VARCHAR(25) NULL,
    
    description VARCHAR(300) NOT NULL,
    
    quantity DECIMAL(14,6) NOT NULL,
    unit_price DECIMAL(14,6) NOT NULL,
    discount_amount DECIMAL(14,2) DEFAULT 0,
    subtotal DECIMAL(14,2) NOT NULL,
    
    tax_code ENUM('0', '2', '3', '4', '5', '6', '7', '8') NOT NULL DEFAULT '4',
    tax_percentage DECIMAL(5,2) NOT NULL DEFAULT 15.00,
    tax_rate_code VARCHAR(10) DEFAULT '4',
    tax_amount DECIMAL(14,2) NOT NULL,
    
    total DECIMAL(14,2) NOT NULL,
    
    appointment_type_id INT UNSIGNED NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_invoice_items_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Detalle facturas';


-- ----------------------------------------------------------------------------
-- Tabla: invoice_payments
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS invoice_payments;
CREATE TABLE invoice_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT UNSIGNED NOT NULL,
    
    payment_method_code VARCHAR(2) NOT NULL,
    payment_method_name VARCHAR(100) NULL,
    
    amount DECIMAL(14,2) NOT NULL,
    term_days INT DEFAULT 0,
    time_unit VARCHAR(20) DEFAULT 'dias',
    
    card_brand VARCHAR(50) NULL,
    card_last_four VARCHAR(4) NULL,
    authorization_code VARCHAR(50) NULL,
    reference_number VARCHAR(100) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_invoice_payments_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Pagos de facturas';


-- ============================================================================
-- SECCIÓN 9: AUDITORÍA
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: audit_logs
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS audit_logs;
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NULL,
    
    user_id INT UNSIGNED NULL,
    user_email VARCHAR(255) NULL,
    user_name VARCHAR(200) NULL,
    
    location_id INT UNSIGNED NULL,
    session_id VARCHAR(128) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    
    action VARCHAR(50) NOT NULL,
    
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT UNSIGNED NULL,
    
    old_values JSON NULL,
    new_values JSON NULL,
    
    additional_data JSON NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_audit_entity (entity_type, entity_id),
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_created (created_at),
    INDEX idx_audit_organization (organization_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de auditoría';


-- ============================================================================
-- SECCIÓN 10: CONFIGURACIÓN DEL SISTEMA
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: system_settings
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS system_settings;
CREATE TABLE system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'integer', 'float', 'boolean', 'json') DEFAULT 'string',
    
    category VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    
    is_public BOOLEAN DEFAULT FALSE,
    
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by_user_id INT UNSIGNED NULL,
    
    UNIQUE INDEX idx_settings_key (organization_id, setting_key),
    INDEX idx_settings_category (organization_id, category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Configuraciones del sistema';


-- ============================================================================
-- SECCIÓN 11: CONTINGENCIAS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: contingencies
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS contingencies;
CREATE TABLE contingencies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    location_id INT UNSIGNED NULL,
    
    contingency_type ENUM(
        'professional_absence',
        'resource_unavailable',
        'location_closed',
        'system_failure',
        'other'
    ) NOT NULL,
    
    affected_professional_id INT UNSIGNED NULL,
    affected_resource_id INT UNSIGNED NULL,
    
    start_datetime TIMESTAMP NOT NULL,
    end_datetime TIMESTAMP NULL,
    is_all_day BOOLEAN DEFAULT FALSE,
    
    reason VARCHAR(500) NOT NULL,
    description TEXT NULL,
    
    appointments_affected_count INT DEFAULT 0,
    appointments_rescheduled_count INT DEFAULT 0,
    appointments_cancelled_count INT DEFAULT 0,
    
    status ENUM('active', 'resolved', 'cancelled') DEFAULT 'active',
    resolved_at TIMESTAMP NULL,
    resolution_notes TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NOT NULL,
    resolved_by_user_id INT UNSIGNED NULL,
    
    INDEX idx_contingencies_status (status, start_datetime),
    INDEX idx_contingencies_professional (affected_professional_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de contingencias';


-- ----------------------------------------------------------------------------
-- Tabla: mass_reschedule_logs
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS mass_reschedule_logs;
CREATE TABLE mass_reschedule_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contingency_id INT UNSIGNED NULL,
    organization_id INT UNSIGNED NOT NULL,
    
    reason VARCHAR(500) NOT NULL,
    appointments_processed INT DEFAULT 0,
    appointments_rescheduled INT DEFAULT 0,
    appointments_cancelled INT DEFAULT 0,
    appointments_failed INT DEFAULT 0,
    
    affected_appointment_ids JSON NULL,
    
    notifications_sent INT DEFAULT 0,
    notification_template_used VARCHAR(50) NULL,
    
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    executed_by_user_id INT UNSIGNED NOT NULL,
    
    INDEX idx_mass_reschedule_date (executed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log reprogramaciones masivas';


-- ============================================================================
-- SECCIÓN 12: FOREIGN KEYS
-- ============================================================================

-- Locations
ALTER TABLE locations 
    ADD CONSTRAINT fk_locations_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Resources
ALTER TABLE resources 
    ADD CONSTRAINT fk_resources_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_resources_type 
    FOREIGN KEY (resource_type_id) REFERENCES resource_types(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- Emission Points
ALTER TABLE emission_points 
    ADD CONSTRAINT fk_emission_points_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Role Permissions
ALTER TABLE role_permissions 
    ADD CONSTRAINT fk_role_permissions_role 
    FOREIGN KEY (role_id) REFERENCES roles(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_role_permissions_permission 
    FOREIGN KEY (permission_id) REFERENCES permissions(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Users
ALTER TABLE users 
    ADD CONSTRAINT fk_users_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_users_role 
    FOREIGN KEY (role_id) REFERENCES roles(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- Location Users
ALTER TABLE location_users 
    ADD CONSTRAINT fk_location_users_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_location_users_user 
    FOREIGN KEY (user_id) REFERENCES users(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- User Sessions
ALTER TABLE user_sessions 
    ADD CONSTRAINT fk_sessions_user 
    FOREIGN KEY (user_id) REFERENCES users(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_sessions_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Patients
ALTER TABLE patients 
    ADD CONSTRAINT fk_patients_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_patients_created_by 
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Patient Files
ALTER TABLE patient_files 
    ADD CONSTRAINT fk_patient_files_patient 
    FOREIGN KEY (patient_id) REFERENCES patients(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_patient_files_uploaded_by 
    FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_patient_files_deleted_by 
    FOREIGN KEY (deleted_by_user_id) REFERENCES users(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Appointment Types
ALTER TABLE appointment_types 
    ADD CONSTRAINT fk_appointment_types_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Appointment Type Resources
ALTER TABLE appointment_type_resources 
    ADD CONSTRAINT fk_atr_type 
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_atr_resource_type 
    FOREIGN KEY (resource_type_id) REFERENCES resource_types(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Recurring Series
ALTER TABLE recurring_series 
    ADD CONSTRAINT fk_series_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_series_patient 
    FOREIGN KEY (patient_id) REFERENCES patients(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_series_type 
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_series_professional 
    FOREIGN KEY (professional_id) REFERENCES users(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_series_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_series_created_by 
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Appointments
ALTER TABLE appointments 
    ADD CONSTRAINT fk_appointments_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_appointments_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_appointments_patient 
    FOREIGN KEY (patient_id) REFERENCES patients(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_appointments_professional 
    FOREIGN KEY (professional_id) REFERENCES users(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_appointments_type 
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_appointments_series 
    FOREIGN KEY (recurring_series_id) REFERENCES recurring_series(id) 
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_appointments_created_by 
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_appointments_cancelled_by 
    FOREIGN KEY (cancelled_by_user_id) REFERENCES users(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Appointment Resources
ALTER TABLE appointment_resources 
    ADD CONSTRAINT fk_appt_resources_appointment 
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_appt_resources_resource 
    FOREIGN KEY (resource_id) REFERENCES resources(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Patient Files -> Appointments
ALTER TABLE patient_files 
    ADD CONSTRAINT fk_patient_files_appointment 
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Professional Schedules
ALTER TABLE professional_schedules 
    ADD CONSTRAINT fk_schedules_user 
    FOREIGN KEY (user_id) REFERENCES users(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_schedules_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_schedules_resource 
    FOREIGN KEY (default_resource_id) REFERENCES resources(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Schedule Exceptions
ALTER TABLE schedule_exceptions 
    ADD CONSTRAINT fk_exceptions_user 
    FOREIGN KEY (user_id) REFERENCES users(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_exceptions_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_exceptions_resource 
    FOREIGN KEY (resource_id) REFERENCES resources(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_exceptions_created_by 
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- Resource Schedules
ALTER TABLE resource_schedules 
    ADD CONSTRAINT fk_resource_schedules_resource 
    FOREIGN KEY (resource_id) REFERENCES resources(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Holidays
ALTER TABLE holidays 
    ADD CONSTRAINT fk_holidays_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_holidays_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_holidays_created_by 
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Waiting List
ALTER TABLE waiting_list 
    ADD CONSTRAINT fk_waitlist_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_waitlist_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_waitlist_patient 
    FOREIGN KEY (patient_id) REFERENCES patients(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_waitlist_type 
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id) 
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_waitlist_professional 
    FOREIGN KEY (preferred_professional_id) REFERENCES users(id) 
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_waitlist_appointment 
    FOREIGN KEY (scheduled_appointment_id) REFERENCES appointments(id) 
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_waitlist_created_by 
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- Notification Templates
ALTER TABLE notification_templates 
    ADD CONSTRAINT fk_templates_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Notification Configs
ALTER TABLE notification_configs 
    ADD CONSTRAINT fk_notif_configs_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Notifications
ALTER TABLE notifications 
    ADD CONSTRAINT fk_notifications_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_notifications_template 
    FOREIGN KEY (template_id) REFERENCES notification_templates(id) 
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_notifications_patient 
    FOREIGN KEY (patient_id) REFERENCES patients(id) 
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_notifications_appointment 
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- SRI Configuration
ALTER TABLE sri_configurations 
    ADD CONSTRAINT fk_sri_config_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Invoice Sequentials
ALTER TABLE invoice_sequentials 
    ADD CONSTRAINT fk_sequentials_emission_point 
    FOREIGN KEY (emission_point_id) REFERENCES emission_points(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Invoices
ALTER TABLE invoices 
    ADD CONSTRAINT fk_invoices_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_invoices_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_invoices_emission_point 
    FOREIGN KEY (emission_point_id) REFERENCES emission_points(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_invoices_patient 
    FOREIGN KEY (patient_id) REFERENCES patients(id) 
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_invoices_appointment 
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) 
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_invoices_created_by 
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_invoices_voided_by 
    FOREIGN KEY (voided_by_user_id) REFERENCES users(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Notifications -> Invoices
ALTER TABLE notifications 
    ADD CONSTRAINT fk_notifications_invoice 
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Invoice Items
ALTER TABLE invoice_items 
    ADD CONSTRAINT fk_items_invoice 
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_items_type 
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Invoice Payments
ALTER TABLE invoice_payments 
    ADD CONSTRAINT fk_payments_invoice 
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- System Settings
ALTER TABLE system_settings 
    ADD CONSTRAINT fk_settings_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_settings_updated_by 
    FOREIGN KEY (updated_by_user_id) REFERENCES users(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Contingencies
ALTER TABLE contingencies 
    ADD CONSTRAINT fk_contingencies_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_contingencies_location 
    FOREIGN KEY (location_id) REFERENCES locations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_contingencies_professional 
    FOREIGN KEY (affected_professional_id) REFERENCES users(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_contingencies_resource 
    FOREIGN KEY (affected_resource_id) REFERENCES resources(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_contingencies_created_by 
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_contingencies_resolved_by 
    FOREIGN KEY (resolved_by_user_id) REFERENCES users(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Mass Reschedule Logs
ALTER TABLE mass_reschedule_logs 
    ADD CONSTRAINT fk_reschedule_contingency 
    FOREIGN KEY (contingency_id) REFERENCES contingencies(id) 
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_reschedule_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_reschedule_executed_by 
    FOREIGN KEY (executed_by_user_id) REFERENCES users(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE;


-- ============================================================================
-- SECCIÓN 13: DATOS INICIALES (SEEDS)
-- ============================================================================

-- Roles del sistema
INSERT INTO roles (code, name, description, is_system) VALUES
('super_admin', 'Super Administrador', 'Acceso total al sistema', TRUE),
('admin', 'Administrador', 'Administrador de clínica', TRUE),
('odontologo', 'Odontólogo', 'Profesional de salud dental', TRUE),
('recepcion', 'Recepción', 'Personal de recepción', TRUE),
('asistente', 'Asistente Dental', 'Asistente de odontología', TRUE);

-- Tipos de recursos
INSERT INTO resource_types (code, name, description, icon, color_hex, sort_order) VALUES
('SILLON', 'Sillón Dental', 'Unidad dental completa', 'chair', '#1E4D3A', 1),
('SALA_RX', 'Sala de Rayos X', 'Sala para radiografías', 'radiation', '#6B7280', 2),
('EQUIPO_LASER', 'Equipo Láser', 'Equipo de láser dental', 'zap', '#F59E0B', 3),
('SALA_CIRUGIA', 'Sala de Cirugía', 'Sala quirúrgica', 'scissors', '#DC2626', 4),
('SALA_ESPERA', 'Sala de Espera', 'Área de espera', 'users', '#A3B7A5', 5);

-- Permisos del sistema
INSERT INTO permissions (module, resource, action, description) VALUES
-- Config
('config', 'organization', 'view', 'Ver organización'),
('config', 'organization', 'edit', 'Editar organización'),
('config', 'locations', 'view', 'Ver sedes'),
('config', 'locations', 'create', 'Crear sedes'),
('config', 'locations', 'edit', 'Editar sedes'),
('config', 'locations', 'delete', 'Eliminar sedes'),
('config', 'resources', 'view', 'Ver recursos'),
('config', 'resources', 'create', 'Crear recursos'),
('config', 'resources', 'edit', 'Editar recursos'),
('config', 'resources', 'delete', 'Eliminar recursos'),
('config', 'users', 'view', 'Ver usuarios'),
('config', 'users', 'create', 'Crear usuarios'),
('config', 'users', 'edit', 'Editar usuarios'),
('config', 'users', 'delete', 'Desactivar usuarios'),
('config', 'users', 'assign_roles', 'Asignar roles'),
('config', 'appointment_types', 'view', 'Ver tipos cita'),
('config', 'appointment_types', 'manage', 'Gestionar tipos cita'),
('config', 'schedules', 'view', 'Ver horarios'),
('config', 'schedules', 'manage', 'Gestionar horarios'),
('config', 'holidays', 'manage', 'Gestionar feriados'),
('config', 'sri', 'view', 'Ver config SRI'),
('config', 'sri', 'manage', 'Gestionar SRI'),

-- Patients
('patients', 'patients', 'view', 'Ver pacientes'),
('patients', 'patients', 'create', 'Crear pacientes'),
('patients', 'patients', 'edit', 'Editar pacientes'),
('patients', 'patients', 'delete', 'Desactivar pacientes'),
('patients', 'patients', 'view_history', 'Ver historial'),
('patients', 'files', 'view', 'Ver archivos'),
('patients', 'files', 'upload', 'Subir archivos'),
('patients', 'files', 'delete_own', 'Eliminar propios'),
('patients', 'files', 'delete_all', 'Eliminar todos'),

-- Agenda
('agenda', 'appointments', 'view_own', 'Ver citas propias'),
('agenda', 'appointments', 'view_all', 'Ver todas las citas'),
('agenda', 'appointments', 'create', 'Crear citas'),
('agenda', 'appointments', 'edit_own', 'Editar propias'),
('agenda', 'appointments', 'edit_all', 'Editar todas'),
('agenda', 'appointments', 'cancel_own', 'Cancelar propias'),
('agenda', 'appointments', 'cancel_all', 'Cancelar todas'),
('agenda', 'appointments', 'checkin', 'Registrar check-in'),
('agenda', 'appointments', 'start', 'Iniciar atención'),
('agenda', 'appointments', 'finish', 'Finalizar atención'),
('agenda', 'appointments', 'no_show', 'Marcar no-show'),
('agenda', 'availability', 'view', 'Ver disponibilidad'),
('agenda', 'availability', 'manage_own', 'Gestionar propia'),
('agenda', 'availability', 'manage_all', 'Gestionar todas'),
('agenda', 'waiting_list', 'view', 'Ver lista espera'),
('agenda', 'waiting_list', 'manage', 'Gestionar lista'),
('agenda', 'recurring', 'view', 'Ver series'),
('agenda', 'recurring', 'manage', 'Gestionar series'),
('agenda', 'contingencies', 'manage', 'Gestionar contingencias'),

-- Billing
('billing', 'invoices', 'view_own', 'Ver facturas propias'),
('billing', 'invoices', 'view_all', 'Ver todas facturas'),
('billing', 'invoices', 'create', 'Crear facturas'),
('billing', 'invoices', 'void', 'Anular facturas'),
('billing', 'credit_notes', 'create', 'Crear NC'),
('billing', 'sri_monitor', 'view', 'Ver monitor SRI'),
('billing', 'sri_monitor', 'retry', 'Reintentar SRI'),
('billing', 'reports', 'view', 'Ver reportes'),

-- Notifications
('notifications', 'logs', 'view', 'Ver logs'),
('notifications', 'logs', 'retry', 'Reenviar'),
('notifications', 'templates', 'view', 'Ver plantillas'),
('notifications', 'templates', 'manage', 'Gestionar plantillas'),
('notifications', 'config', 'manage', 'Config canales'),

-- Reports
('reports', 'dashboard', 'view_own', 'Dashboard propio'),
('reports', 'dashboard', 'view_all', 'Dashboard completo'),
('reports', 'productivity', 'view_own', 'Productividad propia'),
('reports', 'productivity', 'view_all', 'Productividad todos'),
('reports', 'financial', 'view', 'Reportes financieros'),
('reports', 'export', 'excel', 'Exportar Excel'),

-- Audit
('audit', 'logs', 'view', 'Ver auditoría'),
('audit', 'logs', 'export', 'Exportar auditoría');

-- Asignar todos los permisos a super_admin
INSERT INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE code = 'super_admin'),
    id
FROM permissions;

-- Asignar permisos a admin (todos excepto export audit)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE code = 'admin'),
    id
FROM permissions
WHERE NOT (module = 'audit' AND action = 'export');

-- Permisos para odontólogo
INSERT INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE code = 'odontologo'),
    p.id
FROM permissions p
WHERE 
    (p.module = 'patients' AND p.action IN ('view', 'create', 'edit', 'view_history'))
    OR (p.module = 'patients' AND p.resource = 'files' AND p.action IN ('view', 'upload', 'delete_own'))
    OR (p.module = 'agenda' AND p.action IN ('view_own', 'create', 'edit_own', 'cancel_own', 'checkin', 'start', 'finish', 'no_show'))
    OR (p.module = 'agenda' AND p.resource = 'availability' AND p.action IN ('view', 'manage_own'))
    OR (p.module = 'agenda' AND p.resource IN ('waiting_list', 'recurring') AND p.action = 'view')
    OR (p.module = 'billing' AND p.action IN ('view_own', 'create'))
    OR (p.module = 'reports' AND p.action LIKE '%_own')
    OR (p.module = 'config' AND p.resource IN ('locations', 'resources', 'appointment_types') AND p.action = 'view');

-- Permisos para recepción
INSERT INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE code = 'recepcion'),
    p.id
FROM permissions p
WHERE 
    (p.module = 'patients' AND p.action IN ('view', 'create', 'edit'))
    OR (p.module = 'patients' AND p.resource = 'files' AND p.action IN ('view', 'upload'))
    OR (p.module = 'agenda' AND p.action IN ('view_all', 'create', 'edit_all', 'cancel_all', 'checkin', 'no_show'))
    OR (p.module = 'agenda' AND p.resource = 'availability' AND p.action = 'view')
    OR (p.module = 'agenda' AND p.resource = 'waiting_list' AND p.action IN ('view', 'manage'))
    OR (p.module = 'billing' AND p.action IN ('view_all', 'create'))
    OR (p.module = 'notifications' AND p.resource = 'logs' AND p.action IN ('view', 'retry'))
    OR (p.module = 'reports' AND p.action = 'view_all')
    OR (p.module = 'config' AND p.resource IN ('locations', 'resources', 'appointment_types', 'schedules', 'holidays') AND p.action = 'view');

-- Permisos para asistente
INSERT INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE code = 'asistente'),
    p.id
FROM permissions p
WHERE 
    (p.module = 'patients' AND p.action = 'view')
    OR (p.module = 'patients' AND p.resource = 'files' AND p.action IN ('view', 'upload'))
    OR (p.module = 'agenda' AND p.action IN ('view_all', 'checkin'))
    OR (p.module = 'agenda' AND p.resource IN ('availability', 'waiting_list') AND p.action = 'view')
    OR (p.module = 'config' AND p.resource IN ('locations', 'resources') AND p.action = 'view');


-- ============================================================================
-- SECCIÓN 14: VISTAS ÚTILES
-- ============================================================================

-- Vista: Citas del día
CREATE OR REPLACE VIEW v_appointments_today AS
SELECT 
    a.id,
    a.scheduled_date,
    a.scheduled_start_time,
    a.scheduled_end_time,
    a.status,
    a.is_emergency,
    a.checked_in_at,
    a.started_at,
    a.finished_at,
    a.patient_id,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    p.phone AS patient_phone,
    a.professional_id,
    CONCAT(u.professional_title, ' ', u.first_name, ' ', u.last_name) AS professional_name,
    a.appointment_type_id,
    at.name AS appointment_type_name,
    at.color_hex AS appointment_color,
    a.location_id,
    l.name AS location_name,
    CASE 
        WHEN a.checked_in_at IS NOT NULL AND a.started_at IS NULL 
        THEN TIMESTAMPDIFF(MINUTE, a.checked_in_at, NOW())
        ELSE NULL 
    END AS wait_time_minutes
FROM appointments a
JOIN patients p ON a.patient_id = p.id
JOIN users u ON a.professional_id = u.id
JOIN appointment_types at ON a.appointment_type_id = at.id
JOIN locations l ON a.location_id = l.id
WHERE a.scheduled_date = CURDATE()
ORDER BY a.scheduled_start_time;

-- Vista: Disponibilidad semanal
CREATE OR REPLACE VIEW v_professional_availability AS
SELECT 
    ps.user_id,
    CONCAT(u.professional_title, ' ', u.first_name, ' ', u.last_name) AS professional_name,
    ps.location_id,
    l.name AS location_name,
    ps.day_of_week,
    CASE ps.day_of_week
        WHEN 0 THEN 'Domingo'
        WHEN 1 THEN 'Lunes'
        WHEN 2 THEN 'Martes'
        WHEN 3 THEN 'Miércoles'
        WHEN 4 THEN 'Jueves'
        WHEN 5 THEN 'Viernes'
        WHEN 6 THEN 'Sábado'
    END AS day_name,
    ps.start_time,
    ps.end_time,
    r.name AS resource_name
FROM professional_schedules ps
JOIN users u ON ps.user_id = u.id
JOIN locations l ON ps.location_id = l.id
LEFT JOIN resources r ON ps.default_resource_id = r.id
WHERE ps.is_active = TRUE AND u.is_active = TRUE AND u.is_professional = TRUE
ORDER BY ps.user_id, ps.day_of_week;

-- Vista: Resumen facturación mensual
CREATE OR REPLACE VIEW v_monthly_billing AS
SELECT 
    i.organization_id,
    i.location_id,
    l.name AS location_name,
    DATE_FORMAT(i.issue_date, '%Y-%m') AS month_year,
    COUNT(*) AS total_invoices,
    SUM(CASE WHEN i.status = 'authorized' THEN 1 ELSE 0 END) AS authorized_count,
    SUM(CASE WHEN i.status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
    SUM(CASE WHEN i.status = 'authorized' THEN i.subtotal ELSE 0 END) AS total_subtotal,
    SUM(CASE WHEN i.status = 'authorized' THEN i.total_tax ELSE 0 END) AS total_tax,
    SUM(CASE WHEN i.status = 'authorized' THEN i.total ELSE 0 END) AS total_amount
FROM invoices i
JOIN locations l ON i.location_id = l.id
WHERE i.document_type = '01'
GROUP BY i.organization_id, i.location_id, l.name, DATE_FORMAT(i.issue_date, '%Y-%m');


-- ============================================================================
-- SECCIÓN 15: PROCEDIMIENTOS ALMACENADOS
-- ============================================================================

DELIMITER //

-- Obtener siguiente secuencial
CREATE PROCEDURE sp_get_next_sequential(
    IN p_emission_point_id INT,
    IN p_document_type VARCHAR(2),
    OUT p_sequential INT
)
BEGIN
    DECLARE v_current INT;
    
    SELECT current_sequential INTO v_current
    FROM invoice_sequentials
    WHERE emission_point_id = p_emission_point_id
      AND document_type = p_document_type
    FOR UPDATE;
    
    IF v_current IS NULL THEN
        INSERT INTO invoice_sequentials (emission_point_id, document_type, current_sequential, last_used_at)
        VALUES (p_emission_point_id, p_document_type, 1, NOW());
        SET p_sequential = 1;
    ELSE
        SET p_sequential = v_current + 1;
        UPDATE invoice_sequentials
        SET current_sequential = p_sequential, last_used_at = NOW()
        WHERE emission_point_id = p_emission_point_id AND document_type = p_document_type;
    END IF;
END //

-- Verificar conflictos de cita
CREATE PROCEDURE sp_check_appointment_conflict(
    IN p_professional_id INT,
    IN p_scheduled_date DATE,
    IN p_start_time TIME,
    IN p_end_time TIME,
    IN p_exclude_id INT,
    OUT p_has_conflict BOOLEAN,
    OUT p_conflict_info VARCHAR(500)
)
BEGIN
    DECLARE v_conflict_id INT;
    DECLARE v_conflict_start TIME;
    DECLARE v_conflict_end TIME;
    
    SET p_has_conflict = FALSE;
    SET p_conflict_info = NULL;
    
    SELECT id, scheduled_start_time, scheduled_end_time
    INTO v_conflict_id, v_conflict_start, v_conflict_end
    FROM appointments
    WHERE professional_id = p_professional_id
      AND scheduled_date = p_scheduled_date
      AND status NOT IN ('cancelled', 'no_show', 'rescheduled')
      AND (p_exclude_id IS NULL OR id != p_exclude_id)
      AND (
          (p_start_time >= scheduled_start_time AND p_start_time < scheduled_end_time)
          OR (p_end_time > scheduled_start_time AND p_end_time <= scheduled_end_time)
          OR (p_start_time <= scheduled_start_time AND p_end_time >= scheduled_end_time)
      )
    LIMIT 1;
    
    IF v_conflict_id IS NOT NULL THEN
        SET p_has_conflict = TRUE;
        SET p_conflict_info = CONCAT('Conflicto con cita #', v_conflict_id, ' de ', 
            TIME_FORMAT(v_conflict_start, '%H:%i'), ' a ', TIME_FORMAT(v_conflict_end, '%H:%i'));
    END IF;
END //

-- Limpiar sesiones expiradas
CREATE PROCEDURE sp_cleanup_expired_sessions()
BEGIN
    DELETE FROM user_sessions WHERE expires_at < NOW();
END //

-- Actualizar contadores de paciente
CREATE PROCEDURE sp_update_patient_counters(IN p_patient_id INT)
BEGIN
    UPDATE patients p
    SET 
        no_show_count = (
            SELECT COUNT(*) FROM appointments 
            WHERE patient_id = p_patient_id AND status = 'no_show'
        ),
        cancellation_count = (
            SELECT COUNT(*) FROM appointments 
            WHERE patient_id = p_patient_id AND status = 'cancelled' 
            AND cancellation_source = 'patient'
        )
    WHERE p.id = p_patient_id;
END //

DELIMITER ;


-- ============================================================================
-- SECCIÓN 16: TRIGGERS
-- ============================================================================

DELIMITER //

-- Trigger: Actualizar contadores al cambiar estado de cita
CREATE TRIGGER trg_appointment_status_change
AFTER UPDATE ON appointments
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        IF NEW.status IN ('no_show', 'cancelled') THEN
            CALL sp_update_patient_counters(NEW.patient_id);
        END IF;
    END IF;
END //

-- Trigger: Log de auditoría para citas
CREATE TRIGGER trg_appointment_audit_insert
AFTER INSERT ON appointments
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (organization_id, user_id, action, entity_type, entity_id, new_values, created_at)
    VALUES (NEW.organization_id, NEW.created_by_user_id, 'create', 'appointment', NEW.id, 
        JSON_OBJECT(
            'patient_id', NEW.patient_id,
            'professional_id', NEW.professional_id,
            'scheduled_date', NEW.scheduled_date,
            'scheduled_start_time', NEW.scheduled_start_time,
            'status', NEW.status
        ), NOW());
END //

CREATE TRIGGER trg_appointment_audit_update
AFTER UPDATE ON appointments
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO audit_logs (organization_id, user_id, action, entity_type, entity_id, old_values, new_values, created_at)
        VALUES (NEW.organization_id, 
            COALESCE(NEW.cancelled_by_user_id, NEW.created_by_user_id), 
            'status_change', 'appointment', NEW.id,
            JSON_OBJECT('status', OLD.status),
            JSON_OBJECT('status', NEW.status, 'reason', 
                COALESCE(NEW.cancellation_reason, NEW.no_show_reason, NEW.reschedule_reason)),
            NOW());
    END IF;
END //

DELIMITER ;


-- ============================================================================
-- SECCIÓN 17: EVENTOS PROGRAMADOS
-- ============================================================================

-- Habilitar scheduler
SET GLOBAL event_scheduler = ON;

DELIMITER //

-- Evento: Limpiar sesiones expiradas cada hora
CREATE EVENT IF NOT EXISTS evt_cleanup_sessions
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    CALL sp_cleanup_expired_sessions();
END //

-- Evento: Expirar entradas de lista de espera antiguas
CREATE EVENT IF NOT EXISTS evt_expire_waiting_list
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE + INTERVAL 1 DAY + INTERVAL 2 HOUR
DO
BEGIN
    UPDATE waiting_list
    SET status = 'expired'
    WHERE status = 'waiting'
      AND date_until IS NOT NULL
      AND date_until < CURDATE();
END //

DELIMITER ;


-- ============================================================================
-- FINALIZACIÓN
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- Mensaje de confirmación
SELECT 'Base de datos SHALOM DENTAL creada exitosamente' AS mensaje;
SELECT COUNT(*) AS total_tablas FROM information_schema.tables WHERE table_schema = 'shalom_dental';
SELECT COUNT(*) AS total_roles FROM roles;
SELECT COUNT(*) AS total_permisos FROM permissions;
SELECT COUNT(*) AS total_tipos_recurso FROM resource_types;
