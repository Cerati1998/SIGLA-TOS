# Reporte final del Sprint 3 · SIGLA 2.0 (TOS)

**Sprint:** 3 · lunes 16 al viernes 20 de noviembre de 2026 (1 semana).
**Fecha del reporte:** viernes 20 de noviembre de 2026.
**Fuente:** `docs/sprint-3.md`, `docs/product-backlog.md`, `docs/database.md`, `.ai/rules/` y los PR #61 a #95 de GitHub.
**Estado:** borrador para la Review y la Retrospectiva con el Scrum Master. Las observaciones de la sección 9 y los acuerdos propuestos se confirman en esa conversación.

## 1. Resumen

| Indicador | Resultado |
| --- | --- |
| Tareas del sprint | 21 de 21 terminadas (100 %) |
| Horas estimadas del compromiso | 19 h, todas terminadas |
| Horas estimadas terminadas en total | 53 h (compromiso 19 h y 34 h agregadas durante el sprint) |
| Horas reales registradas | Unas 15.75 h, en 18 de las 21 tareas (sección 5) |
| Capacidad del sprint | 20 h, unas 18 h para tareas |
| Historias entregadas | 11 nuevas: H14, H15, H16, H17, H18, H19, H20, H29, H30, H31 y H32, más 5 correcciones de historias ya entregadas |
| Historias aprobadas en el sprint | H29 y H30 (2026-11-13), H31 (2026-11-17), H18, H19 y H20 (2026-11-20) y H32 (2026-11-20) |
| Historias pendientes del sprint | Ninguna |
| PR del sprint | 35 (#61 a #95), todos mergeados |

## 2. Resultado frente al Sprint Goal

Sprint Goal: dejar construido el recorrido del kit en bodega: activar los dispositivos para despacho por rango, armar kits solo con dispositivos activados, despacharlos a un trabajador o a granel, y consultar kits y despachos. Con esto se cierra E4.

**Resultado: cumplido por completo.** E4 quedó cerrada el martes 17. Con el tiempo que quedó se sumó toda la certificación (E5), que se prueba en campo con el cliente.

| Parte del objetivo | Resultado | Tareas |
| --- | --- | --- |
| Activar los dispositivos para despacho por tramos | Cumplido | T46, T47, T48 |
| Armar kits solo con dispositivos activados | Cumplido | T49 |
| Despachar kits a un trabajador | Cumplido | T50 |
| Despachar dispositivos a granel | Cumplido | T52, T56 |
| Consultar kits y despachos | Cumplido | T51 |

## 3. Historias

| Historia | Épica | Estado | Tarea | PR |
| --- | --- | --- | --- | --- |
| H29 · Activar dispositivos para despacho | E2 | Entregada | T48 | #64 |
| H14 · Crear un kit y agregarle dispositivos | E4 | Entregada | T49 | #65 |
| H15 · Despachar kits | E4 | Entregada, con su PDF y el del despacho | T50 | #67, #68, #71 |
| H16 · Consultar kits y despachos | E4 | Entregada | T51 | #75 |
| H31 · Gestionar clientes | E8 | Entregada (pedida por el usuario el 2026-11-17) | T56 | #74 |
| H30 · Despachar dispositivos a granel | E4 | Entregada, rediseñada con BUMMOCK | T52 | #76 |
| H17 · Iniciar una certificación | E5 | Entregada | T53 | #78 |
| H18 · Inspección interna y externa con fotos | E5 | Entregada | T59 | #91 |
| H19 · Cerrar y reaperturar la certificación | E5 | Entregada | T60 | #92 |
| H20 · Listado de asignaciones y consulta, con PDF | E5 | Entregada | T61 | #93 |
| H32 · Anular la asignación de un kit | E5 | Entregada (pedida por el cliente el 2026-11-20) | T66 | #95 |

**Correcciones de historias ya entregadas**

| Corrección | Historias | Tarea | PR |
| --- | --- | --- | --- |
| La cinta de inspección es un solo tipo, repartido entre interna y externa | H06, H13 | T55 | #63 |
| Tipos de dispositivo sin serie y sin aprobación en lote | H06, H07, H08, H28, H29, H30 | T62 | #89, #90 |
| Nombre de sección único dentro de su tipo | H11 | T63 | #94 |
| Despacho a granel por Operación con el contenedor y su kit Usado | H30 | T64 | #94 |
| Kit en la lista de dispositivos del lote y descarte de dispositivos | H08 | T65 | #94 |

**Avance del Product Backlog.** Ya están entregadas 31 historias: las 20 de los Sprints 1 y 2 y las 11 de este sprint. Quedan E6 (checkpoint), que sigue en revisión, y H24 (roles y permisos), que se construye al culminar E6.

Épicas cerradas en el sprint: E2, E4 y E5. Con E3, E7, E8 y E9 ya cerradas, solo faltan E6 y H24 de E1.

## 4. Tareas

| ID | Tarea | Bloque | Horas estimadas | Horas reales | Terminada | PR |
| --- | --- | --- | --- | --- | --- | --- |
| T46 | Preparar H14, H15, H16, H29 y H30 | Compromiso | 1.5 | 0.5 | 2026-11-13 | #62 |
| T47 | Diseños de H29, H30, H14 y H15 | Compromiso | 2 | — | 2026-11-16 | #65 |
| T48 | H29 · Activar dispositivos | Compromiso | 3 | 0.25 | 2026-11-16 | #64 |
| T49 | H14 · Crear un kit | Compromiso | 4 | 2 | 2026-11-16 | #65 |
| T55 | Corrección: cinta de inspección | Corrección del Sprint 2 | 2 | 0.5 | 2026-11-16 | #63 |
| T50 | H15 · Despachar kits | Compromiso | 3 | 2 | 2026-11-17 | #67, #68, #71 |
| T51 | H16 · Consultar kits y despachos | Compromiso | 2.5 | 1.5 | 2026-11-17 | #75 |
| T56 | H31 · Gestionar clientes | Agregada el 2026-11-17 | 2 | 0.5 | 2026-11-17 | #74 |
| T52 | H30 · Despachar a granel | Compromiso | 3 | 2 | 2026-11-17 | #76 |
| T53 | H17 · Iniciar una certificación | Sumada el 2026-11-17 | 4 | 1 | 2026-11-18 | #78 |
| T57 | Despliegue continuo a producción | Agregada el 2026-11-18 | 3 | — | 2026-11-19 | #79 a #83 |
| T54 | Revisar H18 a H20 | Sumada el 2026-11-17 | 1 | 1 | 2026-11-20 | #85 |
| T58 | Diseños de H18, H19 y H20 | Agregada el 2026-11-19 | 1.5 | — | 2026-11-20 | #88 |
| T59 | H18 · Secciones con fotos | Agregada el 2026-11-19 | 5 | 2.25 | 2026-11-20 | #91 |
| T60 | H19 · Cerrar la certificación | Agregada el 2026-11-19 | 2 | 0.25 | 2026-11-20 | #92 |
| T61 | H20 · Consultar certificaciones | Agregada el 2026-11-19 | 3 | 0.25 | 2026-11-20 | #93 |
| T62 | Corrección: tipos sin serie y sin aprobación | Agregada el 2026-11-20 | 4 | 0.75 | 2026-11-20 | #90 |
| T63 | Corrección: nombre de sección por tipo | Agregada el 2026-11-20 | 1 | 0.75, con T64 y T65 | 2026-11-20 | #94 |
| T64 | Corrección: kit con su contenedor | Agregada el 2026-11-20 | 1 | (con T63) | 2026-11-20 | #94 |
| T65 | Corrección: descarte de dispositivos | Agregada el 2026-11-20 | 1.5 | (con T63) | 2026-11-20 | #94 |
| T66 | H32 · Anular la asignación | Agregada el 2026-11-20 | 3 | 0.25 | 2026-11-20 | #95 |

Las horas reales de T51 se cuentan sin T56 y T52, que se hicieron en el mismo tramo (la nota de T51 en el sprint registra 4.5 h para las tres). Las de T50 suman las 0.5 h de su corrección del PDF.

Otros PR del sprint, sin tarea propia: #61 (plan del Sprint 3), #66 (`main` traído a la rama de H29), #69 (UX con Alpine), #70 (resaltado del selector async), #72 y #73 (rule del alcance de Alpine), #77 (T53 y T54 sumadas al sprint), #84 (marca de T57), #86 (despliegue manual), #87 (checkpoint) y #89 (decisiones de T62).

## 5. Horas estimadas contra reales

| Grupo | Tareas | Estimadas | Reales |
| --- | --- | --- | --- |
| Con horas reales | 18 tareas | 46.5 h | unas 15.75 h |
| Sin horas reales (diseño en el lienzo y despliegue) | T47, T57 y T58 | 6.5 h | sin medir |
| **Total** | 21 tareas | **53 h** | — |

En las tareas medidas, las horas reales son **cerca de un tercio** de las estimadas (15.75 de 46.5 h, 34 %).

**Ojo con esta lectura.** Es la primera vez que se registran horas reales (acuerdo 1 del Sprint 2), y la medida es débil:
- Casi siempre los commits llegaron al final, así que se contó desde que se creó la rama, no desde el primer commit.
- No incluye el tiempo del Scrum Master: probar, revisar los diseños y decidir.
- En H18 a H20 y T63 a T66 se contó hasta el último commit, no hasta el visto bueno, porque los vistos buenos llegaron juntos.

Aun así, todo apunta a que las estimaciones del trabajo de Claude Code están altas.

## 6. Avance por día

| Día | Horas estimadas terminadas | Acumulado |
| --- | --- | --- |
| Viernes 13 (antes de empezar) | 1.5 | 1.5 |
| Lunes 16 | 11 | 12.5 |
| Martes 17 | 10.5 | 23 |
| Miércoles 18 | 4 | 27 |
| Jueves 19 | 3 | 30 |
| Viernes 20 | 23 | 53 |

El compromiso (19 h) quedó terminado el martes 17. El viernes concentró 23 h, casi la mitad del sprint: la certificación completa (H18 a H20), su anulación (H32) y cuatro correcciones.

## 7. Cambios de alcance durante el sprint

- **Viernes 13, en la planificación.** El usuario sumó H29 (activar dispositivos) y H30 (despacho a granel).
- **Lunes 16.** El usuario pidió T55, la corrección de la cinta de inspección, antes de empezar el compromiso. H29 cambió antes de construirse: la activación pasó a ser el estado Inactivo, con aprobación por tramos y bitácora.
- **Martes 17.**
  - El usuario sumó H31 (clientes), que H30 necesitaba, y rediseñó H30 siguiendo BUMMOCK.
  - Con el compromiso terminado, sumó T53 (H17) y T54, que eran adelanto opcional.
- **Miércoles 18.** El usuario sumó T57, el despliegue continuo, para adelantar la implementación con el cliente.
- **Jueves 19.** El usuario sumó T58 a T61, con urgencia alta, para dejar la certificación completa: se prueba en campo con el cliente. E6 pasó al Sprint 4.
- **Viernes 20.**
  - El usuario sumó T62 (tipos sin serie y sin aprobación) y T63 a T65 (secciones por tipo, kit con contenedor y descarte).
  - El cliente pidió H32 (anular la asignación), que se aprobó y se construyó el mismo día como T66.

Con eso el sprint sumó 53 h estimadas contra unas 18 h de capacidad para tareas (294 %). Como en el Sprint 2, el exceso es **trabajo agregado después de cumplir el compromiso**, y esta vez buena parte vino de la prueba con el cliente.

## 8. Decisiones tomadas en el sprint

- **Activación.** Un dispositivo nace Inactivo y pasa a Disponible al registrar la aprobación de la naviera por tramos, con su bitácora (H29). Un tipo puede no requerir esa aprobación, y entonces sus dispositivos nacen Disponibles (T62).
- **Series.** Un tipo puede no utilizar serie: cada dispositivo es su correlativo con ceros, y la serie es única dentro de su tipo. Los tipos con serie la mantienen única en todo SIGLA (T62).
- **Kits.** El escaneo funciona como teclado y termina con Enter. Un kit incompleto se guarda pero no se despacha. El kit es de una sola naviera (T46).
- **Despachos.** Los despachos a granel tienen lista propia, motivo Venta (con cliente) u Operación (con el contenedor de un kit Usado) y número `DG-000001` (H30, T64).
- **Certificación.**
  - La naviera de la API se enlaza por su nombre.
  - El kit va en una sola certificación que no esté anulada.
  - La interna y la externa se hacen en cualquier orden.
  - Se cierra y se reapertura desde Asignaciones.
  - Una asignación se anula con motivo, reutilizando o inhabilitando el kit (H17 a H20, H32).
- **Descarte.** Un dispositivo Inactivo o Disponible fuera de un kit se descarta con motivo, en el estado nuevo Descartado (T65).
- **Despliegue.** Producción en cPanel con releases, despliegue manual desde GitHub Actions solo desde `main` y una base que tiene solo datos de prueba mientras dure la prueba con el cliente (T57).
- **Rules nuevas o cambiadas:** el alcance de Alpine para la UX en el navegador y la estructura de Alpine en las vistas (#72, #73), y el formato único de los PDF del sistema (2026-11-17).

## 9. Retrospectiva

Para conversar con el Scrum Master. Son lecturas de los datos; la retrospectiva la decide el equipo.

### 9.1 Observaciones

**Lo que funcionó**
- **Compromiso y Sprint Goal:** el compromiso quedó terminado el martes y el Sprint Goal se cumplió completo. Además se cerraron E2, E4 y E5.
- **Horas reales:** se registraron en 18 de 21 tareas, como pedía el acuerdo 1. Es la primera medida para calibrar la capacidad.
- **Aprobar antes de construir:** las historias nuevas (H29, H30, H31, H32) y la corrección T62 cerraron sus "por confirmar" antes de construirse (acuerdo 3).
- **Ramas encadenadas:** permitieron construir H18 a H20 y H32 sin esperar merges.

**Lo que se puede mejorar**
- **El viernes cargado.** El último día concentró 23 h estimadas y cuatro correcciones nuevas, varias pedidas en el momento. Un pedido urgente del cliente el último día deja poco margen para probar.
- **Correcciones que cambian al construirse.** T64 cambió dos veces el mismo día (contenedores con su kit, y después solo kits Usados). Una pregunta más antes de construir habría evitado un ida y vuelta.
- **Ramas encadenadas con muchos PR.** Al final hubo cinco PR en cadena. Los números de PR anotados de antemano en el sprint quedaron corridos y hubo que corregirlos.
- **Marca fuera de su PR.** T48 se marcó en el PR #65, no en el suyo (acuerdo 2).
- **Revisión visual.** El `textarea` aplastado por una regla global de CSS pasó las pruebas y lo encontró el usuario al probar. Las pruebas de PHP no ven cómo se pinta la pantalla.

### 9.2 Acuerdos propuestos para el Sprint 4

Por confirmar con el Scrum Master.

1. **Capacidad.** Con unas 15.75 h reales en 46.5 h estimadas, planificar el Sprint 4 con estimaciones más bajas para el trabajo de Claude Code, y seguir midiendo. Así la comparación se apoya en más de un sprint.
2. **Horas reales desde el inicio.** Hacer el primer commit apenas se crea la rama, para que las horas se cuenten desde el primer commit y no desde la creación de la rama.
3. **Pedidos del último día.** Lo que llegue el último día del sprint pasa al siguiente, salvo que el Scrum Master lo marque como urgente.
4. **Número de PR.** Anotar el número del PR en el sprint después de abrirlo, no antes.
5. **Revisión en el navegador.** Antes de pedir el visto bueno de una pantalla con cambios de estilo, revisarla en el navegador, en escritorio y a 390 px.

## 10. Lo que pasa al Sprint 4

**Para revisar y aprobar**
- E6 · Checkpoint: sigue en revisión, sin historias aprobadas. Incluye cómo se usan los dispositivos despachados a granel en la reinspección.

**Abiertas desde antes**
- H24 · Roles y permisos: se construye al culminar E6. Debe decidir también qué rol anula asignaciones (H32) y qué rol descarta dispositivos (T65).
- Mejora de H20: las marcas numeradas sobre los dibujos del contenedor en el PDF.
- Licencia de la plantilla de ThemeForest: revisar qué permite antes de usarla como referencia en producción. Arrastrada del Sprint 1.
- Al terminar la prueba con el cliente: correr `migrate:fresh` en producción y solo los seeders necesarios (T57).

**Por confirmar**
- Las horas estimadas de T62 a T66 (12.5 h) fueron propuesta de Claude Code y no se confirmaron.
