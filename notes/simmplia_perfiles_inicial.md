
# SIMMPLIA - Módulo de Perfiles de Usuario

## 🧩 Objetivo
Desarrollar un sistema de perfiles de usuario y control de permisos basado en menús y acciones.

## 👥 Tipos de Usuario
- **MASTER**: Usuario creado manualmente o al inicio del sistema. Tiene acceso completo y puede crear perfiles.
- **Administrador**: Usuario común por defecto al registrarse. Accede a funcionalidades según su perfil.

## 🗃️ Tablas a Crear

### 1. `perfiles`
| Campo         | Tipo        | Detalles                          |
|---------------|-------------|-----------------------------------|
| id            | BIGINT      | PK                                |
| nombre        | STRING      | Nombre del perfil (ej: Admin)     |
| descripcion   | TEXT        | Opcional                          |
| is_master     | BOOLEAN     | `true` si es perfil MASTER        |
| created_at    | TIMESTAMP   |                                   |
| updated_at    | TIMESTAMP   |                                   |

### 2. `menus`
| Campo         | Tipo        | Detalles                             |
|---------------|-------------|--------------------------------------|
| id            | BIGINT      | PK                                   |
| nombre        | STRING      | Nombre del menú (ej: Empresas)       |
| icono         | STRING      | Clave para ícono (ej: fa fa-building)|
| ruta          | STRING      | Ruta (ej: /empresas)                 |
| orden         | INTEGER     | Orden de aparición                   |
| created_at    | TIMESTAMP   |                                      |
| updated_at    | TIMESTAMP   |                                      |

### 3. `menu_perfil`
(Para asociar qué menús tiene acceso cada perfil)

| Campo         | Tipo        | Detalles                   |
|---------------|-------------|----------------------------|
| id            | BIGINT      | PK                         |
| perfil_id     | BIGINT      | FK a `perfiles`            |
| menu_id       | BIGINT      | FK a `menus`               |
| created_at    | TIMESTAMP   |                            |
| updated_at    | TIMESTAMP   |                            |

### 4. `permisos`
| Campo         | Tipo        | Detalles                             |
|---------------|-------------|--------------------------------------|
| id            | BIGINT      | PK                                   |
| nombre        | STRING      | Ej: crear_empresa                    |
| descripcion   | STRING      | Descripción legible (opcional)      |
| created_at    | TIMESTAMP   |                                      |
| updated_at    | TIMESTAMP   |                                      |

### 5. `permiso_perfil`
(Qué permisos tiene cada perfil)

| Campo         | Tipo        | Detalles                   |
|---------------|-------------|----------------------------|
| id            | BIGINT      | PK                         |
| perfil_id     | BIGINT      | FK                         |
| permiso_id    | BIGINT      | FK                         |
| created_at    | TIMESTAMP   |                            |
| updated_at    | TIMESTAMP   |                            |

## 🔐 Comportamiento inicial

- Todos los usuarios registrados serán asignados al perfil “Administrador” por defecto.
- El usuario MASTER tendrá acceso completo, sin restricción de menús o permisos.
- Cada perfil podrá editar los menús visibles y los permisos disponibles a medida que se programen.

---

© 2025 - SIMMPLIA | Sistema de Perfiles - Fase 1
