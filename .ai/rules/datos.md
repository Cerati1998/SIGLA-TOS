---
paths:
  - 'app/Actions/**'
  - 'app/Livewire/**'
  - 'app/Models/**'
  - 'app/Services/**'
  - 'database/**'
  - 'resources/views/**'
  - 'routes/**'
---

# Base de datos y datos

## Migraciones
Todo cambio de la base de datos va por migración, el sistema debe levantar desde cero sin errores y las migraciones deben ser compatibles con MariaDB 10.6.

## Borrado
En datos maestros se desactiva en vez de borrar.

## Transacciones
Lo que escribe en varias tablas se guarda todo o nada.

## Datos personales
El DNI y el brevete solo los ven los roles que los necesitan. Nunca en URLs ni en logs.

## Desactivar con Soft Deletes
Desactivar en datos maestros se hace con Soft Deletes (`deleted_at`), no con una columna de estado. Razón: un solo dato indica si el registro está activo.
