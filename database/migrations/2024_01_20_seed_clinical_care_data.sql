-- ============================================================================
-- SHALOM DENTAL - DATOS INICIALES: MÓDULO DE ATENCIÓN CLÍNICA DENTAL
-- Script de Seeds - MySQL 8.0+ / MariaDB 10.5+
-- Fecha: 2024-01-20
-- ============================================================================
--
-- INSTRUCCIONES: Ejecutar después de crear las tablas clínicas
-- mysql -u root -p shalom_dental < 2024_01_20_seed_clinical_care_data.sql
--
-- ============================================================================

USE shalom_dental;

-- ============================================================================
-- CATEGORÍAS DE SERVICIOS DENTALES
-- ============================================================================

-- Nota: organization_id = 1 asume que existe una organización inicial
-- Ajustar según la organización destino

INSERT INTO dental_service_categories (organization_id, code, name, description, color_hex, icon, sort_order) VALUES
-- Diagnóstico y Prevención
(1, 'DIAG', 'Diagnóstico', 'Evaluaciones, radiografías y diagnóstico inicial', '#3B82F6', 'clipboard-document-check', 1),
(1, 'PREV', 'Prevención', 'Limpieza, profilaxis, sellantes y flúor', '#10B981', 'shield-check', 2),

-- Tratamientos Restauradores
(1, 'REST', 'Restauración', 'Resinas, amalgamas, incrustaciones y reconstrucciones', '#8B5CF6', 'wrench-screwdriver', 3),
(1, 'ENDO', 'Endodoncia', 'Tratamientos de conducto y pulpectomías', '#F59E0B', 'beaker', 4),
(1, 'PERI', 'Periodoncia', 'Raspado, curetaje y tratamiento periodontal', '#EF4444', 'heart', 5),

-- Cirugía
(1, 'CIRU', 'Cirugía Oral', 'Extracciones simples y quirúrgicas', '#DC2626', 'scissors', 6),
(1, 'IMPL', 'Implantología', 'Implantes dentales y rehabilitación', '#7C3AED', 'cube', 7),

-- Rehabilitación
(1, 'PROT', 'Prótesis', 'Coronas, puentes, prótesis removibles y totales', '#0891B2', 'puzzle-piece', 8),
(1, 'ORTO', 'Ortodoncia', 'Brackets, alineadores y ortopedia maxilar', '#EC4899', 'arrows-pointing-out', 9),

-- Estética
(1, 'ESTE', 'Estética Dental', 'Blanqueamiento, carillas y diseño de sonrisa', '#F472B6', 'sparkles', 10),

-- Urgencias
(1, 'URGE', 'Urgencias', 'Atención de emergencias dentales', '#B91C1C', 'exclamation-triangle', 11);


-- ============================================================================
-- ACTUALIZAR TIPOS DE CITA CON CATEGORÍAS
-- ============================================================================

-- Actualizar appointment_types existentes para asignar categorías
-- (Esto asume que ya existen tipos de cita creados)

UPDATE appointment_types at
JOIN dental_service_categories dsc ON dsc.organization_id = at.organization_id
SET at.category_id = dsc.id
WHERE
    (at.code LIKE '%LIMPIEZA%' AND dsc.code = 'PREV')
    OR (at.code LIKE '%PROFILAXIS%' AND dsc.code = 'PREV')
    OR (at.code LIKE '%RESINA%' AND dsc.code = 'REST')
    OR (at.code LIKE '%EXTRAC%' AND dsc.code = 'CIRU')
    OR (at.code LIKE '%ENDOD%' AND dsc.code = 'ENDO')
    OR (at.code LIKE '%CONDUCTO%' AND dsc.code = 'ENDO')
    OR (at.code LIKE '%CORONA%' AND dsc.code = 'PROT')
    OR (at.code LIKE '%CONSULT%' AND dsc.code = 'DIAG')
    OR (at.code LIKE '%EVALUA%' AND dsc.code = 'DIAG')
    OR (at.code LIKE '%RADIOG%' AND dsc.code = 'DIAG');


-- ============================================================================
-- CATÁLOGO DE MATERIALES DENTALES
-- ============================================================================

