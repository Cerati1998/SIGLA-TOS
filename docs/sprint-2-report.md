# Reporte final del Sprint 2 · SIGLA 2.0 (TOS)

**Sprint:** 2 · lunes 9 al viernes 13 de noviembre de 2026 (1 semana).
**Fecha del reporte:** viernes 13 de noviembre de 2026.
**Fuente:** `docs/sprint-2.md`, `docs/product-backlog.md`, `docs/database.md`, `.ai/rules/` y los PR #36 a #59 de GitHub.
**Estado:** Review y Retrospectiva hechas con el Scrum Master el 2026-11-13.

## 1. Resumen

| Indicador | Resultado |
| --- | --- |
| Tareas del sprint (compromiso, adelanto y extensión) | 20 de 20 terminadas (100 %) |
| Horas estimadas del compromiso original (con T26) | 20 h, todas terminadas |
| Horas estimadas terminadas en total | 42.5 h (compromiso 20 h, adelanto T35 2.5 h, extensión 20 h) |
| Capacidad del sprint | 20 h, unas 18 h para tareas |
| Historias entregadas | 13 nuevas: H05, H07, H08, H10, H11, H12, H13, H21, H22, H23, H25, H26 y H28, más el criterio 12 de H04 |
| Historias aprobadas en el sprint | H25, H26 y H27 (2026-11-13) |
| Historias pendientes del sprint | Ninguna |
| PR del sprint | 24 (#36 a #59), todos mergeados |

Las horas son **estimadas**, no medidas. Las horas reales no se registraron (ver sección 9).

## 2. Resultado frente al Sprint Goal

Sprint Goal: cerrar las listas maestras con proveedores (consulta de RUC y DNI incluida) y dejar construido el registro de dispositivos de seguridad: ingresar un lote y consultarlo. Si la API de SIGLA 1.0 está lista, dejar también el puente para consultar solicitudes de retiro.

**Resultado: cumplido por completo**, incluida la parte condicionada a la API de SIGLA 1.0.

| Parte del objetivo | Resultado | Tareas |
| --- | --- | --- |
| Proveedores, con consulta de RUC y DNI | Cumplido | T25 |
| Consulta de DNI al crear un usuario (H04, criterio 12) | Cumplido | T31 |
| Ingresar un lote de dispositivos | Cumplido | T27, T30, T32 |
| Consultar lotes y dispositivos | Cumplido | T33 |
| Corregir un lote (agregada durante el sprint) | Cumplido | T36, T37 |
| Puente con SIGLA 1.0 para consultar solicitudes de retiro | Cumplido | T26 |

Además se adelantó trabajo de E3, E4 y E9 con la extensión del 2026-11-11 (sección 6).

## 3. Historias

| Historia | Épica | Estado | Tarea | PR |
| --- | --- | --- | --- | --- |
| H10 · Gestionar proveedores | E8 | Entregada | T25 | #36 |
| H04 · criterio 12 · Consulta de DNI al crear un usuario | E1 | Entregado | T31 | #38 |
| H07 · Ingresar un lote de dispositivos | E2 | Entregada | T32 | #41 |
| H08 · Consultar lotes y dispositivos | E2 | Entregada | T33 | #42 |
| H28 · Corregir un lote | E2 | Entregada (pedida por el usuario el 2026-11-10) | T37 | #43 |
| H05 · Consultar la solicitud de retiro desde SIGLA 1.0 | E7 | Entregada | T26 | #44 |
| H11 · Gestionar secciones de certificación | E3 | Entregada | T35 | #45 |
| H12 · Definir qué dispositivos lleva cada sección | E3 | Entregada | T38 | #54, #55 |
| H13 · Gestionar tipos de kit | E4 | Entregada | T39 | #53 |
| H21 · Gestionar transportistas | E9 | Entregada | T40 | #46 |
| H22 · Gestionar conductores | E9 | Entregada, con la corrección de conductores por transportista | T41, T45 | #47, #49, #50 |
| H23 · Gestionar vehículos | E9 | Entregada | T42 | #48, #49 |
| H25 · Gestionar trabajadores | E4 | Entregada (aprobada el 2026-11-13) | T43 | #56 |
| H26 · Gestionar zonas de patio | E4 | Entregada (aprobada el 2026-11-13) | T44 | #58 |

**Avance del Product Backlog.** Ya están entregadas 20 historias: las 7 del Sprint 1 (H01, H02, H03, H04, H06, H09 y H27) y las 13 de este sprint. De las historias aprobadas, quedan por construir H14, H15 y H16 (E4) y H17 (E5). H18 a H20 y la épica E6 siguen en revisión, y H24 (roles y permisos) sigue como propuesta, que se construye al culminar E6.

Épicas cerradas en el sprint: E7 y E8. E2, E3 y E9 quedan con todas sus historias entregadas.

## 4. Tareas

| ID | Tarea | Bloque | Horas | Terminada | PR |
| --- | --- | --- | --- | --- | --- |
| T25 | H10 · Gestionar proveedores | Arrastrada del Sprint 1 | 3 | 2026-11-09 | #36 |
| T28 | Empresa y sucursal en sesión y en el título | Arrastrada del Sprint 1 | 1 | 2026-11-09 | #39 |
| T31 | H04, criterio 12 · Consulta de DNI | Compromiso | 1 | 2026-11-09 | #38 |
| T27 | Preparar H07 y H08 | Arrastrada del Sprint 1 | 1 | 2026-11-10 | #40 |
| T30 | Diseño de ingreso de lote | Compromiso | 1 | 2026-11-10 | #40 |
| T32 | H07 · Ingresar un lote | Compromiso | 4 | 2026-11-10 | #41 |
| T33 | H08 · Consultar lotes y dispositivos | Compromiso | 3 | 2026-11-10 | #42 |
| T36 | Diseño de corregir un lote | Compromiso (agregada) | 0.5 | 2026-11-10 | #43 |
| T37 | H28 · Corregir un lote | Compromiso (agregada) | 2.5 | 2026-11-10 | #43 |
| T26 | Puente con SIGLA 1.0 para H05 | Condicionada | 3 | 2026-11-10 | #44 |
| T35 | H11 · Secciones de certificación | Adelanto opcional | 2.5 | 2026-11-10 | #45 |
| T40 | H21 · Transportistas | Extensión | 2 | 2026-11-11 | #46 |
| T41 | H22 · Conductores | Extensión | 2 | 2026-11-11 | #47 |
| T42 | H23 · Vehículos | Extensión | 2 | 2026-11-11 | #48 |
| T45 | H22 · Conductores por transportista | Extensión (agregada) | 3 | 2026-11-11 | #50 |
| T34 | Preparar H11, H12 y H13 | Extensión | 1 | 2026-11-12 | #54 |
| T38 | H12 · Dispositivos por sección | Extensión | 3 | 2026-11-12 | #54 |
| T39 | H13 · Tipos de kit | Extensión | 3 | 2026-11-12 | #53 |
| T43 | H25 · Trabajadores | Extensión | 2 | 2026-11-13 | #56 |
| T44 | H26 · Zonas de patio | Extensión | 2 | 2026-11-13 | #58 |

Otros PR del sprint, sin tarea propia: #37 (reporte del Sprint 1 y plan del Sprint 2), #49 y #55 (marcas de tareas que no alcanzaron a ir en su PR), #51 (botones de crear sin sujeto), #52 (seeder de tipos de dispositivo), #57 (rule de borrar ramas) y #59 (H27 aprobada).

## 5. Avance por día

| Día | Horas terminadas | Acumulado |
| --- | --- | --- |
| Lunes 9 | 5 | 5 |
| Martes 10 | 17.5 | 22.5 |
| Miércoles 11 | 9 | 31.5 |
| Jueves 12 | 7 | 38.5 |
| Viernes 13 | 4 | 42.5 |

El compromiso original (20 h) quedó terminado el martes 10. Desde el miércoles se trabajó la extensión. A diferencia del Sprint 1, hubo tareas terminadas todos los días.

## 6. Cambios de alcance durante el sprint

- **Martes 10.** El usuario sumó H28 (corregir un lote) al revisar H08: T36 y T37, 3 h. T35 (H11) se construyó sin T34, con sus supuestos por confirmar, a pedido del usuario.
- **Miércoles 11.** Con el compromiso terminado, el usuario pidió más trabajo para el miércoles y el jueves: la extensión con T34, T38 a T44 (17 h), y T45 (3 h), la corrección de conductores por transportista.
- **Viernes 13.** El usuario aprobó H25 y H26 antes de construirlas, y H27, que ya estaba construida. Al aprobar H26 pidió el seeder de la zona por defecto y dejar una sola sucursal en el seeder de sucursales.

Con eso el sprint sumó 42.5 h estimadas contra unas 18 h de capacidad para tareas (236 %). Esta vez el exceso fue trabajo **agregado después de cumplir el compromiso**, no un compromiso inflado al planificar.

## 7. Decisiones tomadas en el sprint

- Series de un lote: serie y correlativo sin separador, relleno con ceros hasta el mínimo de dígitos, y un final de serie opcional (2026-11-09 y 2026-11-10).
- Estados de un dispositivo: Disponible, En un kit, Usado y Dañado. Un lote trae como máximo 10 000 series y se guarda sin cola (2026-11-09, T27).
- Un lote solo se corrige en su serie si ningún dispositivo cambió de estado (2026-11-10, H28).
- Las puertas son una sección externa más, con sus cintas y precintos (2026-11-12, P1 de `docs/database.md`).
- El tipo de kit maneja cantidades por tipo de dispositivo, y editarlo no cambia los kits ya armados (2026-11-12, H13).
- Conductores por transportista, con la tabla puente `carrier_driver`, pensando en la extranet (2026-11-11, T45).
- Trabajadores con cargo opcional, proveedor elegido con un buscador en el servidor y «+ Agregar» para crear el proveedor ahí mismo (2026-11-13, H25).
- Zonas de patio: el nombre es único por sucursal aunque la otra zona esté desactivada, y la capacidad es obligatoria y solo informativa (2026-11-13, H26).
- Un solo registro inicial de sucursal: `0001` Depot Paita - Macromar (2026-11-13).
- Rules nuevas o cambiadas: el formato del título de la pestaña (2026-11-09); las acciones junto al título de un campo y los selectores async de catálogos, y el + de «Agregar» como texto (2026-11-13); borrar toda rama después de su merge, también las de historia (2026-11-13).

## 8. Lo que pasa al Sprint 3

**Historias aprobadas listas para planificar**
- H14 · Crear un kit y agregarle dispositivos (E4).
- H15 · Despachar kits (E4).
- H16 · Consultar kits y despachos (E4).
- H17 · Iniciar una certificación (E5). Usa las zonas de patio de H26.

**Abiertas desde antes**
- Revisar lo posterior a la asignación del kit (H18 a H20 y E6), para que E5 y E6 se puedan estimar.
- H24 · Roles y permisos: se construye al culminar E6.
- Incertidumbres que siguen en `docs/sprint-2.md`: la licencia de la plantilla de ThemeForest y el escáner de códigos como teclado (regla común de escaneo, que afecta a H14).

## 9. Retrospectiva

Hecha con el Scrum Master el 2026-11-13. Incluye la retrospectiva del Sprint 1, que había quedado pendiente.

### 9.1 Observaciones para conversar

Son lecturas de los datos; la retrospectiva la decide el equipo.

**Lo que funcionó**
- El compromiso original quedó terminado el martes, y el Sprint Goal se cumplió completo.
- Las ramas encadenadas (T31 sobre T25, T33 sobre T32, T37 sobre T33) evitaron esperar los merges.
- H12, H13, H25 y H26 tuvieron su diseño aprobado en el lienzo y sus "por confirmar" cerrados antes de construirse.
- `docs/database.md` y el backlog se mantuvieron al día dentro de cada PR.

**Lo que se puede mejorar**
- **La capacidad sigue sin calibrar.** Se terminaron 42.5 h estimadas con una capacidad de unas 18 h. Sin horas reales no se sabe si las estimaciones están infladas o si la capacidad con Claude Code es mayor que la declarada.
- **Marcas en PR aparte.** En #49 y #55 la marca de la tarea no alcanzó a ir en el PR de su historia, como pide la guideline (sección 6, punto 4).
- **Historias construidas como propuesta.** H27 llegó al cierre del Sprint 2 sin aprobar; H11 se construyó antes de cerrar sus supuestos (T34). Al final se resolvieron, pero conviene aprobar antes de construir.
- **Pruebas en celular.** El asistente no siempre puede revisar la pantalla a 390 px desde el navegador; la revisión en teléfono depende del usuario.

### 9.2 Temas abiertos del Sprint 1, decididos el 2026-11-13

| Tema | Decisión |
| --- | --- |
| Reparto de tareas entre el Scrum Master y el asistente | Se mantiene: el Scrum Master decide, aprueba los diseños y prueba lo construido; el asistente diseña, construye, escribe las pruebas y mantiene la documentación. |
| Quién prueba el trabajo del asistente | El Scrum Master, también en el celular. El asistente corre las pruebas automáticas, Pint y Larastan, y revisa la pantalla en el navegador. |
| Si alcanzan las pruebas automáticas que pide la Definition of Done | Sí: PHPUnit con el camino normal y los errores principales, al menos una prueba por criterio. Sin pruebas de navegador. |
| Qué se hizo el 5 y el 6 de noviembre que no quedó marcado en `docs/sprint-1.md` | Nada: fueron días sin avance por problemas de salud del equipo. |
| Cómo registrar las horas reales de cada tarea | Desde los commits: las horas entre el primer commit de la rama y el visto bueno del usuario. |

### 9.3 Acuerdos para el Sprint 3

Aceptados por el Scrum Master el 2026-11-13.

1. **Horas reales.** Al marcar una tarea se anotan, junto a la fecha y el PR, las horas entre el primer commit de su rama y el visto bueno del usuario. Es una aproximación: incluye pausas.
2. **Marca en el mismo PR.** La marca de la tarea en `docs/sprint-N.md` va siempre en el PR de su historia, no en uno aparte.
3. **Aprobar antes de construir.** No se construye una historia propuesta o con supuestos abiertos hasta que el usuario la apruebe.
4. **Capacidad del Sprint 3.** Se planifica con horas estimadas, como hasta ahora, y al cierre se comparan con las reales para calibrar la capacidad.
