#!/bin/bash

# ============================================================================
# SHALOM DENTAL - Script de Creación de Estructura del Proyecto
# ============================================================================
# 
# USO:
#   chmod +x create_project_structure.sh
#   ./create_project_structure.sh [ruta_destino]
#
# EJEMPLO:
#   ./create_project_structure.sh /var/www/shalom-dental
#   ./create_project_structure.sh ~/projects/shalom-dental
#
# Si no se especifica ruta, se crea en el directorio actual
# ============================================================================

set -e  # Salir si hay error

# Color para mensajes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Directorio base
BASE_DIR="${1:-.}/shalom-dental"

echo -e "${BLUE}============================================${NC}"
echo -e "${BLUE}  SHALOM DENTAL - Creación de Estructura   ${NC}"
echo -e "${BLUE}============================================${NC}"
echo ""
echo -e "${YELLOW}Directorio destino: ${BASE_DIR}${NC}"
echo ""

# Crear directorio base
mkdir -p "$BASE_DIR"
cd "$BASE_DIR"

echo -e "${GREEN}[1/10] Creando estructura raíz...${NC}"

# ============================================================================
# DIRECTORIO PUBLIC (Document Root)
# ============================================================================
echo -e "${GREEN}[2/10] Creando /public...${NC}"

mkdir -p public/assets/css
mkdir -p public/assets/js/components
mkdir -p public/assets/js/modules
mkdir -p public/assets/js/vendor
mkdir -p public/assets/images/icons
mkdir -p public/assets/images/logos
mkdir -p public/assets/fonts
mkdir -p public/uploads/temp

# ============================================================================
# DIRECTORIO SRC (Código Fuente)
# ============================================================================
echo -e "${GREEN}[3/10] Creando /src...${NC}"

# Core Framework
mkdir -p src/Core/Middleware
mkdir -p src/Core/Exceptions
mkdir -p src/Core/Helpers
mkdir -p src/Core/Traits
mkdir -p src/Core/Contracts

# Módulos de Negocio
# Auth
mkdir -p src/Modules/Auth/Controllers
mkdir -p src/Modules/Auth/Services
mkdir -p src/Modules/Auth/Repositories
mkdir -p src/Modules/Auth/Models
mkdir -p src/Modules/Auth/Validators

# Dashboard
mkdir -p src/Modules/Dashboard/Controllers
mkdir -p src/Modules/Dashboard/Services
mkdir -p src/Modules/Dashboard/Widgets

# Patients
mkdir -p src/Modules/Patients/Controllers
mkdir -p src/Modules/Patients/Services
mkdir -p src/Modules/Patients/Repositories
mkdir -p src/Modules/Patients/Models
mkdir -p src/Modules/Patients/Validators
mkdir -p src/Modules/Patients/Events

# Agenda
mkdir -p src/Modules/Agenda/Controllers
mkdir -p src/Modules/Agenda/Services
mkdir -p src/Modules/Agenda/Repositories
mkdir -p src/Modules/Agenda/Models
mkdir -p src/Modules/Agenda/Validators
mkdir -p src/Modules/Agenda/Events

# Billing
mkdir -p src/Modules/Billing/Controllers
mkdir -p src/Modules/Billing/Services
mkdir -p src/Modules/Billing/Services/SRI
mkdir -p src/Modules/Billing/Repositories
mkdir -p src/Modules/Billing/Models
mkdir -p src/Modules/Billing/Validators
mkdir -p src/Modules/Billing/Events

# Notifications
mkdir -p src/Modules/Notifications/Controllers
mkdir -p src/Modules/Notifications/Services
mkdir -p src/Modules/Notifications/Channels
mkdir -p src/Modules/Notifications/Adapters
mkdir -p src/Modules/Notifications/Repositories
mkdir -p src/Modules/Notifications/Models
mkdir -p src/Modules/Notifications/Events

