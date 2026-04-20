
# 📦 Pixelio E-commerce — Estructura de Base de Datos (Actualizada)

## 1️⃣5️⃣ attributes
| Campo | Tipo | Descripción |
|-------|------|--------------|
| id | BIGINT PK | Clave primaria |
| name | STRING | Nombre del atributo (ej: Material) |
| slug | STRING UNIQUE | Slug amigable |
| is_active | BOOLEAN DEFAULT 1 | Activo o no |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

## 1️⃣6️⃣ attribute_values
| Campo | Tipo | Descripción |
|-------|------|--------------|
| id | BIGINT PK | Clave primaria |
| attribute_id | BIGINT FK | FK a attributes |
| value | STRING | Valor del atributo (ej: Madera) |
| slug | STRING UNIQUE | Slug amigable |
| is_active | BOOLEAN DEFAULT 1 | Activo o no |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

## 1️⃣7️⃣ attribute_product (pivot)
| Campo | Tipo | Descripción |
|-------|------|--------------|
| product_id | BIGINT FK | FK a products |
| attribute_value_id | BIGINT FK | FK a attribute_values |