INSERT INTO dental_materials (organization_id, code, name, description, category, unit, unit_cost) VALUES
-- Materiales Restauradores
(1, 'RES-A1', 'Resina Compuesta A1', 'Resina fotocurable tono A1', 'restaurativo', 'jeringa', 45.00),
(1, 'RES-A2', 'Resina Compuesta A2', 'Resina fotocurable tono A2', 'restaurativo', 'jeringa', 45.00),
(1, 'RES-A3', 'Resina Compuesta A3', 'Resina fotocurable tono A3', 'restaurativo', 'jeringa', 45.00),
(1, 'RES-A35', 'Resina Compuesta A3.5', 'Resina fotocurable tono A3.5', 'restaurativo', 'jeringa', 45.00),
(1, 'RES-B1', 'Resina Compuesta B1', 'Resina fotocurable tono B1', 'restaurativo', 'jeringa', 45.00),
(1, 'RES-FLOW', 'Resina Flow', 'Resina fluida para bases', 'restaurativo', 'jeringa', 38.00),
(1, 'ADHES', 'Adhesivo Dental', 'Sistema adhesivo universal', 'restaurativo', 'ml', 2.50),
(1, 'GRABADOR', 'Ácido Grabador 37%', 'Ácido fosfórico para grabado', 'restaurativo', 'jeringa', 8.00),
(1, 'IONOM-V', 'Ionómero de Vidrio', 'Cemento de ionómero de vidrio', 'restaurativo', 'kit', 65.00),
(1, 'AMALG', 'Amalgama Dental', 'Cápsulas de amalgama', 'restaurativo', 'cápsula', 3.50),

-- Materiales de Endodoncia
(1, 'LIMA-K-15', 'Lima K #15', 'Lima endodóntica manual #15', 'endodontico', 'unidad', 2.00),
(1, 'LIMA-K-20', 'Lima K #20', 'Lima endodóntica manual #20', 'endodontico', 'unidad', 2.00),
(1, 'LIMA-K-25', 'Lima K #25', 'Lima endodóntica manual #25', 'endodontico', 'unidad', 2.00),
(1, 'LIMA-K-30', 'Lima K #30', 'Lima endodóntica manual #30', 'endodontico', 'unidad', 2.00),
(1, 'GUTAP-30', 'Gutapercha #30', 'Conos de gutapercha #30', 'endodontico', 'caja', 12.00),
(1, 'GUTAP-35', 'Gutapercha #35', 'Conos de gutapercha #35', 'endodontico', 'caja', 12.00),
(1, 'CEMENTO-ENDO', 'Cemento Endodóntico', 'Sellador de conductos', 'endodontico', 'tubo', 35.00),
(1, 'HIPOCL', 'Hipoclorito de Sodio', 'Irrigante endodóntico 5.25%', 'endodontico', 'litro', 8.00),
(1, 'EDTA', 'EDTA 17%', 'Quelante para conductos', 'endodontico', 'frasco', 15.00),

-- Materiales Quirúrgicos
(1, 'ANEST-LIDO', 'Lidocaína 2%', 'Anestésico local con epinefrina', 'quirurgico', 'carpule', 1.20),
(1, 'ANEST-MEPI', 'Mepivacaína 3%', 'Anestésico sin vasoconstrictor', 'quirurgico', 'carpule', 1.50),
(1, 'SUTURA-30', 'Sutura 3-0', 'Hilo de sutura seda 3-0', 'quirurgico', 'sobre', 4.00),
(1, 'SUTURA-40', 'Sutura 4-0', 'Hilo de sutura seda 4-0', 'quirurgico', 'sobre', 4.00),
(1, 'GASA-EST', 'Gasa Estéril', 'Gasas estériles 10x10', 'quirurgico', 'paquete', 2.00),
(1, 'ESPONJA-GEL', 'Esponja Hemostática', 'Gelfoam para hemostasia', 'quirurgico', 'unidad', 8.00),
(1, 'COLAGENO', 'Membrana de Colágeno', 'Para regeneración ósea', 'quirurgico', 'unidad', 85.00),

-- Materiales de Profilaxis
(1, 'PASTA-PROF', 'Pasta Profiláctica', 'Pasta para profilaxis grano medio', 'profilaxis', 'frasco', 18.00),
(1, 'FLUOR-GEL', 'Flúor Gel', 'Flúor tópico en gel', 'profilaxis', 'frasco', 22.00),
(1, 'SELLANTE', 'Sellante de Fosas', 'Sellante fotocurable', 'profilaxis', 'jeringa', 28.00),

-- Materiales de Prótesis
(1, 'IMPRES-ALGN', 'Alginato', 'Material de impresión', 'protesis', 'bolsa', 15.00),
(1, 'IMPRES-SILI', 'Silicona', 'Silicona por adición', 'protesis', 'cartucho', 45.00),
(1, 'YESO-III', 'Yeso Tipo III', 'Yeso piedra', 'protesis', 'kg', 8.00),
(1, 'YESO-IV', 'Yeso Tipo IV', 'Yeso extraduro', 'protesis', 'kg', 15.00),
(1, 'CEM-TEMP', 'Cemento Temporal', 'Para cementación temporal', 'protesis', 'tubo', 12.00),
(1, 'CEM-DEFIN', 'Cemento Definitivo', 'Cemento de ionómero para cementación', 'protesis', 'kit', 55.00),