# Files
mkdir -p src/Modules/Files/Controllers
mkdir -p src/Modules/Files/Services
mkdir -p src/Modules/Files/Repositories
mkdir -p src/Modules/Files/Models

# Config
mkdir -p src/Modules/Config/Controllers
mkdir -p src/Modules/Config/Services
mkdir -p src/Modules/Config/Repositories
mkdir -p src/Modules/Config/Models
mkdir -p src/Modules/Config/Validators

# Reports
mkdir -p src/Modules/Reports/Controllers
mkdir -p src/Modules/Reports/Services
mkdir -p src/Modules/Reports/Exports
mkdir -p src/Modules/Reports/Charts

# Audit
mkdir -p src/Modules/Audit/Controllers
mkdir -p src/Modules/Audit/Services
mkdir -p src/Modules/Audit/Repositories
mkdir -p src/Modules/Audit/Models

# ============================================================================
# DIRECTORIO VIEWS
# ============================================================================
echo -e "${GREEN}[4/10] Creando /src/Views...${NC}"

mkdir -p src/Views/layouts/partials
mkdir -p src/Views/components/forms
mkdir -p src/Views/components/tables
mkdir -p src/Views/components/modals
mkdir -p src/Views/components/cards
mkdir -p src/Views/components/alerts
mkdir -p src/Views/components/navigation

mkdir -p src/Views/auth
mkdir -p src/Views/dashboard
mkdir -p src/Views/patients
mkdir -p src/Views/agenda
mkdir -p src/Views/billing
mkdir -p src/Views/billing/invoices
mkdir -p src/Views/billing/credit-notes
mkdir -p src/Views/billing/monitor
mkdir -p src/Views/notifications
mkdir -p src/Views/config/organization
mkdir -p src/Views/config/locations
mkdir -p src/Views/config/resources
mkdir -p src/Views/config/users
mkdir -p src/Views/config/appointment-types
mkdir -p src/Views/config/schedules
mkdir -p src/Views/config/holidays
mkdir -p src/Views/config/sri
mkdir -p src/Views/reports
mkdir -p src/Views/errors

# ============================================================================
# DIRECTORIO CONFIG
# ============================================================================
echo -e "${GREEN}[5/10] Creando /config...${NC}"

mkdir -p config

# ============================================================================
# DIRECTORIO STORAGE
# ============================================================================
echo -e "${GREEN}[6/10] Creando /storage...${NC}"

mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p storage/sessions
mkdir -p storage/uploads/patients
mkdir -p storage/uploads/avatars
mkdir -p storage/uploads/temp
mkdir -p storage/invoices/xml
mkdir -p storage/invoices/ride
mkdir -p storage/certificates
mkdir -p storage/backups

# ============================================================================
# DIRECTORIO DATABASE
# ============================================================================
echo -e "${GREEN}[7/10] Creando /database...${NC}"

mkdir -p database/migrations
mkdir -p database/seeds
mkdir -p database/factories

# ============================================================================
# DIRECTORIO BIN (Scripts CLI)
# ============================================================================
echo -e "${GREEN}[8/10] Creando /bin...${NC}"

mkdir -p bin

# ============================================================================
# DIRECTORIO TESTS
# ============================================================================
echo -e "${GREEN}[9/10] Creando /tests...${NC}"

mkdir -p tests/Unit/Core
mkdir -p tests/Unit/Services
mkdir -p tests/Unit/Repositories
mkdir -p tests/Unit/Validators
mkdir -p tests/Integration/Api
mkdir -p tests/Integration/Database
mkdir -p tests/Feature/Auth
mkdir -p tests/Feature/Agenda
mkdir -p tests/Feature/Billing
mkdir -p tests/Fixtures

# ============================================================================
# DIRECTORIO DOCS
# ============================================================================
echo -e "${GREEN}[10/10] Creando /docs...${NC}"

mkdir -p docs/api
mkdir -p docs/database
mkdir -p docs/user-manual

