# Reporte final del Sprint 1 · SIGLA 2.0 (TOS)

**Sprint:** 1 · lunes 2 al viernes 6 de noviembre de 2026 (1 semana).
**Fecha del reporte:** lunes 9 de noviembre de 2026.
**Fuente:** `docs/sprint-1.md` (última edición registrada: 2026-11-04), `docs/product-backlog.md` y `docs/database.md`.
**Estado:** borrador para revisión del Scrum Master.

> **Límite de este reporte.** Los documentos no tienen ninguna tarea marcada el 5 ni el 6 de noviembre. Todo lo que sigue es lo que quedó registrado hasta el 4. Si se terminó algo más (por ejemplo T25) y no se marcó, hay que ajustar las cifras de la sección 1.

## 1. Resumen

| Indicador | Resultado |
| --- | --- |
| Tareas comprometidas (T01 a T25 y T29) | 25 de 26 terminadas (96 %) |
| Horas estimadas comprometidas | 34.5 h |
| Horas estimadas terminadas | 31.5 h (91 %) |
| Capacidad del sprint | 20 h, unas 18 h para tareas |
| Historias entregadas | 7: H01, H02, H03, H04, H06, H09 y H27 |
| Historias pendientes | H10 (T25, 3 h) |
| Adelantos opcionales (T26 a T28, 5 h) | No se iniciaron |
| PR registrados en el sprint | 19 (#1, #2, #4, #5, #6, #7, #15, #16, #19, #21, #25, #26, #27, #28, #29, #30, #33, #34 y #35) |

Las horas son **estimadas**, no medidas. Son las del plan, ajustadas el 2026-11-03 para que cupieran en la semana.

## 2. Resultado frente al Sprint Goal

Sprint Goal (ampliado el 2026-11-04): dejar lista la base del proyecto y tener construidas las listas maestras hasta tipos de dispositivos de seguridad: usuarios, líneas navieras, proveedores y tipos de dispositivo.

**Resultado: cumplido en parte.** La base y tres de las cuatro listas están en `main`. Falta proveedores.

| Parte del objetivo | Resultado | Tareas |
| --- | --- | --- |
| Stack TALL instalado (Laravel 13, Livewire 4, Flux, Tailwind 4, Fortify, Reverb, Boost) | Cumplido | T03, T04 |
| Identidad visual y plantillas aprobadas | Cumplido | T05, T06, T07, T10 |
| Guideline y rules base en el repo | Cumplido | T01, T08, T09 |
| Usuarios (acceso y gestión) | Cumplido | T11 a T15, T19 |
| Líneas navieras | Cumplido | T23 |
| Tipos de dispositivo | Cumplido | T24 |
| Proveedores | **No cumplido** | T25 |

## 3. Historias

| Historia | Épica | Estado | Tarea | PR |
| --- | --- | --- | --- | --- |
| H01 · Iniciar sesión | E1 | Entregada | T11 | #6 |
| H02 · Cerrar sesión | E1 | Entregada | T12 | #7 |
| H03 · Recuperar contraseña | E1 | Entregada | T13 | #15 |
| H04 · Gestionar usuarios intranet | E1 | Entregada, con el criterio 12 pendiente (consulta de DNI; depende de H10) | T15, T19 | #19, #26 |
| H09 · Gestionar líneas navieras | E8 | Entregada | T23 | #34 |
| H06 · Gestionar tipos de dispositivo | E2 | Entregada | T24 | #35 |
| H27 · Modificar la empresa y gestionar sucursales | E1 | Entregada (en el backlog sigue como propuesta sin aprobar) | T29 | #27, #28 |
| H10 · Gestionar proveedores | E8 | **Pendiente** | T25 | |

Avance del Product Backlog: 6 de las 20 historias aprobadas (H01 a H17 y H21 a H23) están entregadas. H18 a H20 y la épica E6 siguen en revisión, y H24 a H26 son propuestas sin aprobar.

## 4. Tareas

| ID | Tarea | Quién | Horas | Terminada | PR |
| --- | --- | --- | --- | --- | --- |
| T01 | Rules base en `.ai/rules/` | Claude Code | 0.5 | 2026-11-02 | #4 |
| T02 | Repositorio en GitHub | Scrum Master | 0.25 | 2026-10-29 | |
| T03 | Instalación base (Laravel 13, TALL, Reverb, Boost) | Claude Code | 1.5 | 2026-10-29 | #1 |
| T04 | Revisar y confirmar la instalación | — | 0.5 | 2026-10-29 | |
| T05 | Lienzo de Claude Design | Scrum Master | 0.25 | 2026-10-29 | |
| T06 | Identidad visual | — | 1.5 | 2026-10-29 | |
| T07 | Plantillas base (acceso y panel) | — | 2 | 2026-10-29 | |
| T08 | Guideline `flujo-de-trabajo.md` | — | 1.25 | 2026-10-30 | |
| T09 | Subir `docs/` y la guideline, `boost:update` | — | 0.5 | 2026-10-30 | #2 |
| T10 | Plantilla base: tema, layouts y componentes | Claude Code | 2 | 2026-11-02 | #5 |
| T11 | H01 · Iniciar sesión | Claude Code | 1 | 2026-11-02 | #6 |
| T12 | H02 · Cerrar sesión | Claude Code | 0.5 | 2026-11-02 | #7 |
| T13 | H03 · Recuperar contraseña | Claude Code | 1 | 2026-11-03 | #15 |
| T14 | Rutas de intranet | Claude Code | 1 | 2026-11-03 | #16 |
| T15 | H04 · Gestionar usuarios intranet | Claude Code | 2.75 | 2026-11-03 | #19 |
| T16 | Diseñar y aprobar el layout de los correos | Claude Code | 0.5 | 2026-11-03 | #21 |
| T17 | Adaptar el layout de los correos | Claude Code | 1 | 2026-11-03 | #21 |
| T18 | Modelo de datos en `docs/database.md` | Claude Code | 1 | 2026-11-04 | #25 |
| T19 | Tipos de documento, sucursales y empresa; campos en `users` | Claude Code | 2.5 | 2026-11-04 | #26 |
| T20 | Glosario `docs/glossary.md` | Scrum Master / Claude Code | 0.5 | 2026-11-04 | #33 |
| T21 | Cerrar pendientes de H09, H10 y H06 | Scrum Master | 1 | 2026-11-04 | #29 |
| T22 | Servicio de consulta de RUC y DNI (Decolecta, `SunatService`) | Scrum Master / Claude Code | 1 | 2026-11-04 | #30 |
| T23 | H09 · Gestionar líneas navieras | Claude Code | 1.5 | 2026-11-04 | #34 |
| T24 | H06 · Gestionar tipos de dispositivo | Claude Code | 1.5 | 2026-11-04 | #35 |
| T25 | H10 · Gestionar proveedores | Claude Code | 3 | **Pendiente** | |
| T26 | (Opcional) Puente con SIGLA 1.0 para H05 | Claude Code | 3 | No iniciada | |
| T27 | (Opcional) Preparar H07 y H08 | Scrum Master | 1 | No iniciada | |
| T28 | (Opcional) Empresa y sucursal en sesión | Claude Code | 1 | No iniciada | |
| T29 | H27 · Empresa y sucursales | Claude Code | 4.5 | 2026-11-04 | #27, #28 |

Las fechas salen de las notas de `docs/sprint-1.md`; solo la de T02 (repositorio creado) sale de la decisión guardada del 2026-10-29. Donde el sprint no indica quién hace la tarea, se deja "—".

## 5. Avance por día

| Día | Horas terminadas | Acumulado |
| --- | --- | --- |
| 29 y 30 de octubre (antes del inicio oficial) | 7.75 | 7.75 |
| Lunes 2 de noviembre | 4 | 11.75 |
| Martes 3 de noviembre | 6.25 | 18 |
| Miércoles 4 de noviembre | 13.5 | 31.5 |
| Jueves 5 de noviembre | sin registro | 31.5 |
| Viernes 6 de noviembre | sin registro | 31.5 |

Dos cosas se ven en la tabla. Las tareas T02 a T09 (7.75 h) se hicieron la semana anterior, antes del inicio oficial del sprint. Y el 43 % de lo terminado (13.5 h de 31.5) cayó en un solo día, el miércoles 4.

## 6. Cambios de alcance durante el sprint

- **Lunes 2.** Se partió sin historias (opción A). Se sumaron H01 a H04, la plantilla base (T10) y las rutas de intranet (T14).
- **Martes 3.** Se ajustaron las duraciones de todas las tareas para que cupieran en la semana: de 35.5 h a 18 h, sin mover fechas. Se sumó el layout de correos (T16, T17).
- **Miércoles 4.** Se amplió el Sprint Goal con las listas maestras y se sumaron T18 a T25 y T29 (16.5 h), más T26 a T28 como adelanto opcional (5 h). H27 creció con la gestión de sucursales.

Con eso el compromiso llegó a 34.5 h contra 18 h disponibles (192 %). El equipo terminó 31.5 h, así que lo que se registró como terminado supera por mucho la capacidad declarada.

## 7. Decisiones tomadas en el sprint

- Sin Jetstream: starter kit Livewire de Laravel 13 con Fortify (2026-10-29).
- Sin GSD: todo se organiza solo con Scrum (2026-10-29).
- Flujo con Claude Code: el usuario indica el ID de la historia, Claude Code abre el PR solo después del visto bueno y el merge lo hace el usuario (2026-10-30).
- Roles y permisos al final, con `laravel-permission`, al culminar E6 (H24); mientras tanto, cualquier usuario con sesión usa las pantallas (2026-11-03).
- Usuarios desactivados con Soft Deletes (2026-11-03).
- Layout único de correos: propuesta C con el borde azul de la A (2026-11-03).
- `docs/database.md` como fuente de verdad del modelo de datos; toda migración lo actualiza en el mismo PR (2026-11-04).
- Una sola empresa (`companies`) y sucursales gestionadas en pantalla (2026-11-04).
- Consulta de RUC y DNI con Decolecta (2026-11-04).
- Los kits se despachan a trabajadores, no a usuarios (2026-11-04).

## 8. Observaciones para la retrospectiva

Son lecturas de los datos, para conversarlas. La retrospectiva la decide el equipo.

**Lo que funcionó**
- Casi todo el compromiso quedó terminado y mergeado en `main`, con un PR por historia.
- El flujo de la guideline se cumplió: cada tarea terminada quedó marcada en `docs/sprint-1.md` con su PR.
- La documentación (`database.md`, glosario, backlog) se mantuvo al día junto con el código.

**Lo que se puede mejorar**
- **Sobrecompromiso.** Se sumó trabajo el 3 y el 4 hasta casi duplicar la capacidad, sin acordar qué salía. Lo que no entró fue T25, la última del compromiso y parte del Sprint Goal.
- **Horas sin medir.** Las horas del plan se ajustaron para que cupieran. Sin registrar horas reales no hay cómo saber cuánto rinde de verdad la capacidad de 20 h con Claude Code.
- **Trabajo concentrado.** 13.5 h el miércoles y nada registrado el jueves y el viernes. Conviene confirmar si fue así o si faltó marcar tareas.
- **Historias sin aprobar ya construidas.** H27 está entregada, pero sigue como propuesta en el backlog.
- **Temas abiertos desde la planificación.** El reparto entre el Scrum Master y el asistente, quién prueba el trabajo del asistente y si alcanzan las pruebas automáticas no tienen respuesta anotada en `docs/sprint-1.md`.

## 9. Lo que pasa al Sprint 2

- T25 · H10 · Gestionar proveedores (3 h).
- T26 a T28, los tres adelantos opcionales (5 h).
- H04, criterio 12 (completar el nombre con la consulta de DNI): depende de H10 y no tiene tarea todavía.
- Incertidumbre abierta: licencia de la plantilla de ThemeForest.
- Dependencia externa abierta: la API de SIGLA 1.0 debe entregar todos los datos filtrando por contenedor y booking antes de construir H05.
- Por decidir: aprobar H27 en el backlog.

El plan del Sprint 2 está en `docs/sprint-2.md`.
