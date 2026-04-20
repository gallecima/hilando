
# SIMMPLIA - Sistema de Facturación y Gestión para Usuarios con Suscripción

## 💻 Tecnologías Utilizadas
- **Framework**: Laravel 12
- **Frontend**: Bootstrap 5 (plantilla OneUI)
- **Autenticación**: Laravel Breeze (vistas personalizadas)
- **Plantillas**: Blade (`@extends`, `@section`, `@yield`)
- **Estilos/Scripts**: Laravel Vite (`resources/sass/main.sass`, `resources/js/app.js`)
- **Base de Datos**: MySQL / MariaDB
- **Extras**: jQuery (OneUI), SCSS/SASS, componentes de Bootstrap

## 🔄 Flujo del Proyecto
1. **Inicio de sesión** y **registro** personalizados (estética OneUI)
2. **Dashboard** con resumen de actividad, accesible solo a usuarios autenticados
3. **Gestión de Clientes** con CUIT, IVA, contactos y CRUD completo
4. **Gestión de Servicios** facturables (únicos o recurrentes)
5. **Facturación Electrónica AFIP** con librería `pixelio-afip`
6. **Pagos y Vencimientos** controlados por cliente y servicio
7. **Módulo de Perfil de Usuario** con edición, cambio de contraseña y foto de perfil

## 🗂 Estructura del Proyecto
- `resources/views/layouts/` → `guest.blade.php`, `backend.blade.php`
- `resources/views/auth/` → login, register (con diseño OneUI)
- `resources/views/dashboard.blade.php`
- `resources/sass/main.sass`
- `routes/web.php`
- `app/Http/Controllers/ProfileController.php`

## 📂 Personalizaciones Actuales
- Formularios refactorizados sin componentes Blade (`<x-*>`)
- Formularios divididos con `@include` (`update-profile-information-form`, `update-password-form`, etc.)
- Soporte para subida de foto de perfil (`profile_photo` en `users`)
- Validaciones y errores personalizados usando Bootstrap 5

## ⚙️ Funcionalidades de Perfil de Usuario
- Edición de datos personales
- Cambio de contraseña con validación
- Eliminación de cuenta con confirmación por modal
- Subida de imagen con vista previa
- Layout backend incluye avatar del usuario

## 🧱 Siguientes pasos sugeridos
- Crear módulo de servicios
- Implementar dashboard con métricas
- Integrar lógica de planes y suscripciones
- Finalizar facturación con AFIP y QR

---

© 2025 - SIMMPLIA