# ============================================================================
# CREAR ARCHIVOS BASE VACÍOS
# ============================================================================
echo -e "${YELLOW}Creando archivos base...${NC}"

# Archivos de configuración raíz
touch .env.example
touch .gitignore
touch composer.json
touch package.json
touch tailwind.config.js
touch postcss.config.js
touch README.md

# Public
touch public/index.php
touch public/.htaccess
touch public/robots.txt
touch public/favicon.ico

# Assets
touch public/assets/css/app.css
touch public/assets/js/app.js
touch public/assets/js/components/calendar.js
touch public/assets/js/components/modal.js
touch public/assets/js/components/toast.js
touch public/assets/js/components/autocomplete.js
touch public/assets/js/components/datepicker.js
touch public/assets/js/modules/agenda.js
touch public/assets/js/modules/patients.js
touch public/assets/js/modules/billing.js

# Core
touch src/bootstrap.php
touch src/Core/Application.php
touch src/Core/Router.php
touch src/Core/Request.php
touch src/Core/Response.php
touch src/Core/View.php
touch src/Core/Database.php
touch src/Core/Session.php
touch src/Core/Validator.php
touch src/Core/Container.php
touch src/Core/ServiceProvider.php

touch src/Core/Middleware/AuthMiddleware.php
touch src/Core/Middleware/RoleMiddleware.php
touch src/Core/Middleware/CsrfMiddleware.php
touch src/Core/Middleware/LocationMiddleware.php
touch src/Core/Middleware/MiddlewareInterface.php

touch src/Core/Exceptions/HttpException.php
touch src/Core/Exceptions/ValidationException.php
touch src/Core/Exceptions/AuthorizationException.php
touch src/Core/Exceptions/NotFoundException.php
touch src/Core/Exceptions/DatabaseException.php

touch src/Core/Helpers/helpers.php
touch src/Core/Helpers/DateHelper.php
touch src/Core/Helpers/FormatHelper.php
touch src/Core/Helpers/StringHelper.php
touch src/Core/Helpers/FileHelper.php

touch src/Core/Traits/HasTimestamps.php
touch src/Core/Traits/SoftDeletes.php
touch src/Core/Traits/Auditable.php

touch src/Core/Contracts/RepositoryInterface.php
touch src/Core/Contracts/ServiceInterface.php

# Módulo Auth
touch src/Modules/Auth/Controllers/AuthController.php
touch src/Modules/Auth/Controllers/PasswordController.php
touch src/Modules/Auth/Services/AuthService.php
touch src/Modules/Auth/Services/PasswordService.php
touch src/Modules/Auth/Repositories/UserRepository.php
touch src/Modules/Auth/Repositories/SessionRepository.php
touch src/Modules/Auth/Models/User.php
touch src/Modules/Auth/Validators/LoginValidator.php
touch src/Modules/Auth/routes.php

# Módulo Dashboard
touch src/Modules/Dashboard/Controllers/DashboardController.php
touch src/Modules/Dashboard/Services/DashboardService.php
touch src/Modules/Dashboard/Services/MetricsService.php
touch src/Modules/Dashboard/Widgets/AppointmentsTodayWidget.php
touch src/Modules/Dashboard/Widgets/WaitingRoomWidget.php
touch src/Modules/Dashboard/Widgets/BillingWidget.php
touch src/Modules/Dashboard/routes.php

# Módulo Patients
touch src/Modules/Patients/Controllers/PatientController.php
touch src/Modules/Patients/Controllers/PatientApiController.php
touch src/Modules/Patients/Controllers/PatientFileController.php
touch src/Modules/Patients/Services/PatientService.php
touch src/Modules/Patients/Services/PatientSearchService.php
touch src/Modules/Patients/Repositories/PatientRepository.php
touch src/Modules/Patients/Repositories/PatientFileRepository.php
touch src/Modules/Patients/Models/Patient.php
touch src/Modules/Patients/Models/PatientFile.php
touch src/Modules/Patients/Validators/PatientValidator.php
touch src/Modules/Patients/Validators/EcuadorianIdValidator.php
touch src/Modules/Patients/Events/PatientCreated.php
touch src/Modules/Patients/routes.php