-- Materiales de Ortodoncia
(1, 'BRACKET-MET', 'Bracket Metálico', 'Bracket estándar MBT', 'ortodoncia', 'unidad', 3.00),
(1, 'BRACKET-CER', 'Bracket Cerámico', 'Bracket estético cerámico', 'ortodoncia', 'unidad', 12.00),
(1, 'ARCO-NITI-14', 'Arco NiTi .014', 'Arco termoactivado', 'ortodoncia', 'unidad', 5.00),
(1, 'ARCO-SS-16', 'Arco Acero .016', 'Arco de acero inoxidable', 'ortodoncia', 'unidad', 3.00),
(1, 'LIGADURA-EL', 'Ligadura Elástica', 'Ligaduras elásticas colores', 'ortodoncia', 'paquete', 8.00),
(1, 'CEM-ORTO', 'Cemento Ortodóntico', 'Para cementación de brackets', 'ortodoncia', 'kit', 48.00),

-- Consumibles Generales
(1, 'GUANTES-M', 'Guantes Látex M', 'Guantes descartables talla M', 'consumible', 'caja', 12.00),
(1, 'MASCARILLA', 'Mascarilla Descartable', 'Mascarilla 3 pliegues', 'consumible', 'caja', 8.00),
(1, 'EYECTOR', 'Eyector de Saliva', 'Eyectores descartables', 'consumible', 'bolsa', 6.00),
(1, 'BABERO', 'Babero Descartable', 'Baberos para pacientes', 'consumible', 'paquete', 10.00),
(1, 'VASOS-DESC', 'Vasos Descartables', 'Vasos para enjuague', 'consumible', 'paquete', 4.00);


-- ============================================================================
-- PLANTILLAS DE CONSENTIMIENTO INFORMADO
-- ============================================================================

INSERT INTO consent_forms (organization_id, code, name, description, content_template, applies_to_categories, is_active, version) VALUES
(1, 'CONS-GENERAL', 'Consentimiento General', 'Consentimiento informado para procedimientos dentales generales',
'# CONSENTIMIENTO INFORMADO PARA TRATAMIENTO DENTAL

Yo, **{{patient_name}}**, identificado/a con **{{patient_id_type}}** número **{{patient_id_number}}**, declaro que:

## 1. INFORMACIÓN RECIBIDA
He sido informado/a de manera clara y comprensible por el/la **{{professional_name}}** sobre:
- Mi diagnóstico dental actual
- El tratamiento propuesto y sus alternativas
- Los beneficios esperados del procedimiento
- Los posibles riesgos y complicaciones

## 2. TRATAMIENTO AUTORIZADO
Autorizo la realización del siguiente tratamiento:
**{{procedure_description}}**

## 3. RIESGOS GENERALES
Entiendo que todo procedimiento dental puede conllevar riesgos como:
- Dolor o molestias post-operatorias
- Inflamación temporal
- Sangrado menor
- Reacciones alérgicas a medicamentos o materiales
- Sensibilidad dental temporal o permanente

## 4. DECLARACIONES
- He tenido la oportunidad de hacer preguntas y estas han sido respondidas satisfactoriamente
- Entiendo que puedo revocar este consentimiento en cualquier momento
- Me comprometo a seguir las indicaciones post-tratamiento

## 5. FIRMA

Fecha: **{{current_date}}**

_________________________
Firma del Paciente

_________________________
Firma del Profesional
**{{professional_name}}**
Reg. Prof.: **{{professional_registration}}**',
'["DIAG", "PREV", "REST"]', TRUE, '1.0'),

(1, 'CONS-ENDODONCIA', 'Consentimiento Endodoncia', 'Consentimiento para tratamiento de conducto',
'# CONSENTIMIENTO INFORMADO PARA TRATAMIENTO DE ENDODONCIA

Yo, **{{patient_name}}**, identificado/a con **{{patient_id_type}}** número **{{patient_id_number}}**, declaro que:

## 1. DIAGNÓSTICO
Se me ha diagnosticado una afección pulpar (nervio dental) que requiere tratamiento de conducto en la pieza dental **{{tooth_number}}**.

## 2. PROCEDIMIENTO
El tratamiento consiste en:
1. Acceso a la cámara pulpar
2. Limpieza y conformación de los conductos radiculares
3. Obturación (sellado) de los conductos
4. Restauración posterior de la pieza dental

## 3. RIESGOS ESPECÍFICOS
- Fractura de instrumentos dentro del conducto
- Perforación radicular
- Filtración y reinfección
- Necesidad de retratamiento o cirugía periapical
- Posible pérdida de la pieza dental
- Oscurecimiento del diente tratado

## 4. ALTERNATIVAS
- Extracción de la pieza dental
- No tratamiento (con riesgo de infección y pérdida dental)

## 5. POST-TRATAMIENTO
Entiendo que después del tratamiento:
- Puede haber dolor moderado por 2-3 días
- Requeriré una restauración definitiva (corona o resina)
- Necesitaré controles radiográficos periódicos

Fecha: **{{current_date}}**

_________________________
Firma del Paciente

_________________________
Firma del Profesional',
'["ENDO"]', TRUE, '1.0'),

