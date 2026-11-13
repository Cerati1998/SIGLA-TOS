---
paths:
  - '**'
---

# Código y stack

## Idioma
Pantallas, mensajes y validaciones en español. Todo lo demás en inglés y con las convenciones de Laravel: tablas, columnas, claves foráneas, índices, clases, modelos, Actions, Services, Form Objects, componentes Livewire, métodos, variables, constantes, nombres de rutas, segmentos de URL (por ejemplo `/users`, no `/usuarios`) y valores guardados en la base de datos (por ejemplo estados: `active`, no `activo`). Tablas en plural y snake_case, modelos en singular.

## Stack
Laravel 13 con Livewire 4, Flux, Rappasoft Livewire Tables, WireUI, SweetAlert2, Tailwind 4 y Fortify. Sin Jetstream. Cada paquete nuevo debe ser aprobado por el Scrum Master antes de instalarlo: proponlo con su motivo y espera su respuesta.

## Configuración
Nada de claves ni URLs en el código; todo por `.env` y `config()`. Colas, sesión y caché con driver `database`.

## Términos del negocio
Los términos del negocio sin traducción obvia (por ejemplo precinto, funda, gate out) tienen un único nombre en inglés, registrado en `docs/glossary.md`. Antes de crear un nombre en el código, consulta el glosario. Si aparece un término nuevo, propónselo al usuario con su nombre en inglés y, cuando lo apruebe, agrégalo al glosario en el mismo PR. Nunca inventes una traducción distinta para un término que ya está en el glosario.

## Borrar ramas después del merge
Después de cada merge a `main` se borra la rama, local y remota, también las de historia y de épica, y las de tareas del sprint, mejoras y detalles. Solo se conserva una rama con commits que no llegaron a `main`, hasta revisarla con el usuario. Razón: mantener limpio el repositorio; el rastro queda en los PR (cambiado por el usuario el 2026-11-13).