# Módulo Agenda
touch src/Modules/Agenda/Controllers/AgendaController.php
touch src/Modules/Agenda/Controllers/AppointmentApiController.php
touch src/Modules/Agenda/Controllers/AvailabilityApiController.php
touch src/Modules/Agenda/Controllers/WaitingListController.php
touch src/Modules/Agenda/Controllers/RecurringController.php
touch src/Modules/Agenda/Services/AppointmentService.php
touch src/Modules/Agenda/Services/AvailabilityService.php
touch src/Modules/Agenda/Services/ConflictCheckerService.php
touch src/Modules/Agenda/Services/RecurringService.php
touch src/Modules/Agenda/Services/WaitingListService.php
touch src/Modules/Agenda/Services/ContingencyService.php
touch src/Modules/Agenda/Repositories/AppointmentRepository.php
touch src/Modules/Agenda/Repositories/ScheduleRepository.php
touch src/Modules/Agenda/Repositories/WaitingListRepository.php
touch src/Modules/Agenda/Repositories/RecurringSeriesRepository.php
touch src/Modules/Agenda/Models/Appointment.php
touch src/Modules/Agenda/Models/AppointmentType.php
touch src/Modules/Agenda/Models/RecurringSeries.php
touch src/Modules/Agenda/Models/WaitingListEntry.php
touch src/Modules/Agenda/Models/ProfessionalSchedule.php
touch src/Modules/Agenda/Models/ScheduleException.php
touch src/Modules/Agenda/Validators/AppointmentValidator.php
touch src/Modules/Agenda/Events/AppointmentCreated.php
touch src/Modules/Agenda/Events/AppointmentCancelled.php
touch src/Modules/Agenda/Events/AppointmentStatusChanged.php
touch src/Modules/Agenda/Events/SlotReleased.php
touch src/Modules/Agenda/routes.php

# Módulo Billing
touch src/Modules/Billing/Controllers/InvoiceController.php
touch src/Modules/Billing/Controllers/InvoiceApiController.php
touch src/Modules/Billing/Controllers/CreditNoteController.php
touch src/Modules/Billing/Controllers/SriMonitorController.php
touch src/Modules/Billing/Services/InvoiceService.php
touch src/Modules/Billing/Services/CreditNoteService.php
touch src/Modules/Billing/Services/SequentialService.php
touch src/Modules/Billing/Services/SRI/SriService.php
touch src/Modules/Billing/Services/SRI/XmlGeneratorService.php
touch src/Modules/Billing/Services/SRI/SignerService.php
touch src/Modules/Billing/Services/SRI/RideGeneratorService.php
touch src/Modules/Billing/Services/SRI/AccessKeyGenerator.php
touch src/Modules/Billing/Repositories/InvoiceRepository.php
touch src/Modules/Billing/Repositories/SequentialRepository.php
touch src/Modules/Billing/Repositories/CreditNoteRepository.php
touch src/Modules/Billing/Models/Invoice.php
touch src/Modules/Billing/Models/InvoiceItem.php
touch src/Modules/Billing/Models/InvoicePayment.php
touch src/Modules/Billing/Models/CreditNote.php
touch src/Modules/Billing/Models/SriConfiguration.php
touch src/Modules/Billing/Validators/InvoiceValidator.php
touch src/Modules/Billing/Validators/RucValidator.php
touch src/Modules/Billing/Events/InvoiceCreated.php
touch src/Modules/Billing/Events/InvoiceAuthorized.php
touch src/Modules/Billing/Events/InvoiceRejected.php
touch src/Modules/Billing/routes.php