(1, 'CONS-CIRUGIA', 'Consentimiento Cirugía', 'Consentimiento para procedimientos quirúrgicos',
'# CONSENTIMIENTO INFORMADO PARA CIRUGÍA ORAL

Yo, **{{patient_name}}**, identificado/a con **{{patient_id_type}}** número **{{patient_id_number}}**, declaro que:

## 1. PROCEDIMIENTO AUTORIZADO
Autorizo la realización de: **{{procedure_description}}**
Pieza(s) dental(es): **{{tooth_number}}**

## 2. RIESGOS ESPECÍFICOS DE CIRUGÍA ORAL
- Dolor e inflamación post-operatoria
- Sangrado prolongado
- Infección de la herida quirúrgica
- Daño a piezas dentales adyacentes
- Comunicación oroantral (en extracciones superiores)
- Lesión temporal o permanente del nervio dentario inferior (parestesia)
- Fractura mandibular o de la tuberosidad
- Alveolitis (infección del alvéolo)

## 3. INSTRUCCIONES POST-OPERATORIAS
Me comprometo a seguir las indicaciones:
- No escupir ni enjuagar en las primeras 24 horas
- Aplicar hielo en la zona
- Tomar la medicación prescrita
- Dieta blanda y fría
- No fumar ni consumir alcohol
- Asistir a los controles programados

## 4. EMERGENCIAS
En caso de sangrado excesivo, fiebre alta o dolor intenso, debo contactar al consultorio o acudir a urgencias.

Fecha: **{{current_date}}**

_________________________
Firma del Paciente

_________________________
Firma del Profesional',
'["CIRU", "IMPL"]', TRUE, '1.0'),

(1, 'CONS-ORTODONCIA', 'Consentimiento Ortodoncia', 'Consentimiento para tratamiento ortodóntico',
'# CONSENTIMIENTO INFORMADO PARA TRATAMIENTO DE ORTODONCIA

Yo, **{{patient_name}}**, identificado/a con **{{patient_id_type}}** número **{{patient_id_number}}**, declaro que:

## 1. DIAGNÓSTICO Y PLAN DE TRATAMIENTO
Se me ha explicado mi diagnóstico ortodóntico y el plan de tratamiento propuesto con una duración estimada de **{{treatment_duration}}**.

## 2. TIPO DE APARATOLOGÍA
- [ ] Brackets metálicos
- [ ] Brackets cerámicos/estéticos
- [ ] Alineadores transparentes
- [ ] Aparatología removible
- [ ] Otro: _____________

## 3. RIESGOS Y COMPLICACIONES
- Molestias y dolor durante el tratamiento
- Úlceras o lesiones en tejidos blandos
- Descalcificación del esmalte (manchas blancas)
- Reabsorción radicular
- Recidiva (movimiento de dientes después del tratamiento)
- Problemas periodontales si la higiene es deficiente
- Necesidad de prolongar el tratamiento

## 4. COMPROMISOS DEL PACIENTE
- Asistir puntualmente a las citas programadas
- Mantener excelente higiene oral
- Usar los aparatos según las indicaciones
- Evitar alimentos duros o pegajosos
- Usar retenedores después del tratamiento

## 5. COSTOS Y PAGOS
El costo total del tratamiento es de $**{{total_cost}}**, pagadero según el plan acordado.

Fecha: **{{current_date}}**

_________________________
Firma del Paciente/Representante

_________________________
Firma del Profesional',
'["ORTO"]', TRUE, '1.0');


-- ============================================================================
-- MENSAJE DE CONFIRMACIÓN
-- ============================================================================

SELECT 'Datos iniciales del módulo clínico insertados exitosamente' AS mensaje;
SELECT COUNT(*) AS total_categorias FROM dental_service_categories;
SELECT COUNT(*) AS total_materiales FROM dental_materials;
SELECT COUNT(*) AS total_consentimientos FROM consent_forms;
