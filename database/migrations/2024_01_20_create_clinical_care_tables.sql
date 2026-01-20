-- ============================================================================
-- SHALOM DENTAL - MIGRACIÓN: MÓDULO DE ATENCIÓN CLÍNICA DENTAL
-- Script de Base de Datos - MySQL 8.0+ / MariaDB 10.5+
-- Versión: 1.0.0
-- Fecha: 2024-01-20
-- ============================================================================
--
-- INSTRUCCIONES DE USO:
-- 1. Ejecutar: mysql -u root -p shalom_dental < 2024_01_20_create_clinical_care_tables.sql
--
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE shalom_dental;


-- ============================================================================
-- SECCIÓN 1: CATEGORÍAS DE SERVICIOS DENTALES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: dental_service_categories
-- Descripción: Categorías para clasificar servicios dentales
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS dental_service_categories;
CREATE TABLE dental_service_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,

    code VARCHAR(20) NOT NULL COMMENT 'Código único de categoría',
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,

    -- Visual
    color_hex VARCHAR(7) DEFAULT '#1E4D3A',
    icon VARCHAR(50) NULL,

    -- Estado
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE INDEX idx_service_categories_code (organization_id, code),
    INDEX idx_service_categories_active (organization_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Categorías de servicios dentales';


-- ============================================================================
-- SECCIÓN 2: MATERIALES DENTALES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: dental_materials
-- Descripción: Catálogo de materiales e insumos dentales
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS dental_materials;
CREATE TABLE dental_materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,

    code VARCHAR(50) NOT NULL COMMENT 'Código único del material',
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,

    -- Categorización
    category VARCHAR(50) NULL COMMENT 'restaurativo, endodontico, quirurgico, etc.',

    -- Inventario (básico)
    unit VARCHAR(20) DEFAULT 'unidad' COMMENT 'unidad, ml, gr, etc.',
    unit_cost DECIMAL(10,2) NULL COMMENT 'Costo unitario referencial',

    -- Estado
    is_active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE INDEX idx_materials_code (organization_id, code),
    INDEX idx_materials_category (organization_id, category),
    INDEX idx_materials_active (organization_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de materiales dentales';


-- ----------------------------------------------------------------------------
-- Tabla: service_materials
-- Descripción: Materiales requeridos por tipo de servicio
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS service_materials;
CREATE TABLE service_materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_type_id INT UNSIGNED NOT NULL,
    material_id INT UNSIGNED NOT NULL,

    quantity_default DECIMAL(10,2) DEFAULT 1 COMMENT 'Cantidad por defecto',
    is_required BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE INDEX idx_service_materials_unique (appointment_type_id, material_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Materiales por tipo de servicio';


-- ============================================================================
-- SECCIÓN 3: HISTORIAL CLÍNICO DEL PACIENTE
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: patient_clinical_records
-- Descripción: Historial clínico estructurado del paciente
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS patient_clinical_records;
CREATE TABLE patient_clinical_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,

    -- Antecedentes médicos
    medical_history JSON NULL COMMENT 'Antecedentes médicos estructurados',
    surgical_history TEXT NULL COMMENT 'Antecedentes quirúrgicos',
    family_history TEXT NULL COMMENT 'Antecedentes familiares',

    -- Hábitos
    habits JSON NULL COMMENT 'Hábitos: tabaco, alcohol, bruxismo, etc.',

    -- Dental
    dental_history TEXT NULL COMMENT 'Antecedentes odontológicos',
    last_dental_visit DATE NULL,
    oral_hygiene_frequency VARCHAR(50) NULL COMMENT 'diario, 2x/dia, etc.',

    -- Examen extraoral
    extraoral_exam JSON NULL COMMENT 'Examen extraoral: ATM, ganglios, etc.',

    -- Examen intraoral
    intraoral_exam JSON NULL COMMENT 'Examen intraoral: tejidos blandos, etc.',

    -- Oclusión
    occlusion_type VARCHAR(50) NULL COMMENT 'Clase I, II, III',
    occlusion_notes TEXT NULL,

    -- Diagnósticos generales
    general_diagnosis TEXT NULL,

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NULL,
    updated_by_user_id INT UNSIGNED NULL,

    UNIQUE INDEX idx_clinical_records_patient (patient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial clínico del paciente';


-- ============================================================================
-- SECCIÓN 4: ODONTOGRAMA
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: patient_odontogram
-- Descripción: Estado actual de cada pieza dental (notación FDI)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS patient_odontogram;
CREATE TABLE patient_odontogram (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,

    tooth_number VARCHAR(3) NOT NULL COMMENT 'Notación FDI: 11-48 permanentes, 51-85 deciduos',
    tooth_type ENUM('permanent', 'deciduous') DEFAULT 'permanent',

    -- Estado general de la pieza
    tooth_status ENUM(
        'healthy',           -- Sano
        'decayed',           -- Caries
        'filled',            -- Restaurado/Obturado
        'crowned',           -- Corona
        'missing',           -- Ausente
        'extracted',         -- Extracción indicada/realizada
        'impacted',          -- Impactado
        'implant',           -- Implante
        'bridge_pontic',     -- Puente (pieza póntico)
        'bridge_abutment',   -- Puente (pilar)
        'root_canal',        -- Endodoncia
        'prosthetic',        -- Prótesis removible
        'sealant',           -- Sellante
        'veneer',            -- Carilla
        'fracture'           -- Fracturado
    ) DEFAULT 'healthy',

    -- Estado por superficie (JSON para flexibilidad)
    -- Superficies: O(oclusal), M(mesial), D(distal), V(vestibular), L/P(lingual/palatino)
    surfaces JSON NULL COMMENT '{"O": "caries", "M": "filled", ...}',

    -- Movilidad dental
    mobility ENUM('0', 'I', 'II', 'III') DEFAULT '0',

    -- Estado periodontal
    periodontal_status ENUM('healthy', 'gingivitis', 'periodontitis_mild', 'periodontitis_moderate', 'periodontitis_severe') DEFAULT 'healthy',
    pocket_depth JSON NULL COMMENT 'Profundidad de sondaje por superficie',
    gingival_recession JSON NULL COMMENT 'Recesión gingival por superficie',

    -- Notas
    notes TEXT NULL,

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by_user_id INT UNSIGNED NULL,

    UNIQUE INDEX idx_odontogram_patient_tooth (patient_id, tooth_number),
    INDEX idx_odontogram_status (patient_id, tooth_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Odontograma del paciente';


-- ----------------------------------------------------------------------------
-- Tabla: odontogram_history
-- Descripción: Historial de cambios en piezas dentales
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS odontogram_history;
CREATE TABLE odontogram_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    tooth_number VARCHAR(3) NOT NULL,
    appointment_id INT UNSIGNED NULL COMMENT 'Cita donde se registró el cambio',

    -- Estado anterior y nuevo
    previous_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    previous_surfaces JSON NULL,
    new_surfaces JSON NULL,

    -- Procedimiento realizado
    procedure_description VARCHAR(500) NULL,

    -- Auditoría
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changed_by_user_id INT UNSIGNED NOT NULL,

    INDEX idx_odontogram_history_patient (patient_id, tooth_number),
    INDEX idx_odontogram_history_date (changed_at),
    INDEX idx_odontogram_history_appointment (appointment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de cambios del odontograma';


-- ============================================================================
-- SECCIÓN 5: PLANES DE TRATAMIENTO
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: treatment_plans
-- Descripción: Planes de tratamiento del paciente
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS treatment_plans;
CREATE TABLE treatment_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    patient_id INT UNSIGNED NOT NULL,

    -- Identificación
    code VARCHAR(20) NULL COMMENT 'Código del plan',
    name VARCHAR(200) NOT NULL COMMENT 'Nombre descriptivo del plan',
    description TEXT NULL,

    -- Estado del plan
    status ENUM(
        'draft',        -- Borrador
        'proposed',     -- Propuesto al paciente
        'accepted',     -- Aceptado por paciente
        'in_progress',  -- En progreso
        'completed',    -- Completado
        'cancelled',    -- Cancelado
        'on_hold'       -- En pausa
    ) DEFAULT 'draft',

    -- Fechas
    proposed_at TIMESTAMP NULL,
    accepted_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    estimated_completion_date DATE NULL,

    -- Prioridad
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',

    -- Totales calculados
    total_items INT DEFAULT 0,
    completed_items INT DEFAULT 0,
    total_estimated DECIMAL(12,2) DEFAULT 0,
    total_invoiced DECIMAL(12,2) DEFAULT 0,

    -- Notas
    notes TEXT NULL,
    patient_observations TEXT NULL COMMENT 'Observaciones del paciente',

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NOT NULL,

    INDEX idx_treatment_plans_patient (patient_id, status),
    INDEX idx_treatment_plans_status (organization_id, status),
    INDEX idx_treatment_plans_code (organization_id, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Planes de tratamiento';


-- ----------------------------------------------------------------------------
-- Tabla: treatment_plan_items
-- Descripción: Items/procedimientos del plan de tratamiento
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS treatment_plan_items;
CREATE TABLE treatment_plan_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    treatment_plan_id INT UNSIGNED NOT NULL,
    appointment_type_id INT UNSIGNED NOT NULL COMMENT 'Tipo de servicio',

    -- Secuencia y agrupación
    sequence_order INT DEFAULT 0 COMMENT 'Orden de ejecución',
    phase VARCHAR(50) NULL COMMENT 'Fase del tratamiento',

    -- Pieza dental (si aplica)
    tooth_number VARCHAR(3) NULL,
    surfaces VARCHAR(10) NULL COMMENT 'Superficies: O,M,D,V,L',

    -- Descripción
    description TEXT NULL COMMENT 'Descripción específica',

    -- Estado
    status ENUM(
        'pending',      -- Pendiente
        'scheduled',    -- Agendado
        'in_progress',  -- En progreso
        'completed',    -- Completado
        'cancelled'     -- Cancelado
    ) DEFAULT 'pending',

    -- Relación con cita
    scheduled_appointment_id INT UNSIGNED NULL,
    completed_appointment_id INT UNSIGNED NULL,

    -- Costos
    estimated_price DECIMAL(10,2) NULL,
    final_price DECIMAL(10,2) NULL,
    is_invoiced BOOLEAN DEFAULT FALSE,
    invoice_item_id INT UNSIGNED NULL,

    -- Fechas
    scheduled_date DATE NULL,
    completed_at TIMESTAMP NULL,

    -- Notas
    notes TEXT NULL,

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_treatment_items_plan (treatment_plan_id, sequence_order),
    INDEX idx_treatment_items_status (treatment_plan_id, status),
    INDEX idx_treatment_items_tooth (treatment_plan_id, tooth_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Items del plan de tratamiento';


-- ============================================================================
-- SECCIÓN 6: NOTAS CLÍNICAS (SOAP)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: clinical_notes
-- Descripción: Notas clínicas SOAP por cita
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS clinical_notes;
CREATE TABLE clinical_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL,
    patient_id INT UNSIGNED NOT NULL,
    professional_id INT UNSIGNED NOT NULL,

    -- Formato SOAP
    subjective TEXT NULL COMMENT 'S: Lo que refiere el paciente',
    objective TEXT NULL COMMENT 'O: Hallazgos del examen',
    assessment TEXT NULL COMMENT 'A: Diagnóstico/Evaluación',
    plan TEXT NULL COMMENT 'P: Plan de acción',

    -- Campos adicionales
    chief_complaint VARCHAR(500) NULL COMMENT 'Motivo de consulta',
    vital_signs JSON NULL COMMENT 'Signos vitales: PA, pulso, etc.',

    -- Estado de la nota
    status ENUM('draft', 'signed', 'amended') DEFAULT 'draft',

    -- Firma
    signed_at TIMESTAMP NULL,
    signed_by_user_id INT UNSIGNED NULL,

    -- Enmiendas
    amendment_notes TEXT NULL,
    amended_at TIMESTAMP NULL,
    amended_by_user_id INT UNSIGNED NULL,

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE INDEX idx_clinical_notes_appointment (appointment_id),
    INDEX idx_clinical_notes_patient (patient_id, created_at DESC),
    INDEX idx_clinical_notes_professional (professional_id, created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Notas clínicas SOAP';


-- ============================================================================
-- SECCIÓN 7: PROCEDIMIENTOS REALIZADOS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: appointment_procedures
-- Descripción: Procedimientos realizados en cada cita
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS appointment_procedures;
CREATE TABLE appointment_procedures (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL,
    appointment_type_id INT UNSIGNED NOT NULL COMMENT 'Tipo de procedimiento',
    treatment_plan_item_id INT UNSIGNED NULL COMMENT 'Item del plan (si aplica)',

    -- Pieza dental (si aplica)
    tooth_number VARCHAR(3) NULL,
    surfaces VARCHAR(10) NULL COMMENT 'Superficies trabajadas',

    -- Descripción
    description TEXT NULL,
    notes TEXT NULL,

    -- Cantidad y precio
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL,

    -- Impuesto
    tax_code ENUM('0', '2', '3', '4', '5', '6', '7', '8') DEFAULT '4' COMMENT 'Código impuesto SRI',
    tax_percentage DECIMAL(5,2) DEFAULT 15.00,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,

    -- Estado de facturación
    is_invoiced BOOLEAN DEFAULT FALSE,
    invoice_id INT UNSIGNED NULL,
    invoice_item_id INT UNSIGNED NULL,

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NOT NULL,

    INDEX idx_procedures_appointment (appointment_id),
    INDEX idx_procedures_tooth (appointment_id, tooth_number),
    INDEX idx_procedures_invoiced (is_invoiced),
    INDEX idx_procedures_plan_item (treatment_plan_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Procedimientos realizados por cita';


-- ============================================================================
-- SECCIÓN 8: CONSENTIMIENTOS INFORMADOS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: consent_forms
-- Descripción: Plantillas de consentimiento informado
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS consent_forms;
CREATE TABLE consent_forms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,

    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,

    -- Contenido
    content_template TEXT NOT NULL COMMENT 'Plantilla HTML/Markdown',

    -- Procedimientos asociados
    applies_to_categories JSON NULL COMMENT 'Categorías de servicio',
    applies_to_services JSON NULL COMMENT 'Servicios específicos',

    -- Estado
    is_active BOOLEAN DEFAULT TRUE,
    version VARCHAR(20) DEFAULT '1.0',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NULL,

    UNIQUE INDEX idx_consent_forms_code (organization_id, code),
    INDEX idx_consent_forms_active (organization_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Plantillas de consentimiento informado';


-- ----------------------------------------------------------------------------
-- Tabla: patient_consents
-- Descripción: Consentimientos firmados por pacientes
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS patient_consents;
CREATE TABLE patient_consents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    consent_form_id INT UNSIGNED NOT NULL,
    appointment_id INT UNSIGNED NULL,

    -- Contenido firmado (snapshot)
    content_signed TEXT NOT NULL COMMENT 'Contenido al momento de firmar',

    -- Firma
    signed_at TIMESTAMP NOT NULL,
    signature_data TEXT NULL COMMENT 'Firma digital base64',
    signature_method ENUM('digital', 'physical', 'verbal') DEFAULT 'digital',

    -- Testigo (si aplica)
    witness_name VARCHAR(200) NULL,
    witness_id_number VARCHAR(20) NULL,

    -- Archivo
    document_path VARCHAR(500) NULL COMMENT 'PDF generado',

    -- Estado
    is_valid BOOLEAN DEFAULT TRUE,
    revoked_at TIMESTAMP NULL,
    revocation_reason TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NOT NULL,

    INDEX idx_patient_consents_patient (patient_id),
    INDEX idx_patient_consents_form (consent_form_id),
    INDEX idx_patient_consents_appointment (appointment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Consentimientos firmados';


-- ============================================================================
-- SECCIÓN 9: MODIFICACIONES A TABLAS EXISTENTES
-- ============================================================================

-- Agregar campos a appointment_types
ALTER TABLE appointment_types
    ADD COLUMN category_id INT UNSIGNED NULL AFTER organization_id,
    ADD COLUMN requires_consent BOOLEAN DEFAULT FALSE AFTER price_default,
    ADD COLUMN applies_to_teeth BOOLEAN DEFAULT FALSE AFTER requires_consent,
    ADD COLUMN max_teeth_per_session INT NULL AFTER applies_to_teeth,
    ADD COLUMN tax_percentage DECIMAL(5,2) DEFAULT 15.00 AFTER price_default,
    ADD INDEX idx_appointment_types_category (category_id);


-- ============================================================================
-- SECCIÓN 10: FOREIGN KEYS
-- ============================================================================

-- dental_service_categories
ALTER TABLE dental_service_categories
    ADD CONSTRAINT fk_service_categories_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- dental_materials
ALTER TABLE dental_materials
    ADD CONSTRAINT fk_materials_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- service_materials
ALTER TABLE service_materials
    ADD CONSTRAINT fk_service_materials_type
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_service_materials_material
    FOREIGN KEY (material_id) REFERENCES dental_materials(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- patient_clinical_records
ALTER TABLE patient_clinical_records
    ADD CONSTRAINT fk_clinical_records_patient
    FOREIGN KEY (patient_id) REFERENCES patients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_clinical_records_created_by
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_clinical_records_updated_by
    FOREIGN KEY (updated_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- patient_odontogram
ALTER TABLE patient_odontogram
    ADD CONSTRAINT fk_odontogram_patient
    FOREIGN KEY (patient_id) REFERENCES patients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_odontogram_updated_by
    FOREIGN KEY (updated_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- odontogram_history
ALTER TABLE odontogram_history
    ADD CONSTRAINT fk_odontogram_history_patient
    FOREIGN KEY (patient_id) REFERENCES patients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_odontogram_history_appointment
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_odontogram_history_changed_by
    FOREIGN KEY (changed_by_user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- treatment_plans
ALTER TABLE treatment_plans
    ADD CONSTRAINT fk_treatment_plans_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_treatment_plans_patient
    FOREIGN KEY (patient_id) REFERENCES patients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_treatment_plans_created_by
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- treatment_plan_items
ALTER TABLE treatment_plan_items
    ADD CONSTRAINT fk_plan_items_plan
    FOREIGN KEY (treatment_plan_id) REFERENCES treatment_plans(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_plan_items_type
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_plan_items_scheduled
    FOREIGN KEY (scheduled_appointment_id) REFERENCES appointments(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_plan_items_completed
    FOREIGN KEY (completed_appointment_id) REFERENCES appointments(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_plan_items_invoice_item
    FOREIGN KEY (invoice_item_id) REFERENCES invoice_items(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- clinical_notes
ALTER TABLE clinical_notes
    ADD CONSTRAINT fk_clinical_notes_appointment
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_clinical_notes_patient
    FOREIGN KEY (patient_id) REFERENCES patients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_clinical_notes_professional
    FOREIGN KEY (professional_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_clinical_notes_signed_by
    FOREIGN KEY (signed_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_clinical_notes_amended_by
    FOREIGN KEY (amended_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- appointment_procedures
ALTER TABLE appointment_procedures
    ADD CONSTRAINT fk_procedures_appointment
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_procedures_type
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_procedures_plan_item
    FOREIGN KEY (treatment_plan_item_id) REFERENCES treatment_plan_items(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_procedures_invoice
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_procedures_invoice_item
    FOREIGN KEY (invoice_item_id) REFERENCES invoice_items(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_procedures_created_by
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- consent_forms
ALTER TABLE consent_forms
    ADD CONSTRAINT fk_consent_forms_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_consent_forms_created_by
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- patient_consents
ALTER TABLE patient_consents
    ADD CONSTRAINT fk_patient_consents_patient
    FOREIGN KEY (patient_id) REFERENCES patients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_patient_consents_form
    FOREIGN KEY (consent_form_id) REFERENCES consent_forms(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_patient_consents_appointment
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_patient_consents_created_by
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- appointment_types.category_id
ALTER TABLE appointment_types
    ADD CONSTRAINT fk_appointment_types_category
    FOREIGN KEY (category_id) REFERENCES dental_service_categories(id)
    ON DELETE SET NULL ON UPDATE CASCADE;


-- ============================================================================
-- SECCIÓN 11: PERMISOS DEL MÓDULO CLÍNICO
-- ============================================================================

-- Insertar nuevos permisos
INSERT INTO permissions (module, resource, action, description) VALUES
-- Historial clínico
('clinical', 'records', 'view', 'Ver historial clínico'),
('clinical', 'records', 'edit', 'Editar historial clínico'),

-- Odontograma
('clinical', 'odontogram', 'view', 'Ver odontograma'),
('clinical', 'odontogram', 'edit', 'Editar odontograma'),

-- Notas clínicas
('clinical', 'notes', 'create', 'Crear notas clínicas'),
('clinical', 'notes', 'view', 'Ver notas clínicas'),
('clinical', 'notes', 'sign', 'Firmar notas clínicas'),
('clinical', 'notes', 'amend', 'Enmendar notas clínicas'),

-- Planes de tratamiento
('clinical', 'treatment_plans', 'view', 'Ver planes de tratamiento'),
('clinical', 'treatment_plans', 'create', 'Crear planes de tratamiento'),
('clinical', 'treatment_plans', 'edit', 'Editar planes de tratamiento'),
('clinical', 'treatment_plans', 'delete', 'Eliminar planes de tratamiento'),

-- Procedimientos
('clinical', 'procedures', 'create', 'Registrar procedimientos'),
('clinical', 'procedures', 'view', 'Ver procedimientos'),
('clinical', 'procedures', 'invoice', 'Facturar procedimientos'),

-- Consentimientos
('clinical', 'consents', 'view', 'Ver consentimientos'),
('clinical', 'consents', 'create', 'Crear consentimientos'),
('clinical', 'consents', 'sign', 'Firmar consentimientos'),

-- Configuración de servicios
('config', 'service_categories', 'view', 'Ver categorías de servicios'),
('config', 'service_categories', 'manage', 'Gestionar categorías de servicios'),
('config', 'materials', 'view', 'Ver materiales'),
('config', 'materials', 'manage', 'Gestionar materiales');

-- Asignar permisos a super_admin
INSERT INTO role_permissions (role_id, permission_id)
SELECT
    (SELECT id FROM roles WHERE code = 'super_admin'),
    id
FROM permissions
WHERE module IN ('clinical', 'config')
AND resource IN ('records', 'odontogram', 'notes', 'treatment_plans', 'procedures', 'consents', 'service_categories', 'materials');

-- Asignar permisos a admin
INSERT INTO role_permissions (role_id, permission_id)
SELECT
    (SELECT id FROM roles WHERE code = 'admin'),
    id
FROM permissions
WHERE module IN ('clinical', 'config')
AND resource IN ('records', 'odontogram', 'notes', 'treatment_plans', 'procedures', 'consents', 'service_categories', 'materials');

-- Asignar permisos a odontólogo
INSERT INTO role_permissions (role_id, permission_id)
SELECT
    (SELECT id FROM roles WHERE code = 'odontologo'),
    p.id
FROM permissions p
WHERE
    (p.module = 'clinical' AND p.resource IN ('records', 'odontogram', 'notes', 'treatment_plans', 'procedures', 'consents'))
    OR (p.module = 'config' AND p.resource IN ('service_categories', 'materials') AND p.action = 'view');


-- ============================================================================
-- SECCIÓN 12: VISTAS ÚTILES
-- ============================================================================

-- Vista: Odontograma completo del paciente
CREATE OR REPLACE VIEW v_patient_odontogram AS
SELECT
    o.patient_id,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    o.tooth_number,
    o.tooth_type,
    o.tooth_status,
    o.surfaces,
    o.mobility,
    o.periodontal_status,
    o.notes,
    o.updated_at,
    CONCAT(u.first_name, ' ', u.last_name) AS updated_by
FROM patient_odontogram o
JOIN patients p ON o.patient_id = p.id
LEFT JOIN users u ON o.updated_by_user_id = u.id
ORDER BY o.patient_id, o.tooth_number;

-- Vista: Planes de tratamiento activos
CREATE OR REPLACE VIEW v_active_treatment_plans AS
SELECT
    tp.id,
    tp.organization_id,
    tp.patient_id,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    tp.code,
    tp.name,
    tp.status,
    tp.priority,
    tp.total_items,
    tp.completed_items,
    ROUND((tp.completed_items / NULLIF(tp.total_items, 0)) * 100, 1) AS progress_percentage,
    tp.total_estimated,
    tp.total_invoiced,
    tp.created_at,
    CONCAT(u.first_name, ' ', u.last_name) AS created_by
FROM treatment_plans tp
JOIN patients p ON tp.patient_id = p.id
JOIN users u ON tp.created_by_user_id = u.id
WHERE tp.status IN ('draft', 'proposed', 'accepted', 'in_progress')
ORDER BY tp.priority DESC, tp.created_at DESC;

-- Vista: Procedimientos pendientes de facturar
CREATE OR REPLACE VIEW v_pending_invoice_procedures AS
SELECT
    ap.id,
    ap.appointment_id,
    a.scheduled_date,
    ap.patient_id,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    at.code AS service_code,
    at.name AS service_name,
    ap.tooth_number,
    ap.surfaces,
    ap.quantity,
    ap.unit_price,
    ap.subtotal,
    ap.tax_amount,
    ap.total,
    ap.created_at
FROM appointment_procedures ap
JOIN appointments a ON ap.appointment_id = a.id
JOIN patients p ON ap.patient_id = p.id
JOIN appointment_types at ON ap.appointment_type_id = at.id
WHERE ap.is_invoiced = FALSE
ORDER BY a.scheduled_date DESC, ap.created_at DESC;


-- ============================================================================
-- FINALIZACIÓN
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Migración de módulo clínico completada exitosamente' AS mensaje;