# Módulo Notifications
touch src/Modules/Notifications/Controllers/NotificationController.php
touch src/Modules/Notifications/Controllers/TemplateController.php
touch src/Modules/Notifications/Services/NotificationService.php
touch src/Modules/Notifications/Services/QueueService.php
touch src/Modules/Notifications/Services/TemplateService.php
touch src/Modules/Notifications/Channels/ChannelInterface.php
touch src/Modules/Notifications/Channels/EmailChannel.php
touch src/Modules/Notifications/Channels/SmsChannel.php
touch src/Modules/Notifications/Channels/WhatsAppChannel.php
touch src/Modules/Notifications/Adapters/SmtpAdapter.php
touch src/Modules/Notifications/Adapters/TwilioAdapter.php
touch src/Modules/Notifications/Adapters/UltraMsgAdapter.php
touch src/Modules/Notifications/Repositories/NotificationRepository.php
touch src/Modules/Notifications/Repositories/TemplateRepository.php
touch src/Modules/Notifications/Models/Notification.php
touch src/Modules/Notifications/Models/NotificationTemplate.php
touch src/Modules/Notifications/Models/NotificationConfig.php
touch src/Modules/Notifications/Events/NotificationSent.php
touch src/Modules/Notifications/Events/NotificationFailed.php
touch src/Modules/Notifications/routes.php

# Módulo Files
touch src/Modules/Files/Controllers/FileController.php
touch src/Modules/Files/Controllers/FileApiController.php
touch src/Modules/Files/Services/FileService.php
touch src/Modules/Files/Services/ImageService.php
touch src/Modules/Files/Repositories/FileRepository.php
touch src/Modules/Files/Models/File.php
touch src/Modules/Files/routes.php

# Módulo Config
touch src/Modules/Config/Controllers/OrganizationController.php
touch src/Modules/Config/Controllers/LocationController.php
touch src/Modules/Config/Controllers/ResourceController.php
touch src/Modules/Config/Controllers/UserController.php
touch src/Modules/Config/Controllers/AppointmentTypeController.php
touch src/Modules/Config/Controllers/ScheduleController.php
touch src/Modules/Config/Controllers/HolidayController.php
touch src/Modules/Config/Controllers/SriConfigController.php
touch src/Modules/Config/Services/OrganizationService.php
touch src/Modules/Config/Services/LocationService.php
touch src/Modules/Config/Services/ResourceService.php
touch src/Modules/Config/Services/UserService.php
touch src/Modules/Config/Services/AppointmentTypeService.php
touch src/Modules/Config/Services/ScheduleService.php
touch src/Modules/Config/Services/HolidayService.php
touch src/Modules/Config/Repositories/OrganizationRepository.php
touch src/Modules/Config/Repositories/LocationRepository.php
touch src/Modules/Config/Repositories/ResourceRepository.php
touch src/Modules/Config/Repositories/UserRepository.php
touch src/Modules/Config/Repositories/AppointmentTypeRepository.php
touch src/Modules/Config/Repositories/ScheduleRepository.php
touch src/Modules/Config/Repositories/HolidayRepository.php
touch src/Modules/Config/Repositories/RoleRepository.php
touch src/Modules/Config/Repositories/PermissionRepository.php
touch src/Modules/Config/Models/Organization.php
touch src/Modules/Config/Models/Location.php
touch src/Modules/Config/Models/Resource.php
touch src/Modules/Config/Models/ResourceType.php
touch src/Modules/Config/Models/Role.php
touch src/Modules/Config/Models/Permission.php
touch src/Modules/Config/Models/Holiday.php
touch src/Modules/Config/Models/SystemSetting.php
touch src/Modules/Config/Validators/UserValidator.php
touch src/Modules/Config/Validators/LocationValidator.php
touch src/Modules/Config/routes.php

