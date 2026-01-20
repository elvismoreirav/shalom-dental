-- ============================================================================
-- SHALOM DENTAL - MIGRACIÓN: MÓDULO DE ATENCIÓN CLÍNICA DENTAL
-- Versión: 1.0.0
-- Fecha: 2026-01-19
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE shalom_dental;

-- ============================================================================
-- SECCIÓN 1: CATEGORÍAS DE SERVICIOS DENTALES
-- ============================================================================

CREATE TABLE IF NOT EXISTS dental_service_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,

    code VARCHAR(20) NOT NULL COMMENT 'Código único de categoría',
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,

    color_hex VARCHAR(7) DEFAULT '#1E4D3A',
    icon VARCHAR(50) NULL,

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

CREATE TABLE IF NOT EXISTS dental_materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,

    code VARCHAR(50) NOT NULL COMMENT 'Código único del material',
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,

    category VARCHAR(50) NULL COMMENT 'restaurativo, endodontico, quirurgico, etc.',
    unit VARCHAR(20) DEFAULT 'unidad' COMMENT 'unidad, ml, gr, etc.',
    unit_cost DECIMAL(10,2) NULL COMMENT 'Costo unitario referencial',

    is_active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE INDEX idx_materials_code (organization_id, code),
    INDEX idx_materials_category (organization_id, category),
    INDEX idx_materials_active (organization_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de materiales dentales';

CREATE TABLE IF NOT EXISTS service_materials (
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

CREATE TABLE IF NOT EXISTS patient_clinical_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,

    medical_history JSON NULL COMMENT 'Antecedentes médicos estructurados',
    surgical_history TEXT NULL COMMENT 'Antecedentes quirúrgicos',
    family_history TEXT NULL COMMENT 'Antecedentes familiares',

    habits JSON NULL COMMENT 'Hábitos: tabaco, alcohol, bruxismo, etc.',

    dental_history TEXT NULL COMMENT 'Antecedentes odontológicos',
    last_dental_visit DATE NULL,
    oral_hygiene_frequency VARCHAR(50) NULL COMMENT 'diario, 2x/dia, etc.',

    extraoral_exam JSON NULL COMMENT 'Examen extraoral: ATM, ganglios, etc.',
    intraoral_exam JSON NULL COMMENT 'Examen intraoral: tejidos blandos, etc.',

    occlusion_type VARCHAR(50) NULL COMMENT 'Clase I, II, III',
    occlusion_notes TEXT NULL,

    general_diagnosis TEXT NULL,

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

CREATE TABLE IF NOT EXISTS patient_odontogram (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,

    tooth_number VARCHAR(3) NOT NULL COMMENT 'Notación FDI: 11-48 permanentes, 51-85 deciduos',
    tooth_type ENUM('permanent', 'deciduous') DEFAULT 'permanent',

    tooth_status ENUM(
        'healthy',
        'decayed',
        'filled',
        'crowned',
        'missing',
        'extracted',
        'impacted',
        'implant',
        'bridge_pontic',
        'bridge_abutment',
        'root_canal',
        'prosthetic',
        'sealant',
        'veneer',
        'fracture'
    ) DEFAULT 'healthy',

    surfaces JSON NULL COMMENT '{"O": "caries", "M": "filled", ...}',
    mobility ENUM('0', 'I', 'II', 'III') DEFAULT '0',

    periodontal_status ENUM('healthy', 'gingivitis', 'periodontitis_mild', 'periodontitis_moderate', 'periodontitis_severe') DEFAULT 'healthy',
    pocket_depth JSON NULL COMMENT 'Profundidad de sondaje por superficie',
    gingival_recession JSON NULL COMMENT 'Recesión gingival por superficie',

    notes TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by_user_id INT UNSIGNED NULL,

    UNIQUE INDEX idx_odontogram_patient_tooth (patient_id, tooth_number),
    INDEX idx_odontogram_status (patient_id, tooth_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Odontograma del paciente';

CREATE TABLE IF NOT EXISTS odontogram_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    tooth_number VARCHAR(3) NOT NULL,
    appointment_id INT UNSIGNED NULL COMMENT 'Cita donde se registró el cambio',

    previous_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    previous_surfaces JSON NULL,
    new_surfaces JSON NULL,

    procedure_description VARCHAR(500) NULL,

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

CREATE TABLE IF NOT EXISTS treatment_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    patient_id INT UNSIGNED NOT NULL,

    code VARCHAR(20) NULL COMMENT 'Código del plan',
    name VARCHAR(200) NOT NULL COMMENT 'Nombre descriptivo del plan',
    description TEXT NULL,

    status ENUM(
        'draft',
        'proposed',
        'accepted',
        'in_progress',
        'completed',
        'cancelled',
        'on_hold'
    ) DEFAULT 'draft',

    proposed_at TIMESTAMP NULL,
    accepted_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    estimated_completion_date DATE NULL,

    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',

    total_items INT DEFAULT 0,
    completed_items INT DEFAULT 0,
    total_estimated DECIMAL(12,2) DEFAULT 0,
    total_invoiced DECIMAL(12,2) DEFAULT 0,

    notes TEXT NULL,
    patient_observations TEXT NULL COMMENT 'Observaciones del paciente',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NOT NULL,

    INDEX idx_treatment_plans_patient (patient_id, status),
    INDEX idx_treatment_plans_status (organization_id, status),
    INDEX idx_treatment_plans_code (organization_id, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Planes de tratamiento';

CREATE TABLE IF NOT EXISTS treatment_plan_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    treatment_plan_id INT UNSIGNED NOT NULL,
    appointment_type_id INT UNSIGNED NOT NULL COMMENT 'Tipo de servicio',

    sequence_order INT DEFAULT 0 COMMENT 'Orden de ejecución',
    phase VARCHAR(50) NULL COMMENT 'Fase del tratamiento',

    tooth_number VARCHAR(3) NULL,
    surfaces VARCHAR(10) NULL COMMENT 'Superficies: O,M,D,V,L',

    description TEXT NULL COMMENT 'Descripción específica',

    status ENUM(
        'pending',
        'scheduled',
        'in_progress',
        'completed',
        'cancelled'
    ) DEFAULT 'pending',

    scheduled_appointment_id INT UNSIGNED NULL,
    completed_appointment_id INT UNSIGNED NULL,

    estimated_price DECIMAL(10,2) NULL,
    final_price DECIMAL(10,2) NULL,
    is_invoiced BOOLEAN DEFAULT FALSE,
    invoice_item_id INT UNSIGNED NULL,

    scheduled_date DATE NULL,
    completed_at TIMESTAMP NULL,

    notes TEXT NULL,

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

CREATE TABLE IF NOT EXISTS clinical_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL,
    patient_id INT UNSIGNED NOT NULL,
    professional_id INT UNSIGNED NOT NULL,

    subjective TEXT NULL,
    objective TEXT NULL,
    assessment TEXT NULL,
    plan TEXT NULL,

    chief_complaint VARCHAR(500) NULL,
    vital_signs JSON NULL,

    status ENUM('draft', 'signed', 'amended') DEFAULT 'draft',

    signed_at TIMESTAMP NULL,
    signed_by_user_id INT UNSIGNED NULL,

    amendment_notes TEXT NULL,
    amended_at TIMESTAMP NULL,
    amended_by_user_id INT UNSIGNED NULL,

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

CREATE TABLE IF NOT EXISTS appointment_procedures (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL,
    appointment_type_id INT UNSIGNED NOT NULL COMMENT 'Tipo de procedimiento',
    treatment_plan_item_id INT UNSIGNED NULL COMMENT 'Item del plan (si aplica)',

    tooth_number VARCHAR(3) NULL,
    surfaces VARCHAR(10) NULL COMMENT 'Superficies trabajadas',

    description TEXT NULL,
    notes TEXT NULL,

    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL,

    tax_code ENUM('0', '2', '3', '4', '5', '6', '7', '8') DEFAULT '4',
    tax_percentage DECIMAL(5,2) DEFAULT 15.00,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,

    is_invoiced BOOLEAN DEFAULT FALSE,
    invoice_id INT UNSIGNED NULL,
    invoice_item_id INT UNSIGNED NULL,

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

CREATE TABLE IF NOT EXISTS consent_forms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,

    code VARCHAR(50) NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,

    content_template TEXT NOT NULL COMMENT 'Plantilla HTML/Markdown',

    applies_to_categories JSON NULL,
    applies_to_services JSON NULL,

    is_active BOOLEAN DEFAULT TRUE,
    version VARCHAR(20) DEFAULT '1.0',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by_user_id INT UNSIGNED NULL,

    UNIQUE INDEX idx_consent_forms_code (organization_id, code),
    INDEX idx_consent_forms_active (organization_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Plantillas de consentimiento informado';

CREATE TABLE IF NOT EXISTS patient_consents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    consent_form_id INT UNSIGNED NOT NULL,
    appointment_id INT UNSIGNED NULL,

    content_signed TEXT NOT NULL,

    signed_at TIMESTAMP NOT NULL,
    signature_data TEXT NULL,
    signature_method ENUM('digital', 'physical', 'verbal') DEFAULT 'digital',

    witness_name VARCHAR(200) NULL,
    witness_id_number VARCHAR(20) NULL,

    document_path VARCHAR(500) NULL,

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
-- SECCIÓN 9: PERIODOGRAMA DENTAL
-- ============================================================================

CREATE TABLE IF NOT EXISTS periodontal_charts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Relaciones
    patient_id INT UNSIGNED NOT NULL,
    appointment_id INT UNSIGNED NULL,
    
    -- Información básica
    chart_date DATE NOT NULL COMMENT 'Fecha del examen periodontal',
    chart_type ENUM('initial', 'follow_up', 'maintenance', 'post_treatment') DEFAULT 'initial' COMMENT 'Tipo de examen',
    
    -- Datos del periodontograma (JSON con estructura completa)
    chart_data JSON NOT NULL COMMENT 'Datos completos del periodontograma',
    
    -- Notas clínicas
    notes TEXT NULL COMMENT 'Notas periodontales específicas',
    recommendations TEXT NULL COMMENT 'Recomendaciones de tratamiento',
    
    -- Metadatos
    risk_level ENUM('low', 'mild', 'moderate', 'severe') NULL COMMENT 'Nivel de riesgo periodontal',
    treatment_urgency ENUM('routine', 'soon', 'urgent', 'immediate') NULL COMMENT 'Urgencia del tratamiento',
    follow_up_date DATE NULL COMMENT 'Fecha de seguimiento recomendada',
    
    -- Auditoría
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_periodontal_patient_date (patient_id, chart_date),
    INDEX idx_periodontal_appointment (appointment_id),
    INDEX idx_periodontal_risk (risk_level, treatment_urgency),
    INDEX idx_periodontal_follow_up (follow_up_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Periodontogramas y exámenes periodontales';

-- ============================================================================
-- SECCIÓN 10: MODIFICACIONES A TABLAS EXISTENTES
-- ============================================================================

ALTER TABLE appointment_types
    ADD COLUMN IF NOT EXISTS category_id INT UNSIGNED NULL AFTER organization_id,
    ADD COLUMN IF NOT EXISTS requires_consent BOOLEAN DEFAULT FALSE AFTER price_default,
    ADD COLUMN IF NOT EXISTS applies_to_teeth BOOLEAN DEFAULT FALSE AFTER requires_consent,
    ADD COLUMN IF NOT EXISTS max_teeth_per_session INT NULL AFTER applies_to_teeth,
    ADD COLUMN IF NOT EXISTS tax_percentage DECIMAL(5,2) DEFAULT 15.00 AFTER price_default,
    ADD INDEX idx_appointment_types_category (category_id);

-- ============================================================================
-- SECCIÓN 10: FOREIGN KEYS
-- ============================================================================

ALTER TABLE dental_service_categories
    ADD CONSTRAINT fk_service_categories_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE dental_materials
    ADD CONSTRAINT fk_materials_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE service_materials
    ADD CONSTRAINT fk_service_materials_type
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_service_materials_material
    FOREIGN KEY (material_id) REFERENCES dental_materials(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

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

ALTER TABLE patient_odontogram
    ADD CONSTRAINT fk_odontogram_patient
    FOREIGN KEY (patient_id) REFERENCES patients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_odontogram_updated_by
    FOREIGN KEY (updated_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

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

ALTER TABLE treatment_plan_items
    ADD CONSTRAINT fk_plan_items_plan
    FOREIGN KEY (treatment_plan_id) REFERENCES treatment_plans(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_plan_items_type
    FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id)
    ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE periodontal_charts
    ADD CONSTRAINT fk_periodontal_charts_patient
    FOREIGN KEY (patient_id) REFERENCES patients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_periodontal_charts_appointment
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_periodontal_charts_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE;
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

ALTER TABLE consent_forms
    ADD CONSTRAINT fk_consent_forms_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT fk_consent_forms_created_by
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

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

ALTER TABLE appointment_types
    ADD CONSTRAINT fk_appointment_types_category
    FOREIGN KEY (category_id) REFERENCES dental_service_categories(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Migración de módulo clínico completada exitosamente' AS mensaje;
