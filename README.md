# 🦷 Shalom Dental

**Sistema de Gestión Integral para Clínicas Odontológicas**

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.4+-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)
![License](https://img.shields.io/badge/License-Proprietary-red?style=flat-square)

---

## 📋 Descripción

**Shalom Dental** es un sistema de gestión integral diseñado específicamente para clínicas odontológicas en Ecuador. Permite administrar pacientes, citas, historiales clínicos, facturación electrónica (integración con SRI), y múltiples sucursales desde una única plataforma.

### ✨ Características Principales

| Característica | Descripción |
|----------------|-------------|
| 🏥 **Multi-sede** | Gestión de múltiples sucursales |
| 👥 **Pacientes** | Historiales clínicos con odontograma |
| 📅 **Agenda** | Calendario visual con drag & drop |
| 💰 **Facturación** | Electrónica integrada con SRI |
| 📊 **Reportes** | Dashboard con métricas en tiempo real |
| 🔐 **Seguridad** | Roles y permisos granulares |
| 📱 **Responsive** | Adaptable a cualquier dispositivo |
| 🔔 **Notificaciones** | WhatsApp y Email automáticos |

---

## 🔧 Requisitos

| Componente | Versión Mínima |
|------------|----------------|
| PHP | 8.2+ |
| MySQL | 8.0+ |
| Node.js | 18+ |
| Composer | 2.x |

### Extensiones PHP

```
php-pdo, php-mysql, php-mbstring, php-json, php-openssl, php-curl, php-gd, php-zip
```

---

## 🚀 Instalación Rápida

```bash
# 1. Descomprimir y entrar al proyecto
unzip shalom-dental-final.zip
cd shalom-dental

# 2. Ejecutar script de instalación
chmod +x bin/setup.sh
./bin/setup.sh

# 3. Configurar base de datos
mysql -u root -p < shalom_dental_database.sql

# 4. Editar configuración
nano .env

# 5. Crear usuario administrador
php bin/create-admin.php

# 6. Iniciar servidor de desarrollo
php -S localhost:8000 -t public
```

---

## 📁 Estructura del Proyecto

```
shalom-dental/
├── config/                 # Configuraciones
│   ├── app.php            # Config general
│   └── database.php       # Config MySQL
│
├── public/                 # Document Root
│   ├── index.php          # Front Controller
│   ├── .htaccess          # Config Apache
│   └── assets/            # CSS, JS, imágenes
│
├── src/
│   ├── Core/              # Framework
│   │   ├── Application.php    # Container/Kernel
│   │   ├── Database.php       # PDO Wrapper
│   │   ├── Router.php         # Enrutador
│   │   ├── Request.php        # Petición HTTP
│   │   ├── Response.php       # Respuesta HTTP
│   │   ├── Session.php        # Sesiones
│   │   ├── View.php           # Motor de plantillas
│   │   ├── Middleware/        # Auth, CSRF, Role, Location
│   │   └── Helpers/           # Funciones globales
│   │
│   ├── Modules/           # Módulos de negocio
│   │   ├── Auth/          # ✅ Autenticación
│   │   ├── Dashboard/     # ✅ Panel principal
│   │   ├── Patients/      # 🔜 Pacientes
│   │   ├── Agenda/        # 🔜 Citas
│   │   └── Billing/       # 🔜 Facturación
│   │
│   ├── Views/             # Plantillas PHP
│   └── css/               # CSS fuente (Tailwind)
│
├── storage/               # Logs, cache, uploads
├── bin/                   # Scripts CLI
├── docs/                  # Documentación
│
├── tailwind.config.js     # Config Tailwind
├── package.json           # Deps Node.js
├── composer.json          # Deps PHP
└── .env                   # Variables de entorno
```

---

## ⚙️ Configuración

### Archivo `.env`

```env
# Aplicación
APP_NAME="Shalom Dental"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
APP_TIMEZONE=America/Guayaquil

# Base de Datos
DB_HOST=127.0.0.1
DB_DATABASE=shalom_dental
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# SRI (Ecuador)
SRI_ENVIRONMENT=1
SRI_CERTIFICATE_PATH=/path/to/certificate.p12
```

---

## 🛠️ Desarrollo

### Comandos Disponibles

```bash
# Servidor de desarrollo
php -S localhost:8000 -t public

# Tailwind CSS
npm run dev          # Watch mode
npm run build        # Compilar
npm run build:prod   # Producción (minificado)

# PHP
composer install     # Instalar dependencias
composer test        # Ejecutar tests
```

### Compilación de Assets

```bash
# Desarrollo (watch)
npm run dev

# Producción
npm run build:prod

# Tamaño esperado
# CDN: ~3MB → Compilado: ~10-50KB
```

---

## 👥 Roles y Permisos

| Rol | Acceso |
|-----|--------|
| `super_admin` | Todo el sistema |
| `admin` | Toda la sede |
| `odontologo` | Agenda y pacientes propios |
| `recepcion` | Agenda y facturación básica |

### Uso en Código

```php
// Verificar permiso
if (can('agenda.appointments.create')) {
    // Crear cita
}

// Verificar rol
if (hasRole('admin', 'super_admin')) {
    // Mostrar admin
}
```

---

## 🔒 Seguridad

- ✅ CSRF Protection en formularios
- ✅ SQL Injection prevention (PDO)
- ✅ XSS Prevention (escape automático)
- ✅ Password Hashing (bcrypt)
- ✅ Session Security (HTTP-only, Secure)
- ✅ Account Lockout (5 intentos)

---

## 📊 Estado del Proyecto

| Sprint | Módulo | Estado |
|--------|--------|--------|
| 0/1 | Autenticación | ✅ Completado |
| 0/1 | Dashboard | ✅ Completado |
| 2 | Pacientes | 🔜 Pendiente |
| 3 | Agenda | 🔜 Pendiente |
| 4 | Facturación | 🔜 Pendiente |
| 5 | Configuración | 🔜 Pendiente |

---

## 📖 Documentación

| Documento | Descripción |
|-----------|-------------|
| [TAILWIND-SETUP.md](docs/TAILWIND-SETUP.md) | Configuración de Tailwind CSS |
| [README-SPRINT-01.md](README-SPRINT-01.md) | Detalle del Sprint 0/1 |

---

## 🤝 Soporte

- **Email**: soporte@shalom-dental.com
- **Documentación**: `/docs/`

---

## 📄 Licencia

Copyright © 2024 Shalom Dental. Todos los derechos reservados.

---

<p align="center">
  <strong>🦷 Shalom Dental</strong><br>
  Sistema de Gestión Odontológica<br>
  <sub>Hecho en Ecuador 🇪🇨</sub>
</p>