# Módulo Reports
touch src/Modules/Reports/Controllers/ReportController.php
touch src/Modules/Reports/Controllers/ExportController.php
touch src/Modules/Reports/Services/ProductivityReportService.php
touch src/Modules/Reports/Services/FinancialReportService.php
touch src/Modules/Reports/Services/AppointmentReportService.php
touch src/Modules/Reports/Services/NoShowReportService.php
touch src/Modules/Reports/Exports/ExcelExporter.php
touch src/Modules/Reports/Exports/PdfExporter.php
touch src/Modules/Reports/Charts/ChartBuilder.php
touch src/Modules/Reports/routes.php

# Módulo Audit
touch src/Modules/Audit/Controllers/AuditController.php
touch src/Modules/Audit/Services/AuditService.php
touch src/Modules/Audit/Repositories/AuditRepository.php
touch src/Modules/Audit/Models/AuditLog.php
touch src/Modules/Audit/routes.php

# Views - Layouts
touch src/Views/layouts/app.php
touch src/Views/layouts/auth.php
touch src/Views/layouts/partials/header.php
touch src/Views/layouts/partials/sidebar.php
touch src/Views/layouts/partials/footer.php
touch src/Views/layouts/partials/scripts.php
touch src/Views/layouts/partials/styles.php

# Views - Components
touch src/Views/components/forms/input.php
touch src/Views/components/forms/select.php
touch src/Views/components/forms/textarea.php
touch src/Views/components/forms/checkbox.php
touch src/Views/components/forms/radio.php
touch src/Views/components/forms/datepicker.php
touch src/Views/components/forms/timepicker.php
touch src/Views/components/forms/file-upload.php
touch src/Views/components/tables/table.php
touch src/Views/components/tables/pagination.php
touch src/Views/components/modals/modal.php
touch src/Views/components/modals/confirm.php
touch src/Views/components/cards/card.php
touch src/Views/components/cards/stat-card.php
touch src/Views/components/alerts/alert.php
touch src/Views/components/alerts/toast.php
touch src/Views/components/navigation/breadcrumb.php
touch src/Views/components/navigation/tabs.php
touch src/Views/components/button.php
touch src/Views/components/badge.php
touch src/Views/components/avatar.php
touch src/Views/components/dropdown.php
touch src/Views/components/loading.php
touch src/Views/components/empty-state.php

# Views - Auth
touch src/Views/auth/login.php
touch src/Views/auth/forgot-password.php
touch src/Views/auth/reset-password.php

# Views - Dashboard
touch src/Views/dashboard/index.php

# Views - Patients
touch src/Views/patients/index.php
touch src/Views/patients/show.php
touch src/Views/patients/create.php
touch src/Views/patients/edit.php
touch src/Views/patients/_form.php
touch src/Views/patients/_table.php
touch src/Views/patients/_files.php

# Views - Agenda
touch src/Views/agenda/index.php
touch src/Views/agenda/calendar.php
touch src/Views/agenda/waiting-room.php
touch src/Views/agenda/waiting-list.php
touch src/Views/agenda/_appointment-modal.php
touch src/Views/agenda/_appointment-detail.php
touch src/Views/agenda/_recurring-modal.php

# Views - Billing
touch src/Views/billing/index.php
touch src/Views/billing/invoices/index.php
touch src/Views/billing/invoices/create.php
touch src/Views/billing/invoices/show.php
touch src/Views/billing/invoices/_form.php
touch src/Views/billing/invoices/_items.php
touch src/Views/billing/credit-notes/index.php
touch src/Views/billing/credit-notes/create.php
touch src/Views/billing/monitor/index.php

# Views - Notifications
touch src/Views/notifications/logs.php
touch src/Views/notifications/templates.php

