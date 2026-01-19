-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 18, 2026 at 03:46 PM
-- Server version: 8.3.0
-- PHP Version: 8.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shalom_dental`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_check_appointment_conflict` (IN `p_professional_id` INT, IN `p_scheduled_date` DATE, IN `p_start_time` TIME, IN `p_end_time` TIME, IN `p_exclude_id` INT, OUT `p_has_conflict` BOOLEAN, OUT `p_conflict_info` VARCHAR(500))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cleanup_expired_sessions` ()   BEGIN
    DELETE FROM user_sessions WHERE expires_at < NOW();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_next_sequential` (IN `p_emission_point_id` INT, IN `p_document_type` VARCHAR(2), OUT `p_sequential` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_patient_counters` (IN `p_patient_id` INT)   BEGIN
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `patient_id` int UNSIGNED NOT NULL,
  `professional_id` int UNSIGNED NOT NULL,
  `appointment_type_id` int UNSIGNED NOT NULL,
  `recurring_series_id` int UNSIGNED DEFAULT NULL,
  `series_sequence_number` int DEFAULT NULL,
  `rescheduled_from_id` int UNSIGNED DEFAULT NULL,
  `rescheduled_to_id` int UNSIGNED DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_start_time` time NOT NULL,
  `scheduled_end_time` time NOT NULL,
  `duration_minutes` int NOT NULL,
  `status` enum('scheduled','confirmed','checked_in','in_progress','completed','cancelled','no_show','rescheduled','late') COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled',
  `confirmation_sent_at` timestamp NULL DEFAULT NULL,
  `reminder_sent_at` timestamp NULL DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `confirmed_via` enum('email','sms','whatsapp','phone','in_person') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `called_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `is_emergency` tinyint(1) DEFAULT '0',
  `emergency_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancellation_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancellation_source` enum('patient','clinic','professional','system') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_show_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reschedule_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `incomplete_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `internal_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by_user_id` int UNSIGNED NOT NULL,
  `cancelled_by_user_id` int UNSIGNED DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Citas agendadas';

--
-- Triggers `appointments`
--
DELIMITER $$
CREATE TRIGGER `trg_appointment_audit_insert` AFTER INSERT ON `appointments` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (organization_id, user_id, action, entity_type, entity_id, new_values, created_at)
    VALUES (NEW.organization_id, NEW.created_by_user_id, 'create', 'appointment', NEW.id, 
        JSON_OBJECT(
            'patient_id', NEW.patient_id,
            'professional_id', NEW.professional_id,
            'scheduled_date', NEW.scheduled_date,
            'scheduled_start_time', NEW.scheduled_start_time,
            'status', NEW.status
        ), NOW());
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_appointment_audit_update` AFTER UPDATE ON `appointments` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_appointment_status_change` AFTER UPDATE ON `appointments` FOR EACH ROW BEGIN
    IF OLD.status != NEW.status THEN
        IF NEW.status IN ('no_show', 'cancelled') THEN
            CALL sp_update_patient_counters(NEW.patient_id);
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_resources`
--

CREATE TABLE `appointment_resources` (
  `id` int UNSIGNED NOT NULL,
  `appointment_id` int UNSIGNED NOT NULL,
  `resource_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Recursos asignados a citas';

-- --------------------------------------------------------

--
-- Table structure for table `appointment_types`
--

CREATE TABLE `appointment_types` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `default_duration_minutes` int NOT NULL DEFAULT '30',
  `buffer_before_minutes` int DEFAULT '0',
  `buffer_after_minutes` int DEFAULT '5',
  `color_hex` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#1E4D3A',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT '0',
  `default_recurring_interval_days` int DEFAULT NULL,
  `requires_confirmation` tinyint(1) DEFAULT '1',
  `confirmation_hours_before` int DEFAULT '48',
  `reminder_hours_before` int DEFAULT '24',
  `price_default` decimal(10,2) DEFAULT NULL,
  `tax_percentage` decimal(5,2) DEFAULT '15.00',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tipos de cita';

-- --------------------------------------------------------

--
-- Table structure for table `appointment_type_resources`
--

CREATE TABLE `appointment_type_resources` (
  `id` int UNSIGNED NOT NULL,
  `appointment_type_id` int UNSIGNED NOT NULL,
  `resource_type_id` int UNSIGNED NOT NULL,
  `is_required` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Recursos por tipo de cita';

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED DEFAULT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_id` int UNSIGNED DEFAULT NULL,
  `session_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` int UNSIGNED DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `additional_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de auditoría';

-- --------------------------------------------------------

--
-- Table structure for table `contingencies`
--

CREATE TABLE `contingencies` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED DEFAULT NULL,
  `contingency_type` enum('professional_absence','resource_unavailable','location_closed','system_failure','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `affected_professional_id` int UNSIGNED DEFAULT NULL,
  `affected_resource_id` int UNSIGNED DEFAULT NULL,
  `start_datetime` timestamp NOT NULL,
  `end_datetime` timestamp NULL DEFAULT NULL,
  `is_all_day` tinyint(1) DEFAULT '0',
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `appointments_affected_count` int DEFAULT '0',
  `appointments_rescheduled_count` int DEFAULT '0',
  `appointments_cancelled_count` int DEFAULT '0',
  `status` enum('active','resolved','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by_user_id` int UNSIGNED NOT NULL,
  `resolved_by_user_id` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de contingencias';

-- --------------------------------------------------------

--
-- Table structure for table `emission_points`
--

CREATE TABLE `emission_points` (
  `id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código punto emisión SRI (001, 002...)',
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Puntos de emisión SRI';

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED DEFAULT NULL COMMENT 'NULL = todas las sedes',
  `holiday_date` date NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT '0' COMMENT 'Repite cada año',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_id` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Feriados';

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `emission_point_id` int UNSIGNED NOT NULL,
  `patient_id` int UNSIGNED DEFAULT NULL,
  `appointment_id` int UNSIGNED DEFAULT NULL,
  `document_type` enum('01','04') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '01' COMMENT '01=Factura, 04=NC',
  `establishment_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emission_point_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sequential` int UNSIGNED NOT NULL,
  `access_key` varchar(49) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `issue_time` time NOT NULL,
  `buyer_id_type` enum('04','05','06','07','08') COLLATE utf8mb4_unicode_ci NOT NULL,
  `buyer_id_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `buyer_name` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `buyer_address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtotal_no_tax` decimal(14,2) DEFAULT '0.00',
  `subtotal_0` decimal(14,2) DEFAULT '0.00',
  `subtotal_12` decimal(14,2) DEFAULT '0.00',
  `subtotal_15` decimal(14,2) DEFAULT '0.00',
  `subtotal_not_subject` decimal(14,2) DEFAULT '0.00',
  `subtotal_exempt` decimal(14,2) DEFAULT '0.00',
  `total_discount` decimal(14,2) DEFAULT '0.00',
  `subtotal` decimal(14,2) NOT NULL,
  `total_tax` decimal(14,2) DEFAULT '0.00',
  `total_ice` decimal(14,2) DEFAULT '0.00',
  `tip` decimal(14,2) DEFAULT '0.00',
  `total` decimal(14,2) NOT NULL,
  `status` enum('draft','pending','sent','authorized','rejected','voided','contingency') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `sri_received_at` timestamp NULL DEFAULT NULL,
  `sri_authorization_number` varchar(49) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sri_authorization_date` timestamp NULL DEFAULT NULL,
  `sri_receipt_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sri_error_messages` json DEFAULT NULL,
  `xml_content` mediumtext COLLATE utf8mb4_unicode_ci,
  `xml_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ride_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modified_document_id` int UNSIGNED DEFAULT NULL,
  `modification_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `voided_at` timestamp NULL DEFAULT NULL,
  `voided_by_user_id` int UNSIGNED DEFAULT NULL,
  `credit_note_id` int UNSIGNED DEFAULT NULL,
  `additional_info` json DEFAULT NULL,
  `internal_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by_user_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Facturas electrónicas';

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` int UNSIGNED NOT NULL,
  `sequence` int NOT NULL,
  `main_code` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aux_code` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(14,6) NOT NULL,
  `unit_price` decimal(14,6) NOT NULL,
  `discount_amount` decimal(14,2) DEFAULT '0.00',
  `subtotal` decimal(14,2) NOT NULL,
  `tax_code` enum('0','2','3','4','5','6','7','8') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '4',
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT '15.00',
  `tax_rate_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '4',
  `tax_amount` decimal(14,2) NOT NULL,
  `total` decimal(14,2) NOT NULL,
  `appointment_type_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle facturas';

-- --------------------------------------------------------

--
-- Table structure for table `invoice_payments`
--

CREATE TABLE `invoice_payments` (
  `id` int UNSIGNED NOT NULL,
  `invoice_id` int UNSIGNED NOT NULL,
  `payment_method_code` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `term_days` int DEFAULT '0',
  `time_unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'dias',
  `card_brand` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_last_four` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authorization_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagos de facturas';

-- --------------------------------------------------------

--
-- Table structure for table `credit_notes`
--

CREATE TABLE `credit_notes` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `invoice_id` int UNSIGNED NOT NULL,
  `issue_date` date NOT NULL,
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `status` enum('draft','issued','voided') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by_user_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notas de credito';

-- --------------------------------------------------------

--
-- Table structure for table `invoice_sequentials`
--

CREATE TABLE `invoice_sequentials` (
  `id` int UNSIGNED NOT NULL,
  `emission_point_id` int UNSIGNED NOT NULL,
  `document_type` enum('01','04','05','06','07') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '01=Fact, 04=NC, 05=ND, 06=Guía, 07=Ret',
  `current_sequential` int UNSIGNED NOT NULL DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Secuenciales SRI';

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código interno de la sede',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de la sede',
  `sri_establishment_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código establecimiento SRI (001, 002...)',
  `address` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_time` time DEFAULT '08:00:00',
  `closing_time` time DEFAULT '18:00:00',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sedes o sucursales';

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `organization_id`, `code`, `name`, `sri_establishment_code`, `address`, `city`, `province`, `phone`, `email`, `opening_time`, `closing_time`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'MATRIZ', 'Matriz Portoviejo', '001', 'Av. Manabí', 'Portoviejo', 'Manabí', NULL, NULL, '08:00:00', '18:00:00', 1, '2025-12-29 03:00:51', '2025-12-29 03:00:51');

-- --------------------------------------------------------

--
-- Table structure for table `location_users`
--

CREATE TABLE `location_users` (
  `id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0' COMMENT 'Sede principal',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Asignación de usuarios a sedes';

--
-- Dumping data for table `location_users`
--

INSERT INTO `location_users` (`id`, `location_id`, `user_id`, `is_primary`, `created_at`) VALUES
(1, 1, 1, 1, '2025-12-29 03:00:51');

-- --------------------------------------------------------

--
-- Table structure for table `mass_reschedule_logs`
--

CREATE TABLE `mass_reschedule_logs` (
  `id` int UNSIGNED NOT NULL,
  `contingency_id` int UNSIGNED DEFAULT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `appointments_processed` int DEFAULT '0',
  `appointments_rescheduled` int DEFAULT '0',
  `appointments_cancelled` int DEFAULT '0',
  `appointments_failed` int DEFAULT '0',
  `affected_appointment_ids` json DEFAULT NULL,
  `notifications_sent` int DEFAULT '0',
  `notification_template_used` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `executed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `executed_by_user_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log reprogramaciones masivas';

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `template_id` int UNSIGNED DEFAULT NULL,
  `patient_id` int UNSIGNED DEFAULT NULL,
  `appointment_id` int UNSIGNED DEFAULT NULL,
  `invoice_id` int UNSIGNED DEFAULT NULL,
  `channel` enum('email','sms','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `variables_used` json DEFAULT NULL,
  `status` enum('pending','processing','sent','failed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `retry_count` int DEFAULT '0',
  `max_retries` int DEFAULT '3',
  `next_retry_at` timestamp NULL DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `provider_message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cola de notificaciones';

-- --------------------------------------------------------

--
-- Table structure for table `notification_configs`
--

CREATE TABLE `notification_configs` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `channel` enum('email','sms','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'smtp, twilio, ultramsg',
  `config_encrypted` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_test_at` timestamp NULL DEFAULT NULL,
  `last_test_result` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuración canales notificación';

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `channel` enum('email','sms','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_template` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body_template` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `available_variables` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Plantillas de notificación';

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `id` int UNSIGNED NOT NULL,
  `ruc` varchar(13) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'RUC de la organización',
  `business_name` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Razón social',
  `trade_name` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre comercial',
  `address` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta al logo',
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'America/Guayaquil',
  `date_format` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'd/m/Y',
  `time_format` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'H:i',
  `currency_code` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Datos de la organización principal';

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `ruc`, `business_name`, `trade_name`, `address`, `phone`, `email`, `website`, `logo_path`, `timezone`, `date_format`, `time_format`, `currency_code`, `created_at`, `updated_at`) VALUES
(1, '1391823721001', 'SHALOM DENTAL S.A.', 'Shalom Dental', 'Av. Manabí y Calle Quito, Portoviejo', NULL, 'admin@shalomdental.com', NULL, NULL, 'America/Guayaquil', 'd/m/Y', 'H:i', 'USD', '2025-12-29 08:00:51', '2025-12-29 03:00:51');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `id_type` enum('cedula','ruc','pasaporte','otro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cedula',
  `id_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_secondary` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('M','F','O') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'M=Masculino, F=Femenino, O=Otro',
  `marital_status` enum('soltero','casado','divorciado','viudo','union_libre') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_relation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blood_type` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A+, A-, B+, B-, AB+, AB-, O+, O-',
  `allergies` text COLLATE utf8mb4_unicode_ci,
  `current_medications` text COLLATE utf8mb4_unicode_ci,
  `medical_conditions` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `internal_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Solo staff',
  `preferred_contact_method` enum('phone','email','sms','whatsapp') COLLATE utf8mb4_unicode_ci DEFAULT 'whatsapp',
  `accepts_marketing` tinyint(1) DEFAULT '0',
  `no_show_count` int DEFAULT '0',
  `late_count` int DEFAULT '0',
  `cancellation_count` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by_user_id` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Datos de pacientes';

-- --------------------------------------------------------

--
-- Table structure for table `patient_files`
--

CREATE TABLE `patient_files` (
  `id` int UNSIGNED NOT NULL,
  `patient_id` int UNSIGNED NOT NULL,
  `uploaded_by_user_id` int UNSIGNED NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre en sistema',
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre original',
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int UNSIGNED NOT NULL COMMENT 'Bytes',
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('photo','xray','document','consent','lab_result','other') COLLATE utf8mb4_unicode_ci DEFAULT 'other',
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `appointment_id` int UNSIGNED DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '0' COMMENT 'Visible para paciente',
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by_user_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Archivos de pacientes';

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int UNSIGNED NOT NULL,
  `module` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Módulo (agenda, patients, billing)',
  `resource` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Recurso (appointments, invoices)',
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Acción (create, read, update, delete)',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos del sistema';

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `module`, `resource`, `action`, `description`, `created_at`) VALUES
(1, 'config', 'organization', 'view', 'Ver organización', '2025-12-28 18:04:33'),
(2, 'config', 'organization', 'edit', 'Editar organización', '2025-12-28 18:04:33'),
(3, 'config', 'locations', 'view', 'Ver sedes', '2025-12-28 18:04:33'),
(4, 'config', 'locations', 'create', 'Crear sedes', '2025-12-28 18:04:33'),
(5, 'config', 'locations', 'edit', 'Editar sedes', '2025-12-28 18:04:33'),
(6, 'config', 'locations', 'delete', 'Eliminar sedes', '2025-12-28 18:04:33'),
(7, 'config', 'resources', 'view', 'Ver recursos', '2025-12-28 18:04:33'),
(8, 'config', 'resources', 'create', 'Crear recursos', '2025-12-28 18:04:33'),
(9, 'config', 'resources', 'edit', 'Editar recursos', '2025-12-28 18:04:33'),
(10, 'config', 'resources', 'delete', 'Eliminar recursos', '2025-12-28 18:04:33'),
(11, 'config', 'users', 'view', 'Ver usuarios', '2025-12-28 18:04:33'),
(12, 'config', 'users', 'create', 'Crear usuarios', '2025-12-28 18:04:33'),
(13, 'config', 'users', 'edit', 'Editar usuarios', '2025-12-28 18:04:33'),
(14, 'config', 'users', 'delete', 'Desactivar usuarios', '2025-12-28 18:04:33'),
(15, 'config', 'users', 'assign_roles', 'Asignar roles', '2025-12-28 18:04:33'),
(16, 'config', 'appointment_types', 'view', 'Ver tipos cita', '2025-12-28 18:04:33'),
(17, 'config', 'appointment_types', 'manage', 'Gestionar tipos cita', '2025-12-28 18:04:33'),
(18, 'config', 'schedules', 'view', 'Ver horarios', '2025-12-28 18:04:33'),
(19, 'config', 'schedules', 'manage', 'Gestionar horarios', '2025-12-28 18:04:33'),
(20, 'config', 'holidays', 'manage', 'Gestionar feriados', '2025-12-28 18:04:33'),
(21, 'config', 'sri', 'view', 'Ver config SRI', '2025-12-28 18:04:33'),
(22, 'config', 'sri', 'manage', 'Gestionar SRI', '2025-12-28 18:04:33'),
(23, 'patients', 'patients', 'view', 'Ver pacientes', '2025-12-28 18:04:33'),
(24, 'patients', 'patients', 'create', 'Crear pacientes', '2025-12-28 18:04:33'),
(25, 'patients', 'patients', 'edit', 'Editar pacientes', '2025-12-28 18:04:33'),
(26, 'patients', 'patients', 'delete', 'Desactivar pacientes', '2025-12-28 18:04:33'),
(27, 'patients', 'patients', 'view_history', 'Ver historial', '2025-12-28 18:04:33'),
(28, 'patients', 'files', 'view', 'Ver archivos', '2025-12-28 18:04:33'),
(29, 'patients', 'files', 'upload', 'Subir archivos', '2025-12-28 18:04:33'),
(30, 'patients', 'files', 'delete_own', 'Eliminar propios', '2025-12-28 18:04:33'),
(31, 'patients', 'files', 'delete_all', 'Eliminar todos', '2025-12-28 18:04:33'),
(32, 'agenda', 'appointments', 'view_own', 'Ver citas propias', '2025-12-28 18:04:33'),
(33, 'agenda', 'appointments', 'view_all', 'Ver todas las citas', '2025-12-28 18:04:33'),
(34, 'agenda', 'appointments', 'create', 'Crear citas', '2025-12-28 18:04:33'),
(35, 'agenda', 'appointments', 'edit_own', 'Editar propias', '2025-12-28 18:04:33'),
(36, 'agenda', 'appointments', 'edit_all', 'Editar todas', '2025-12-28 18:04:33'),
(37, 'agenda', 'appointments', 'cancel_own', 'Cancelar propias', '2025-12-28 18:04:33'),
(38, 'agenda', 'appointments', 'cancel_all', 'Cancelar todas', '2025-12-28 18:04:33'),
(39, 'agenda', 'appointments', 'checkin', 'Registrar check-in', '2025-12-28 18:04:33'),
(40, 'agenda', 'appointments', 'start', 'Iniciar atención', '2025-12-28 18:04:33'),
(41, 'agenda', 'appointments', 'finish', 'Finalizar atención', '2025-12-28 18:04:33'),
(42, 'agenda', 'appointments', 'no_show', 'Marcar no-show', '2025-12-28 18:04:33'),
(43, 'agenda', 'availability', 'view', 'Ver disponibilidad', '2025-12-28 18:04:33'),
(44, 'agenda', 'availability', 'manage_own', 'Gestionar propia', '2025-12-28 18:04:33'),
(45, 'agenda', 'availability', 'manage_all', 'Gestionar todas', '2025-12-28 18:04:33'),
(46, 'agenda', 'waiting_list', 'view', 'Ver lista espera', '2025-12-28 18:04:33'),
(47, 'agenda', 'waiting_list', 'manage', 'Gestionar lista', '2025-12-28 18:04:33'),
(48, 'agenda', 'recurring', 'view', 'Ver series', '2025-12-28 18:04:33'),
(49, 'agenda', 'recurring', 'manage', 'Gestionar series', '2025-12-28 18:04:33'),
(50, 'agenda', 'contingencies', 'manage', 'Gestionar contingencias', '2025-12-28 18:04:33'),
(51, 'billing', 'invoices', 'view_own', 'Ver facturas propias', '2025-12-28 18:04:33'),
(52, 'billing', 'invoices', 'view_all', 'Ver todas facturas', '2025-12-28 18:04:33'),
(53, 'billing', 'invoices', 'create', 'Crear facturas', '2025-12-28 18:04:33'),
(54, 'billing', 'invoices', 'void', 'Anular facturas', '2025-12-28 18:04:33'),
(55, 'billing', 'credit_notes', 'create', 'Crear NC', '2025-12-28 18:04:33'),
(56, 'billing', 'sri_monitor', 'view', 'Ver monitor SRI', '2025-12-28 18:04:33'),
(57, 'billing', 'sri_monitor', 'retry', 'Reintentar SRI', '2025-12-28 18:04:33'),
(58, 'billing', 'reports', 'view', 'Ver reportes', '2025-12-28 18:04:33'),
(59, 'notifications', 'logs', 'view', 'Ver logs', '2025-12-28 18:04:33'),
(60, 'notifications', 'logs', 'retry', 'Reenviar', '2025-12-28 18:04:33'),
(61, 'notifications', 'templates', 'view', 'Ver plantillas', '2025-12-28 18:04:33'),
(62, 'notifications', 'templates', 'manage', 'Gestionar plantillas', '2025-12-28 18:04:33'),
(63, 'notifications', 'config', 'manage', 'Config canales', '2025-12-28 18:04:33'),
(64, 'reports', 'dashboard', 'view_own', 'Dashboard propio', '2025-12-28 18:04:33'),
(65, 'reports', 'dashboard', 'view_all', 'Dashboard completo', '2025-12-28 18:04:33'),
(66, 'reports', 'productivity', 'view_own', 'Productividad propia', '2025-12-28 18:04:33'),
(67, 'reports', 'productivity', 'view_all', 'Productividad todos', '2025-12-28 18:04:33'),
(68, 'reports', 'financial', 'view', 'Reportes financieros', '2025-12-28 18:04:33'),
(69, 'reports', 'export', 'excel', 'Exportar Excel', '2025-12-28 18:04:33'),
(70, 'audit', 'logs', 'view', 'Ver auditoría', '2025-12-28 18:04:33'),
(71, 'audit', 'logs', 'export', 'Exportar auditoría', '2025-12-28 18:04:33'),
(72, 'patients', 'files', 'view_all', 'Ver archivos todos', '2025-12-28 18:04:33'),
(73, 'patients', 'files', 'view_own', 'Ver archivos propios', '2025-12-28 18:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `professional_schedules`
--

CREATE TABLE `professional_schedules` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL COMMENT 'Profesional',
  `location_id` int UNSIGNED NOT NULL,
  `day_of_week` tinyint NOT NULL COMMENT '0=Dom, 6=Sáb',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `default_resource_id` int UNSIGNED DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Horarios semanales profesionales';

-- --------------------------------------------------------

--
-- Table structure for table `recurring_series`
--

CREATE TABLE `recurring_series` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `patient_id` int UNSIGNED NOT NULL,
  `appointment_type_id` int UNSIGNED NOT NULL,
  `professional_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `treatment_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `treatment_description` text COLLATE utf8mb4_unicode_ci,
  `interval_days` int NOT NULL DEFAULT '28',
  `preferred_day_of_week` tinyint DEFAULT NULL COMMENT '0=Dom, 6=Sáb',
  `preferred_time` time DEFAULT NULL,
  `total_sessions` int DEFAULT NULL COMMENT 'NULL=indefinido',
  `sessions_completed` int DEFAULT '0',
  `sessions_scheduled` int DEFAULT '0',
  `start_date` date NOT NULL,
  `estimated_end_date` date DEFAULT NULL,
  `actual_end_date` date DEFAULT NULL,
  `status` enum('active','paused','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `pause_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancellation_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by_user_id` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Series de citas recurrentes';

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `resource_type_id` int UNSIGNED NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código interno',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre descriptivo',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `is_available` tinyint(1) DEFAULT '1' COMMENT 'Disponible (false = mantenimiento)',
  `unavailable_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unavailable_until` date DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Recursos físicos por sede';

-- --------------------------------------------------------

--
-- Table structure for table `resource_schedules`
--

CREATE TABLE `resource_schedules` (
  `id` int UNSIGNED NOT NULL,
  `resource_id` int UNSIGNED NOT NULL,
  `day_of_week` tinyint NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Horarios de recursos';

-- --------------------------------------------------------

--
-- Table structure for table `resource_types`
--

CREATE TABLE `resource_types` (
  `id` int UNSIGNED NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único del tipo',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre del icono para UI',
  `color_hex` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#6B7280',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de tipos de recursos';

--
-- Dumping data for table `resource_types`
--

INSERT INTO `resource_types` (`id`, `code`, `name`, `description`, `icon`, `color_hex`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'SILLON', 'Sillón Dental', 'Unidad dental completa', 'chair', '#1E4D3A', 1, 1, '2025-12-28 18:04:33', '2025-12-28 18:04:33'),
(2, 'SALA_RX', 'Sala de Rayos X', 'Sala para radiografías', 'radiation', '#6B7280', 1, 2, '2025-12-28 18:04:33', '2025-12-28 18:04:33'),
(3, 'EQUIPO_LASER', 'Equipo Láser', 'Equipo de láser dental', 'zap', '#F59E0B', 1, 3, '2025-12-28 18:04:33', '2025-12-28 18:04:33'),
(4, 'SALA_CIRUGIA', 'Sala de Cirugía', 'Sala quirúrgica', 'scissors', '#DC2626', 1, 4, '2025-12-28 18:04:33', '2025-12-28 18:04:33'),
(5, 'SALA_ESPERA', 'Sala de Espera', 'Área de espera', 'users', '#A3B7A5', 1, 5, '2025-12-28 18:04:33', '2025-12-28 18:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int UNSIGNED NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre visible',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_system` tinyint(1) DEFAULT '0' COMMENT 'Rol de sistema, no eliminable',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles del sistema';

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `code`, `name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'Super Administrador', 'Acceso total al sistema', 1, '2025-12-28 18:04:33', '2025-12-28 18:04:33'),
(2, 'admin', 'Administrador', 'Administrador de clínica', 1, '2025-12-28 18:04:33', '2025-12-28 18:04:33'),
(3, 'odontologo', 'Odontólogo', 'Profesional de salud dental', 1, '2025-12-28 18:04:33', '2025-12-28 18:04:33'),
(4, 'recepcion', 'Recepción', 'Personal de recepción', 1, '2025-12-28 18:04:33', '2025-12-28 18:04:33'),
(5, 'asistente', 'Asistente Dental', 'Asistente de odontología', 1, '2025-12-28 18:04:33', '2025-12-28 18:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int UNSIGNED NOT NULL,
  `role_id` int UNSIGNED NOT NULL,
  `permission_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Asignación de permisos a roles';

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 32, '2025-12-28 18:04:33'),
(2, 1, 33, '2025-12-28 18:04:33'),
(3, 1, 34, '2025-12-28 18:04:33'),
(4, 1, 35, '2025-12-28 18:04:33'),
(5, 1, 36, '2025-12-28 18:04:33'),
(6, 1, 37, '2025-12-28 18:04:33'),
(7, 1, 38, '2025-12-28 18:04:33'),
(8, 1, 39, '2025-12-28 18:04:33'),
(9, 1, 40, '2025-12-28 18:04:33'),
(10, 1, 41, '2025-12-28 18:04:33'),
(11, 1, 42, '2025-12-28 18:04:33'),
(12, 1, 43, '2025-12-28 18:04:33'),
(13, 1, 44, '2025-12-28 18:04:33'),
(14, 1, 45, '2025-12-28 18:04:33'),
(15, 1, 46, '2025-12-28 18:04:33'),
(16, 1, 47, '2025-12-28 18:04:33'),
(17, 1, 48, '2025-12-28 18:04:33'),
(18, 1, 49, '2025-12-28 18:04:33'),
(19, 1, 50, '2025-12-28 18:04:33'),
(20, 1, 70, '2025-12-28 18:04:33'),
(21, 1, 71, '2025-12-28 18:04:33'),
(22, 1, 51, '2025-12-28 18:04:33'),
(23, 1, 52, '2025-12-28 18:04:33'),
(24, 1, 53, '2025-12-28 18:04:33'),
(25, 1, 54, '2025-12-28 18:04:33'),
(26, 1, 55, '2025-12-28 18:04:33'),
(27, 1, 56, '2025-12-28 18:04:33'),
(28, 1, 57, '2025-12-28 18:04:33'),
(29, 1, 58, '2025-12-28 18:04:33'),
(30, 1, 1, '2025-12-28 18:04:33'),
(31, 1, 2, '2025-12-28 18:04:33'),
(32, 1, 3, '2025-12-28 18:04:33'),
(33, 1, 4, '2025-12-28 18:04:33'),
(34, 1, 5, '2025-12-28 18:04:33'),
(35, 1, 6, '2025-12-28 18:04:33'),
(36, 1, 7, '2025-12-28 18:04:33'),
(37, 1, 8, '2025-12-28 18:04:33'),
(38, 1, 9, '2025-12-28 18:04:33'),
(39, 1, 10, '2025-12-28 18:04:33'),
(40, 1, 11, '2025-12-28 18:04:33'),
(41, 1, 12, '2025-12-28 18:04:33'),
(42, 1, 13, '2025-12-28 18:04:33'),
(43, 1, 14, '2025-12-28 18:04:33'),
(44, 1, 15, '2025-12-28 18:04:33'),
(45, 1, 16, '2025-12-28 18:04:33'),
(46, 1, 17, '2025-12-28 18:04:33'),
(47, 1, 18, '2025-12-28 18:04:33'),
(48, 1, 19, '2025-12-28 18:04:33'),
(49, 1, 20, '2025-12-28 18:04:33'),
(50, 1, 21, '2025-12-28 18:04:33'),
(51, 1, 22, '2025-12-28 18:04:33'),
(52, 1, 59, '2025-12-28 18:04:33'),
(53, 1, 60, '2025-12-28 18:04:33'),
(54, 1, 61, '2025-12-28 18:04:33'),
(55, 1, 62, '2025-12-28 18:04:33'),
(56, 1, 63, '2025-12-28 18:04:33'),
(57, 1, 23, '2025-12-28 18:04:33'),
(58, 1, 24, '2025-12-28 18:04:33'),
(59, 1, 25, '2025-12-28 18:04:33'),
(60, 1, 26, '2025-12-28 18:04:33'),
(61, 1, 27, '2025-12-28 18:04:33'),
(62, 1, 28, '2025-12-28 18:04:33'),
(63, 1, 29, '2025-12-28 18:04:33'),
(64, 1, 30, '2025-12-28 18:04:33'),
(65, 1, 31, '2025-12-28 18:04:33'),
(66, 1, 64, '2025-12-28 18:04:33'),
(67, 1, 65, '2025-12-28 18:04:33'),
(68, 1, 66, '2025-12-28 18:04:33'),
(69, 1, 67, '2025-12-28 18:04:33'),
(70, 1, 68, '2025-12-28 18:04:33'),
(71, 1, 69, '2025-12-28 18:04:33'),
(128, 2, 38, '2025-12-28 18:04:33'),
(129, 2, 37, '2025-12-28 18:04:33'),
(130, 2, 39, '2025-12-28 18:04:33'),
(131, 2, 34, '2025-12-28 18:04:33'),
(132, 2, 36, '2025-12-28 18:04:33'),
(133, 2, 35, '2025-12-28 18:04:33'),
(134, 2, 41, '2025-12-28 18:04:33'),
(135, 2, 42, '2025-12-28 18:04:33'),
(136, 2, 40, '2025-12-28 18:04:33'),
(137, 2, 33, '2025-12-28 18:04:33'),
(138, 2, 32, '2025-12-28 18:04:33'),
(139, 2, 45, '2025-12-28 18:04:33'),
(140, 2, 44, '2025-12-28 18:04:33'),
(141, 2, 43, '2025-12-28 18:04:33'),
(142, 2, 50, '2025-12-28 18:04:33'),
(143, 2, 49, '2025-12-28 18:04:33'),
(144, 2, 48, '2025-12-28 18:04:33'),
(145, 2, 47, '2025-12-28 18:04:33'),
(146, 2, 46, '2025-12-28 18:04:33'),
(147, 2, 70, '2025-12-28 18:04:33'),
(148, 2, 55, '2025-12-28 18:04:33'),
(149, 2, 53, '2025-12-28 18:04:33'),
(150, 2, 52, '2025-12-28 18:04:33'),
(151, 2, 51, '2025-12-28 18:04:33'),
(152, 2, 54, '2025-12-28 18:04:33'),
(153, 2, 58, '2025-12-28 18:04:33'),
(154, 2, 57, '2025-12-28 18:04:33'),
(155, 2, 56, '2025-12-28 18:04:33'),
(156, 2, 17, '2025-12-28 18:04:33'),
(157, 2, 16, '2025-12-28 18:04:33'),
(158, 2, 20, '2025-12-28 18:04:33'),
(159, 2, 4, '2025-12-28 18:04:33'),
(160, 2, 6, '2025-12-28 18:04:33'),
(161, 2, 5, '2025-12-28 18:04:33'),
(162, 2, 3, '2025-12-28 18:04:33'),
(163, 2, 2, '2025-12-28 18:04:33'),
(164, 2, 1, '2025-12-28 18:04:33'),
(165, 2, 8, '2025-12-28 18:04:33'),
(166, 2, 10, '2025-12-28 18:04:33'),
(167, 2, 9, '2025-12-28 18:04:33'),
(168, 2, 7, '2025-12-28 18:04:33'),
(169, 2, 19, '2025-12-28 18:04:33'),
(170, 2, 18, '2025-12-28 18:04:33'),
(171, 2, 22, '2025-12-28 18:04:33'),
(172, 2, 21, '2025-12-28 18:04:33'),
(173, 2, 15, '2025-12-28 18:04:33'),
(174, 2, 12, '2025-12-28 18:04:33'),
(175, 2, 14, '2025-12-28 18:04:33'),
(176, 2, 13, '2025-12-28 18:04:33'),
(177, 2, 11, '2025-12-28 18:04:33'),
(178, 2, 63, '2025-12-28 18:04:33'),
(179, 2, 60, '2025-12-28 18:04:33'),
(180, 2, 59, '2025-12-28 18:04:33'),
(181, 2, 62, '2025-12-28 18:04:33'),
(182, 2, 61, '2025-12-28 18:04:33'),
(183, 2, 31, '2025-12-28 18:04:33'),
(184, 2, 30, '2025-12-28 18:04:33'),
(185, 2, 29, '2025-12-28 18:04:33'),
(186, 2, 28, '2025-12-28 18:04:33'),
(187, 2, 24, '2025-12-28 18:04:33'),
(188, 2, 26, '2025-12-28 18:04:33'),
(189, 2, 25, '2025-12-28 18:04:33'),
(190, 2, 23, '2025-12-28 18:04:33'),
(191, 2, 27, '2025-12-28 18:04:33'),
(192, 2, 65, '2025-12-28 18:04:33'),
(193, 2, 64, '2025-12-28 18:04:33'),
(194, 2, 69, '2025-12-28 18:04:33'),
(195, 2, 68, '2025-12-28 18:04:33'),
(196, 2, 67, '2025-12-28 18:04:33'),
(197, 2, 66, '2025-12-28 18:04:33'),
(255, 3, 37, '2025-12-28 18:04:33'),
(256, 3, 39, '2025-12-28 18:04:33'),
(257, 3, 34, '2025-12-28 18:04:33'),
(258, 3, 35, '2025-12-28 18:04:33'),
(259, 3, 41, '2025-12-28 18:04:33'),
(260, 3, 42, '2025-12-28 18:04:33'),
(261, 3, 40, '2025-12-28 18:04:33'),
(262, 3, 32, '2025-12-28 18:04:33'),
(263, 3, 44, '2025-12-28 18:04:33'),
(264, 3, 43, '2025-12-28 18:04:33'),
(265, 3, 48, '2025-12-28 18:04:33'),
(266, 3, 46, '2025-12-28 18:04:33'),
(267, 3, 55, '2025-12-28 18:04:33'),
(268, 3, 53, '2025-12-28 18:04:33'),
(269, 3, 51, '2025-12-28 18:04:33'),
(270, 3, 16, '2025-12-28 18:04:33'),
(271, 3, 3, '2025-12-28 18:04:33'),
(272, 3, 7, '2025-12-28 18:04:33'),
(273, 3, 30, '2025-12-28 18:04:33'),
(274, 3, 29, '2025-12-28 18:04:33'),
(275, 3, 28, '2025-12-28 18:04:33'),
(276, 3, 24, '2025-12-28 18:04:33'),
(277, 3, 25, '2025-12-28 18:04:33'),
(278, 3, 23, '2025-12-28 18:04:33'),
(279, 3, 27, '2025-12-28 18:04:33'),
(280, 3, 64, '2025-12-28 18:04:33'),
(281, 3, 66, '2025-12-28 18:04:33'),
(286, 4, 38, '2025-12-28 18:04:33'),
(287, 4, 39, '2025-12-28 18:04:33'),
(288, 4, 34, '2025-12-28 18:04:33'),
(289, 4, 36, '2025-12-28 18:04:33'),
(290, 4, 42, '2025-12-28 18:04:33'),
(291, 4, 33, '2025-12-28 18:04:33'),
(292, 4, 43, '2025-12-28 18:04:33'),
(293, 4, 47, '2025-12-28 18:04:33'),
(294, 4, 46, '2025-12-28 18:04:33'),
(295, 4, 55, '2025-12-28 18:04:33'),
(296, 4, 53, '2025-12-28 18:04:33'),
(297, 4, 52, '2025-12-28 18:04:33'),
(298, 4, 16, '2025-12-28 18:04:33'),
(299, 4, 3, '2025-12-28 18:04:33'),
(300, 4, 7, '2025-12-28 18:04:33'),
(301, 4, 18, '2025-12-28 18:04:33'),
(302, 4, 60, '2025-12-28 18:04:33'),
(303, 4, 59, '2025-12-28 18:04:33'),
(304, 4, 29, '2025-12-28 18:04:33'),
(305, 4, 28, '2025-12-28 18:04:33'),
(306, 4, 24, '2025-12-28 18:04:33'),
(307, 4, 25, '2025-12-28 18:04:33'),
(308, 4, 23, '2025-12-28 18:04:33'),
(309, 4, 65, '2025-12-28 18:04:33'),
(310, 4, 67, '2025-12-28 18:04:33'),
(317, 5, 39, '2025-12-28 18:04:33'),
(318, 5, 33, '2025-12-28 18:04:33'),
(319, 5, 43, '2025-12-28 18:04:33'),
(320, 5, 46, '2025-12-28 18:04:33'),
(321, 5, 3, '2025-12-28 18:04:33'),
(322, 5, 7, '2025-12-28 18:04:33'),
(323, 5, 29, '2025-12-28 18:04:33'),
(324, 5, 28, '2025-12-28 18:04:33'),
(325, 5, 23, '2025-12-28 18:04:33'),
(326, 1, 72, '2025-12-28 18:04:33'),
(327, 1, 73, '2025-12-28 18:04:33'),
(328, 2, 72, '2025-12-28 18:04:33'),
(329, 2, 73, '2025-12-28 18:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_exceptions`
--

CREATE TABLE `schedule_exceptions` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED DEFAULT NULL,
  `exception_date` date NOT NULL,
  `exception_type` enum('block','extend','modify') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_all_day` tinyint(1) DEFAULT '0',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by_user_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Excepciones de horario';

-- --------------------------------------------------------

--
-- Table structure for table `sri_configurations`
--

CREATE TABLE `sri_configurations` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `environment` enum('1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '1=Pruebas, 2=Producción',
  `certificate_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificate_password_encrypted` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificate_subject` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificate_issuer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificate_valid_from` date DEFAULT NULL,
  `certificate_valid_until` date DEFAULT NULL,
  `forced_accounting` tinyint(1) DEFAULT '0',
  `special_taxpayer_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `withholding_agent_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rimpe_taxpayer` tinyint(1) DEFAULT '0',
  `contingency_mode` tinyint(1) DEFAULT '0',
  `contingency_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contingency_started_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuración SRI';

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('string','integer','float','boolean','json') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by_user_id` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuraciones del sistema';

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `role_id` int UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_professional` tinyint(1) DEFAULT '0' COMMENT 'Es profesional de salud',
  `professional_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dr., Dra., Lic.',
  `professional_registration` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número registro profesional',
  `specialty` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Firma digital',
  `is_active` tinyint(1) DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` int DEFAULT '0',
  `locked_until` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferences` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuarios del sistema';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `organization_id`, `role_id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `avatar_path`, `is_professional`, `professional_title`, `professional_registration`, `specialty`, `signature_path`, `is_active`, `email_verified_at`, `failed_login_attempts`, `locked_until`, `last_login_at`, `last_login_ip`, `preferences`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'admin@shalomdental.com', '$2y$12$/3PLLVLXppmvl4myDYv6Uurvg7h1L./Z5CAvdjH3PBdzZ1CqS2Utm', 'Super', 'Admin', NULL, NULL, 0, NULL, NULL, NULL, NULL, 1, NULL, 0, NULL, '2026-01-18 15:17:18', '::1', NULL, '2025-12-29 08:00:51', '2026-01-18 15:17:18');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED DEFAULT NULL COMMENT 'Sede activa',
  `session_id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Token remember me',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sesiones activas';

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_appointments_today`
-- (See below for the actual view)
--
CREATE TABLE `v_appointments_today` (
`appointment_color` varchar(7)
,`appointment_type_id` int unsigned
,`appointment_type_name` varchar(100)
,`checked_in_at` timestamp
,`finished_at` timestamp
,`id` int unsigned
,`is_emergency` tinyint(1)
,`location_id` int unsigned
,`location_name` varchar(100)
,`patient_id` int unsigned
,`patient_name` varchar(201)
,`patient_phone` varchar(20)
,`professional_id` int unsigned
,`professional_name` varchar(302)
,`scheduled_date` date
,`scheduled_end_time` time
,`scheduled_start_time` time
,`started_at` timestamp
,`status` enum('scheduled','confirmed','checked_in','in_progress','completed','cancelled','no_show','rescheduled','late')
,`wait_time_minutes` bigint
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_monthly_billing`
-- (See below for the actual view)
--
CREATE TABLE `v_monthly_billing` (
`authorized_count` decimal(23,0)
,`location_id` int unsigned
,`location_name` varchar(100)
,`month_year` varchar(7)
,`organization_id` int unsigned
,`rejected_count` decimal(23,0)
,`total_amount` decimal(36,2)
,`total_invoices` bigint
,`total_subtotal` decimal(36,2)
,`total_tax` decimal(36,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_professional_availability`
-- (See below for the actual view)
--
CREATE TABLE `v_professional_availability` (
`day_name` varchar(9)
,`day_of_week` tinyint
,`end_time` time
,`location_id` int unsigned
,`location_name` varchar(100)
,`professional_name` varchar(302)
,`resource_name` varchar(100)
,`start_time` time
,`user_id` int unsigned
);

-- --------------------------------------------------------

--
-- Table structure for table `waiting_list`
--

CREATE TABLE `waiting_list` (
  `id` int UNSIGNED NOT NULL,
  `organization_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `patient_id` int UNSIGNED NOT NULL,
  `appointment_type_id` int UNSIGNED DEFAULT NULL,
  `preferred_professional_id` int UNSIGNED DEFAULT NULL,
  `date_from` date NOT NULL,
  `date_until` date DEFAULT NULL,
  `preferred_days_of_week` json DEFAULT NULL COMMENT '[1,3,5] = Lun,Mié,Vie',
  `preferred_time_slot` enum('morning','afternoon','any') COLLATE utf8mb4_unicode_ci DEFAULT 'any',
  `preferred_time_from` time DEFAULT NULL,
  `preferred_time_until` time DEFAULT NULL,
  `priority` enum('normal','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `status` enum('waiting','contacted','scheduled','expired','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'waiting',
  `contacted_at` timestamp NULL DEFAULT NULL,
  `contacted_via` enum('email','sms','whatsapp','phone') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_deadline` timestamp NULL DEFAULT NULL,
  `scheduled_appointment_id` int UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by_user_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lista de espera';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_appointments_date_location` (`scheduled_date`,`location_id`),
  ADD KEY `idx_appointments_professional_date` (`professional_id`,`scheduled_date`),
  ADD KEY `idx_appointments_patient` (`patient_id`),
  ADD KEY `idx_appointments_status` (`status`),
  ADD KEY `idx_appointments_series` (`recurring_series_id`),
  ADD KEY `idx_appointments_pending` (`scheduled_date`,`status`,`location_id`),
  ADD KEY `fk_appointments_organization` (`organization_id`),
  ADD KEY `fk_appointments_location` (`location_id`),
  ADD KEY `fk_appointments_type` (`appointment_type_id`),
  ADD KEY `fk_appointments_created_by` (`created_by_user_id`),
  ADD KEY `fk_appointments_cancelled_by` (`cancelled_by_user_id`);

--
-- Indexes for table `appointment_resources`
--
ALTER TABLE `appointment_resources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_appointment_resources_unique` (`appointment_id`,`resource_id`),
  ADD KEY `idx_appointment_resources_resource` (`resource_id`);

--
-- Indexes for table `appointment_types`
--
ALTER TABLE `appointment_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_appointment_types_code` (`organization_id`,`code`),
  ADD KEY `idx_appointment_types_active` (`organization_id`,`is_active`);

--
-- Indexes for table `appointment_type_resources`
--
ALTER TABLE `appointment_type_resources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_atr_unique` (`appointment_type_id`,`resource_type_id`),
  ADD KEY `fk_atr_resource_type` (`resource_type_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_created` (`created_at`),
  ADD KEY `idx_audit_organization` (`organization_id`,`created_at`);

--
-- Indexes for table `contingencies`
--
ALTER TABLE `contingencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contingencies_status` (`status`,`start_datetime`),
  ADD KEY `idx_contingencies_professional` (`affected_professional_id`),
  ADD KEY `fk_contingencies_organization` (`organization_id`),
  ADD KEY `fk_contingencies_location` (`location_id`),
  ADD KEY `fk_contingencies_resource` (`affected_resource_id`),
  ADD KEY `fk_contingencies_created_by` (`created_by_user_id`),
  ADD KEY `fk_contingencies_resolved_by` (`resolved_by_user_id`);

--
-- Indexes for table `emission_points`
--
ALTER TABLE `emission_points`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_emission_points_code` (`location_id`,`code`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_holidays_unique` (`organization_id`,`location_id`,`holiday_date`),
  ADD KEY `idx_holidays_date` (`holiday_date`),
  ADD KEY `fk_holidays_location` (`location_id`),
  ADD KEY `fk_holidays_created_by` (`created_by_user_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_invoices_sequential` (`establishment_code`,`emission_point_code`,`sequential`,`document_type`),
  ADD UNIQUE KEY `idx_invoices_access_key` (`access_key`),
  ADD KEY `idx_invoices_patient` (`patient_id`),
  ADD KEY `idx_invoices_status` (`status`),
  ADD KEY `idx_invoices_date` (`issue_date`),
  ADD KEY `idx_invoices_pending` (`status`,`created_at`),
  ADD KEY `fk_invoices_organization` (`organization_id`),
  ADD KEY `fk_invoices_location` (`location_id`),
  ADD KEY `fk_invoices_emission_point` (`emission_point_id`),
  ADD KEY `fk_invoices_appointment` (`appointment_id`),
  ADD KEY `fk_invoices_created_by` (`created_by_user_id`),
  ADD KEY `fk_invoices_voided_by` (`voided_by_user_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_items_invoice` (`invoice_id`),
  ADD KEY `fk_items_type` (`appointment_type_id`);

--
-- Indexes for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_payments_invoice` (`invoice_id`);

--
-- Indexes for table `credit_notes`
--
ALTER TABLE `credit_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_credit_notes_invoice` (`invoice_id`),
  ADD KEY `idx_credit_notes_location` (`location_id`),
  ADD KEY `idx_credit_notes_organization` (`organization_id`),
  ADD KEY `idx_credit_notes_created_by` (`created_by_user_id`);

--
-- Indexes for table `invoice_sequentials`
--
ALTER TABLE `invoice_sequentials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_sequentials_unique` (`emission_point_id`,`document_type`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_locations_code` (`organization_id`,`code`),
  ADD UNIQUE KEY `idx_locations_sri` (`organization_id`,`sri_establishment_code`),
  ADD KEY `idx_locations_active` (`is_active`);

--
-- Indexes for table `location_users`
--
ALTER TABLE `location_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_location_users_unique` (`location_id`,`user_id`),
  ADD KEY `idx_location_users_user` (`user_id`);

--
-- Indexes for table `mass_reschedule_logs`
--
ALTER TABLE `mass_reschedule_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mass_reschedule_date` (`executed_at`),
  ADD KEY `fk_reschedule_contingency` (`contingency_id`),
  ADD KEY `fk_reschedule_organization` (`organization_id`),
  ADD KEY `fk_reschedule_executed_by` (`executed_by_user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_pending` (`status`,`next_retry_at`),
  ADD KEY `idx_notifications_patient` (`patient_id`),
  ADD KEY `idx_notifications_appointment` (`appointment_id`),
  ADD KEY `idx_notifications_created` (`created_at`),
  ADD KEY `fk_notifications_organization` (`organization_id`),
  ADD KEY `fk_notifications_template` (`template_id`),
  ADD KEY `fk_notifications_invoice` (`invoice_id`);

--
-- Indexes for table `notification_configs`
--
ALTER TABLE `notification_configs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_notification_configs` (`organization_id`,`channel`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_templates_code` (`organization_id`,`code`),
  ADD KEY `idx_templates_event` (`organization_id`,`event_type`,`channel`,`is_active`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_organizations_ruc` (`ruc`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_patients_id_number` (`organization_id`,`id_type`,`id_number`),
  ADD KEY `idx_patients_name` (`organization_id`,`last_name`,`first_name`),
  ADD KEY `idx_patients_phone` (`phone`),
  ADD KEY `idx_patients_email` (`email`),
  ADD KEY `idx_patients_active` (`organization_id`,`is_active`),
  ADD KEY `fk_patients_created_by` (`created_by_user_id`);
ALTER TABLE `patients` ADD FULLTEXT KEY `idx_patients_search` (`first_name`,`last_name`,`id_number`,`phone`,`email`);

--
-- Indexes for table `patient_files`
--
ALTER TABLE `patient_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_files_patient` (`patient_id`),
  ADD KEY `idx_patient_files_category` (`patient_id`,`category`),
  ADD KEY `idx_patient_files_deleted` (`is_deleted`),
  ADD KEY `fk_patient_files_uploaded_by` (`uploaded_by_user_id`),
  ADD KEY `fk_patient_files_deleted_by` (`deleted_by_user_id`),
  ADD KEY `fk_patient_files_appointment` (`appointment_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_permissions_unique` (`module`,`resource`,`action`),
  ADD KEY `idx_permissions_module` (`module`);

--
-- Indexes for table `professional_schedules`
--
ALTER TABLE `professional_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedules_user_day` (`user_id`,`day_of_week`,`is_active`),
  ADD KEY `idx_schedules_location` (`location_id`),
  ADD KEY `fk_schedules_resource` (`default_resource_id`);

--
-- Indexes for table `recurring_series`
--
ALTER TABLE `recurring_series`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recurring_series_patient` (`patient_id`),
  ADD KEY `idx_recurring_series_status` (`status`),
  ADD KEY `idx_recurring_series_professional` (`professional_id`),
  ADD KEY `fk_series_organization` (`organization_id`),
  ADD KEY `fk_series_type` (`appointment_type_id`),
  ADD KEY `fk_series_location` (`location_id`),
  ADD KEY `fk_series_created_by` (`created_by_user_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_resources_code` (`location_id`,`code`),
  ADD KEY `idx_resources_type` (`resource_type_id`),
  ADD KEY `idx_resources_available` (`is_active`,`is_available`);

--
-- Indexes for table `resource_schedules`
--
ALTER TABLE `resource_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resource_schedules` (`resource_id`,`day_of_week`);

--
-- Indexes for table `resource_types`
--
ALTER TABLE `resource_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_resource_types_code` (`code`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_roles_code` (`code`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_role_permissions_unique` (`role_id`,`permission_id`),
  ADD KEY `fk_role_permissions_permission` (`permission_id`);

--
-- Indexes for table `schedule_exceptions`
--
ALTER TABLE `schedule_exceptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exceptions_user_date` (`user_id`,`exception_date`),
  ADD KEY `idx_exceptions_date` (`exception_date`),
  ADD KEY `fk_exceptions_location` (`location_id`),
  ADD KEY `fk_exceptions_resource` (`resource_id`),
  ADD KEY `fk_exceptions_created_by` (`created_by_user_id`);

--
-- Indexes for table `sri_configurations`
--
ALTER TABLE `sri_configurations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_sri_config_org` (`organization_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_settings_key` (`organization_id`,`setting_key`),
  ADD KEY `idx_settings_category` (`organization_id`,`category`),
  ADD KEY `fk_settings_updated_by` (`updated_by_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role_id`),
  ADD KEY `idx_users_professional` (`is_professional`),
  ADD KEY `idx_users_active` (`is_active`),
  ADD KEY `fk_users_organization` (`organization_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_sessions_session_id` (`session_id`),
  ADD KEY `idx_sessions_user` (`user_id`),
  ADD KEY `idx_sessions_expires` (`expires_at`),
  ADD KEY `fk_sessions_location` (`location_id`);

--
-- Indexes for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_waiting_list_location` (`location_id`,`status`),
  ADD KEY `idx_waiting_list_priority` (`priority`,`created_at`),
  ADD KEY `idx_waiting_list_patient` (`patient_id`),
  ADD KEY `fk_waitlist_organization` (`organization_id`),
  ADD KEY `fk_waitlist_type` (`appointment_type_id`),
  ADD KEY `fk_waitlist_professional` (`preferred_professional_id`),
  ADD KEY `fk_waitlist_appointment` (`scheduled_appointment_id`),
  ADD KEY `fk_waitlist_created_by` (`created_by_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointment_resources`
--
ALTER TABLE `appointment_resources`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointment_types`
--
ALTER TABLE `appointment_types`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointment_type_resources`
--
ALTER TABLE `appointment_type_resources`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contingencies`
--
ALTER TABLE `contingencies`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emission_points`
--
ALTER TABLE `emission_points`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credit_notes`
--
ALTER TABLE `credit_notes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_sequentials`
--
ALTER TABLE `invoice_sequentials`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `location_users`
--
ALTER TABLE `location_users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mass_reschedule_logs`
--
ALTER TABLE `mass_reschedule_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_configs`
--
ALTER TABLE `notification_configs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_files`
--
ALTER TABLE `patient_files`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `professional_schedules`
--
ALTER TABLE `professional_schedules`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recurring_series`
--
ALTER TABLE `recurring_series`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resource_schedules`
--
ALTER TABLE `resource_schedules`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resource_types`
--
ALTER TABLE `resource_types`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=326;

--
-- AUTO_INCREMENT for table `schedule_exceptions`
--
ALTER TABLE `schedule_exceptions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sri_configurations`
--
ALTER TABLE `sri_configurations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `waiting_list`
--
ALTER TABLE `waiting_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `v_appointments_today`
--
DROP TABLE IF EXISTS `v_appointments_today`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_appointments_today`  AS SELECT `a`.`id` AS `id`, `a`.`scheduled_date` AS `scheduled_date`, `a`.`scheduled_start_time` AS `scheduled_start_time`, `a`.`scheduled_end_time` AS `scheduled_end_time`, `a`.`status` AS `status`, `a`.`is_emergency` AS `is_emergency`, `a`.`checked_in_at` AS `checked_in_at`, `a`.`started_at` AS `started_at`, `a`.`finished_at` AS `finished_at`, `a`.`patient_id` AS `patient_id`, concat(`p`.`first_name`,' ',`p`.`last_name`) AS `patient_name`, `p`.`phone` AS `patient_phone`, `a`.`professional_id` AS `professional_id`, concat(`u`.`professional_title`,' ',`u`.`first_name`,' ',`u`.`last_name`) AS `professional_name`, `a`.`appointment_type_id` AS `appointment_type_id`, `at`.`name` AS `appointment_type_name`, `at`.`color_hex` AS `appointment_color`, `a`.`location_id` AS `location_id`, `l`.`name` AS `location_name`, (case when ((`a`.`checked_in_at` is not null) and (`a`.`started_at` is null)) then timestampdiff(MINUTE,`a`.`checked_in_at`,now()) else NULL end) AS `wait_time_minutes` FROM ((((`appointments` `a` join `patients` `p` on((`a`.`patient_id` = `p`.`id`))) join `users` `u` on((`a`.`professional_id` = `u`.`id`))) join `appointment_types` `at` on((`a`.`appointment_type_id` = `at`.`id`))) join `locations` `l` on((`a`.`location_id` = `l`.`id`))) WHERE (`a`.`scheduled_date` = curdate()) ORDER BY `a`.`scheduled_start_time` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_monthly_billing`
--
DROP TABLE IF EXISTS `v_monthly_billing`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_monthly_billing`  AS SELECT `i`.`organization_id` AS `organization_id`, `i`.`location_id` AS `location_id`, `l`.`name` AS `location_name`, date_format(`i`.`issue_date`,'%Y-%m') AS `month_year`, count(0) AS `total_invoices`, sum((case when (`i`.`status` = 'authorized') then 1 else 0 end)) AS `authorized_count`, sum((case when (`i`.`status` = 'rejected') then 1 else 0 end)) AS `rejected_count`, sum((case when (`i`.`status` = 'authorized') then `i`.`subtotal` else 0 end)) AS `total_subtotal`, sum((case when (`i`.`status` = 'authorized') then `i`.`total_tax` else 0 end)) AS `total_tax`, sum((case when (`i`.`status` = 'authorized') then `i`.`total` else 0 end)) AS `total_amount` FROM (`invoices` `i` join `locations` `l` on((`i`.`location_id` = `l`.`id`))) WHERE (`i`.`document_type` = '01') GROUP BY `i`.`organization_id`, `i`.`location_id`, `l`.`name`, date_format(`i`.`issue_date`,'%Y-%m') ;

-- --------------------------------------------------------

--
-- Structure for view `v_professional_availability`
--
DROP TABLE IF EXISTS `v_professional_availability`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_professional_availability`  AS SELECT `ps`.`user_id` AS `user_id`, concat(`u`.`professional_title`,' ',`u`.`first_name`,' ',`u`.`last_name`) AS `professional_name`, `ps`.`location_id` AS `location_id`, `l`.`name` AS `location_name`, `ps`.`day_of_week` AS `day_of_week`, (case `ps`.`day_of_week` when 0 then 'Domingo' when 1 then 'Lunes' when 2 then 'Martes' when 3 then 'Miércoles' when 4 then 'Jueves' when 5 then 'Viernes' when 6 then 'Sábado' end) AS `day_name`, `ps`.`start_time` AS `start_time`, `ps`.`end_time` AS `end_time`, `r`.`name` AS `resource_name` FROM (((`professional_schedules` `ps` join `users` `u` on((`ps`.`user_id` = `u`.`id`))) join `locations` `l` on((`ps`.`location_id` = `l`.`id`))) left join `resources` `r` on((`ps`.`default_resource_id` = `r`.`id`))) WHERE ((`ps`.`is_active` = true) AND (`u`.`is_active` = true) AND (`u`.`is_professional` = true)) ORDER BY `ps`.`user_id` ASC, `ps`.`day_of_week` ASC ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_cancelled_by` FOREIGN KEY (`cancelled_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_professional` FOREIGN KEY (`professional_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_series` FOREIGN KEY (`recurring_series_id`) REFERENCES `recurring_series` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_type` FOREIGN KEY (`appointment_type_id`) REFERENCES `appointment_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `appointment_resources`
--
ALTER TABLE `appointment_resources`
  ADD CONSTRAINT `fk_appt_resources_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appt_resources_resource` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `appointment_types`
--
ALTER TABLE `appointment_types`
  ADD CONSTRAINT `fk_appointment_types_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `appointment_type_resources`
--
ALTER TABLE `appointment_type_resources`
  ADD CONSTRAINT `fk_atr_resource_type` FOREIGN KEY (`resource_type_id`) REFERENCES `resource_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_atr_type` FOREIGN KEY (`appointment_type_id`) REFERENCES `appointment_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `contingencies`
--
ALTER TABLE `contingencies`
  ADD CONSTRAINT `fk_contingencies_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contingencies_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contingencies_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contingencies_professional` FOREIGN KEY (`affected_professional_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contingencies_resolved_by` FOREIGN KEY (`resolved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contingencies_resource` FOREIGN KEY (`affected_resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `emission_points`
--
ALTER TABLE `emission_points`
  ADD CONSTRAINT `fk_emission_points_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `holidays`
--
ALTER TABLE `holidays`
  ADD CONSTRAINT `fk_holidays_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_holidays_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_holidays_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoices_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoices_emission_point` FOREIGN KEY (`emission_point_id`) REFERENCES `emission_points` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoices_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoices_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoices_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoices_voided_by` FOREIGN KEY (`voided_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `fk_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_items_type` FOREIGN KEY (`appointment_type_id`) REFERENCES `appointment_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `credit_notes`
--
ALTER TABLE `credit_notes`
  ADD CONSTRAINT `fk_credit_notes_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_credit_notes_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_credit_notes_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_credit_notes_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `invoice_sequentials`
--
ALTER TABLE `invoice_sequentials`
  ADD CONSTRAINT `fk_sequentials_emission_point` FOREIGN KEY (`emission_point_id`) REFERENCES `emission_points` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `fk_locations_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `location_users`
--
ALTER TABLE `location_users`
  ADD CONSTRAINT `fk_location_users_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_location_users_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mass_reschedule_logs`
--
ALTER TABLE `mass_reschedule_logs`
  ADD CONSTRAINT `fk_reschedule_contingency` FOREIGN KEY (`contingency_id`) REFERENCES `contingencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reschedule_executed_by` FOREIGN KEY (`executed_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reschedule_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_template` FOREIGN KEY (`template_id`) REFERENCES `notification_templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `notification_configs`
--
ALTER TABLE `notification_configs`
  ADD CONSTRAINT `fk_notif_configs_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD CONSTRAINT `fk_templates_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patients_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_patients_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `patient_files`
--
ALTER TABLE `patient_files`
  ADD CONSTRAINT `fk_patient_files_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_patient_files_deleted_by` FOREIGN KEY (`deleted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_patient_files_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_patient_files_uploaded_by` FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `professional_schedules`
--
ALTER TABLE `professional_schedules`
  ADD CONSTRAINT `fk_schedules_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedules_resource` FOREIGN KEY (`default_resource_id`) REFERENCES `resources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedules_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recurring_series`
--
ALTER TABLE `recurring_series`
  ADD CONSTRAINT `fk_series_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_series_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_series_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_series_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_series_professional` FOREIGN KEY (`professional_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_series_type` FOREIGN KEY (`appointment_type_id`) REFERENCES `appointment_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `fk_resources_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_resources_type` FOREIGN KEY (`resource_type_id`) REFERENCES `resource_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `resource_schedules`
--
ALTER TABLE `resource_schedules`
  ADD CONSTRAINT `fk_resource_schedules_resource` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedule_exceptions`
--
ALTER TABLE `schedule_exceptions`
  ADD CONSTRAINT `fk_exceptions_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_exceptions_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_exceptions_resource` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_exceptions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sri_configurations`
--
ALTER TABLE `sri_configurations`
  ADD CONSTRAINT `fk_sri_config_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `fk_settings_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_settings_updated_by` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_sessions_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD CONSTRAINT `fk_waitlist_appointment` FOREIGN KEY (`scheduled_appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_waitlist_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_waitlist_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_waitlist_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_waitlist_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_waitlist_professional` FOREIGN KEY (`preferred_professional_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_waitlist_type` FOREIGN KEY (`appointment_type_id`) REFERENCES `appointment_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `evt_cleanup_sessions` ON SCHEDULE EVERY 1 HOUR STARTS '2025-12-28 13:04:33' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    CALL sp_cleanup_expired_sessions();
END$$

CREATE DEFINER=`root`@`localhost` EVENT `evt_expire_waiting_list` ON SCHEDULE EVERY 1 DAY STARTS '2025-12-29 02:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    UPDATE waiting_list
    SET status = 'expired'
    WHERE status = 'waiting'
      AND date_until IS NOT NULL
      AND date_until < CURDATE();
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