# Views - Config
touch src/Views/config/organization/edit.php
touch src/Views/config/locations/index.php
touch src/Views/config/resources/index.php
touch src/Views/config/users/index.php
touch src/Views/config/users/create.php
touch src/Views/config/users/edit.php
touch src/Views/config/appointment-types/index.php
touch src/Views/config/schedules/index.php
touch src/Views/config/holidays/index.php
touch src/Views/config/sri/index.php

# Views - Reports
touch src/Views/reports/index.php
touch src/Views/reports/productivity.php
touch src/Views/reports/financial.php
touch src/Views/reports/appointments.php
touch src/Views/reports/no-shows.php

# Views - Errors
touch src/Views/errors/404.php
touch src/Views/errors/403.php
touch src/Views/errors/500.php
touch src/Views/errors/maintenance.php

# Config files
touch config/app.php
touch config/database.php
touch config/mail.php
touch config/sri.php
touch config/filesystems.php
touch config/session.php
touch config/routes.php
touch config/permissions.php

# Database
touch database/schema.sql
touch database/migrations/.gitkeep
touch database/seeds/RolesSeeder.php
touch database/seeds/PermissionsSeeder.php
touch database/seeds/ResourceTypesSeeder.php
touch database/seeds/AppointmentTypesSeeder.php
touch database/seeds/DemoDataSeeder.php

# Bin scripts
touch bin/migrate.php
touch bin/seed.php
touch bin/notification-worker.php
touch bin/sri-monitor.php
touch bin/cleanup-sessions.php
touch bin/backup-database.php

# Tests
touch tests/bootstrap.php
touch tests/TestCase.php
touch tests/Unit/Core/RouterTest.php
touch tests/Unit/Core/ValidatorTest.php
touch tests/Unit/Services/AppointmentServiceTest.php
touch tests/Unit/Services/ConflictCheckerServiceTest.php
touch tests/Unit/Validators/EcuadorianIdValidatorTest.php
touch tests/Integration/Api/AppointmentApiTest.php
touch tests/Integration/Api/PatientApiTest.php
touch tests/Integration/Database/PatientRepositoryTest.php
touch tests/Feature/Auth/LoginTest.php
touch tests/Feature/Agenda/CreateAppointmentTest.php
touch tests/Feature/Billing/CreateInvoiceTest.php
touch tests/Fixtures/patients.json
touch tests/Fixtures/appointments.json

# Docs
touch docs/README.md
touch docs/api/README.md
touch docs/api/appointments.md
touch docs/api/patients.md
touch docs/api/invoices.md
touch docs/database/README.md
touch docs/database/er-diagram.md
touch docs/user-manual/README.md

# ============================================================================
# CREAR ARCHIVOS .gitkeep EN DIRECTORIOS VACÍOS
# ============================================================================
echo -e "${YELLOW}Creando archivos .gitkeep...${NC}"

find . -type d -empty -exec touch {}/.gitkeep \;

# ============================================================================
# PERMISOS
# ============================================================================
echo -e "${YELLOW}Configurando permisos...${NC}"

chmod -R 755 storage
chmod -R 755 public/uploads

# ============================================================================
# RESUMEN
# ============================================================================
echo ""
echo -e "${BLUE}============================================${NC}"
echo -e "${GREEN}  ✅ Estructura creada exitosamente!${NC}"
echo -e "${BLUE}============================================${NC}"
echo ""
echo -e "Directorio: ${YELLOW}$BASE_DIR${NC}"
echo ""
echo -e "Estadísticas:"
echo -e "  📁 Directorios: $(find . -type d | wc -l)"
echo -e "  📄 Archivos:    $(find . -type f | wc -l)"
echo ""
echo -e "${YELLOW}Próximos pasos:${NC}"
echo "  1. cd $BASE_DIR"
echo "  2. composer install"
echo "  3. npm install"
echo "  4. cp .env.example .env"
echo "  5. Configurar .env con datos de BD"
echo "  6. php bin/migrate.php"
echo "  7. php bin/seed.php"
echo ""
echo -e "${BLUE}============================================${NC}"
