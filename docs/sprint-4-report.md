# Reporte final del Sprint 4 · SIGLA 2.0 (TOS)

**Sprint:** 4 · lunes 23 al viernes 27 de noviembre de 2026 (1 semana).
**Fecha del reporte:** viernes 27 de noviembre de 2026.
**Fuente:** `docs/sprint-4.md`, `docs/product-backlog.md`, `.ai/rules/` y los PR #97 a #137 de GitHub.
**Estado:** sprint cerrado por el usuario el 2026-11-27. Las observaciones de la sección 9 y los acuerdos propuestos se confirman con el Scrum Master al planificar el Sprint 5.

## 1. Resumen

| Indicador | Resultado |
| --- | --- |
| Tareas del sprint | 30 de 30 terminadas (100 %), más T96, descartada por el usuario |
| Horas estimadas del compromiso | 13 h, todas terminadas |
| Horas estimadas terminadas en total | 55 h (compromiso 13 h, adelanto opcional 2 h y 40 h agregadas durante el sprint) |
| Horas reales registradas | Unas 22 h, en 27 de las 30 tareas (sección 5) |
| Capacidad del sprint | 20 h, unas 18 h para tareas |
| Historias entregadas | 9 nuevas: H33, H34, H35, H36, H37, H38, H39, H40 y H42, más 16 correcciones y mejoras de historias ya entregadas |
| Historias aprobadas en el sprint | H33 a H36 (2026-11-23), H37 y H38 (2026-11-24), H39, H40, H41 y H42 (2026-11-27) |
| Historias descartadas | H41 · Sincronizar con 24HM (2026-11-27): los dispositivos GPS se registran a mano |
| Historias pendientes del sprint | Ninguna |
| PR del sprint | 41 (#97 a #137), todos mergeados |

## 2. Resultado frente al Sprint Goal

Sprint Goal: dejar construido el checkpoint: las secciones de checkpoint en el catálogo, el paso a paso que verifica las cintas y sellos de una unidad certificada, la reinspección cuando algo falla y la consulta de los checkpoints. Con esto se cierra E6.

**Resultado: cumplido por completo.** E6 quedó cerrada el jueves 26 con H36. Con E6 cerrada, todas las épicas del Product Goal están construidas; solo falta H24 (roles y permisos), que pasa al Sprint 5. El viernes se abrió E10 (candados GPS), que no estaba en el Product Goal.

| Parte del objetivo | Resultado | Tareas |
| --- | --- | --- |
| Secciones de checkpoint en el catálogo | Cumplido | T67, T68, T69 |
| Paso a paso que verifica cintas y sellos | Cumplido | T70 |
| Reinspección cuando algo falla | Cumplido | T71 |
| Consulta de los checkpoints | Cumplido | T72 |

E6 la construyó un colega del equipo, en paralelo con las correcciones que el usuario trabajó desde `main`. El colega hizo también T93 y T97.

## 3. Historias

| Historia | Épica | Estado | Tarea | PR |
| --- | --- | --- | --- | --- |
| H33 · Secciones de checkpoint | E6 | Entregada | T69 | #107 |
| H34 · Hacer el checkpoint de una unidad | E6 | Entregada | T70 | #123 |
| H35 · Reinspección desde el checkpoint | E6 | Entregada | T71 | #125 |
| H36 · Consultar checkpoints | E6 | Entregada | T72 | #127 |
| H37 · Ingresar un anuncio de ingreso de lotes | E2 | Entregada (pedida por el usuario el 2026-11-24) | T78 | #109 |
| H38 · Reemplazar dispositivos del kit con dispositivos extra | E5 | Entregada (pedida por el usuario el 2026-11-24) | T79 | #112 |
| H39 · Gestionar modelos de dispositivo GPS | E10 | Entregada (urgente, del 2026-11-27) | T89, T90 | #130 |
| H40 · Gestionar dispositivos GPS | E10 | Entregada (urgente, del 2026-11-27) | T91, T92 | #133 |
| H42 · Probar la conectividad de un dispositivo GPS | E10 | Entregada (urgente, del 2026-11-27) | T94, T95 | #134 |
| H41 · Sincronizar los dispositivos GPS con 24HM | E10 | Descartada el 2026-11-27 | T96 | #136 |

**Correcciones y mejoras de historias ya entregadas**

| Corrección o mejora | Historias | Tarea | PR |
| --- | --- | --- | --- |
| Naviera de SIGLA 1.0 buscada por pasos y recordada | H17 | T74 | #101 |
| Cantidad al ingresar un lote | H07, H28 | T75 | #102 |
| La funda como dispositivo del kit | H06, H13, H14 | T76 | #104 |
| Foto obligatoria al descartar un dispositivo | H08 | T77 | #106 |
| Migas de pan en el celular | Plantilla del panel | T80 | #114 |
| Origen de la solicitud (`source`) | H17 | T81 | #115 |
| Modo de despacho lineal o aleatorio de la naviera | H09, H14, H30 | T82 | #116 |
| Dispositivos que sobran en el kit | H17, H18 | T83 | #118 |
| Naviera en el despacho y lado de la cinta en las secciones | H12, H15 | T84 | #119 |
| Fotos de la inspección: hora de Lima, calidad en el PDF y opciones al tocar | H18, H20 | T85 | #120 |
| Miniaturas más grandes en el celular y ver o borrar desde la PC | H18 | T86 | #121 |
| PDF del despacho a granel | H30 | T87 | #124 |
| Fotos del PDF sin separarse de su título | H20 | T88 | #128 |
| Marcas numeradas sobre los dibujos del contenedor | H20 | T73 | #128, #129 |
| Una ✗ en la cinta de puerta no pide reinspección | H33 a H36 | T93 | #132 |
| El checkpoint cerrado al final del PDF de la certificación | H20, H36 | T97 | #137 |

**Avance del Product Backlog.** Ya están entregadas 40 historias: las 31 de los Sprints 1 a 3 y las 9 de este sprint. Del Product Goal solo queda H24 (roles y permisos), sin aprobar.

Épicas cerradas en el sprint: E6. Con E2, E3, E4, E5, E7, E8 y E9 ya cerradas, solo falta H24 de E1. E10 quedó abierta: tiene el catálogo, el inventario y la conectividad, pero el resto de lo conversado en la daily del 2026-11-27 todavía no tiene historias (sección 10).

## 4. Tareas

| ID | Tarea | Bloque | Horas estimadas | Horas reales | Terminada | PR |
| --- | --- | --- | --- | --- | --- | --- |
| T67 | Aprobar H33 a H36 | Compromiso | 1 | — | 2026-11-23 | #98 |
| T68 | Diseños de H33 a H36 | Compromiso | 2.5 | — | 2026-11-24 | #100 |
| T69 | H33 · Secciones de checkpoint | Compromiso | 2 | 1.75 | 2026-11-24 | #107 |
| T74 | Corrección: naviera de SIGLA 1.0 | Corrección del Sprint 3 | 2 | 0.8 | 2026-11-24 | #101 |
| T75 | Corrección: cantidad del lote | Corrección del Sprint 3 | 1 | 0.25 | 2026-11-24 | #102 |
| T76 | Corrección: funda en el kit | Corrección del Sprint 3 | 3 | 0.6 | 2026-11-24 | #104 |
| T77 | Corrección: foto al descartar | Corrección del Sprint 3 | 1.5 | 1.4 | 2026-11-24 | #106 |
| T78 | H37 · Anuncio de ingreso de lotes | Agregada el 2026-11-24 | 4 | 2.5 | 2026-11-24 | #109 |
| T70 | H34 · Hacer el checkpoint | Compromiso | 3 | — | 2026-11-25 | #123 |
| T79 | H38 · Reemplazo con extras | Agregada el 2026-11-24 | 4.5 | 0.5 | 2026-11-25 | #112 |
| T80 | Corrección: migas en el celular | Agregada el 2026-11-25 | 0.5 | 0.2 | 2026-11-25 | #114 |
| T81 | Corrección: origen de la solicitud | Agregada el 2026-11-25 | 0.75 | 0.25 | 2026-11-25 | #115 |
| T82 | Modo de despacho de la naviera | Agregada el 2026-11-25 | 3 | 0.6 | 2026-11-25 | #116 |
| T83 | Corrección: sobrantes del kit | Agregada el 2026-11-25 | 1 | 0.3 | 2026-11-25 | #118 |
| T84 | Corrección: naviera y lado de la cinta | Agregada el 2026-11-25 | 1 | 0.2 | 2026-11-25 | #119 |
| T85 | Corrección: fotos de la inspección | Agregada el 2026-11-25 | 2 | 0.25 | 2026-11-25 | #120 |
| T86 | Corrección: ver o borrar desde la PC | Agregada el 2026-11-25 | 1 | 0.1 | 2026-11-25 | #121 |
| T71 | H35 · Reinspección | Compromiso | 3 | 2.25 | 2026-11-26 | #125 |
| T72 | H36 · Consultar checkpoints | Compromiso | 1.5 | 1.25 | 2026-11-26 | #127 |
| T87 | Corrección: PDF del despacho a granel | Agregada el 2026-11-26 | 2 | 0.3 | 2026-11-26 | #124 |
| T73 | Mejora: marcas numeradas en el PDF | Adelanto opcional | 2 | 0.4 | 2026-11-27 | #128, #129 |
| T88 | Corrección: fotos del PDF con su título | Agregada el 2026-11-27 | 1 | 0.25 | 2026-11-27 | #128 |
| T89 | Aprobar H39 | Agregada el 2026-11-27 | 0.25 | (con T90) | 2026-11-27 | #130 |
| T90 | H39 · Modelos GPS | Agregada el 2026-11-27 | 1.5 | 0.8, con T89 | 2026-11-27 | #130 |
| T91 | Aprobar H40 | Agregada el 2026-11-27 | 0.25 | (con T92) | 2026-11-27 | #133 |
| T92 | H40 · Dispositivos GPS | Agregada el 2026-11-27 | 2 | 0.6, con T91 | 2026-11-27 | #133 |
| T93 | Corrección: cinta de puerta sin reinspección | Agregada el 2026-11-27 | 2.5 | 5 | 2026-11-27 | #132 |
| T94 | Aprobar H41 y H42 | Agregada el 2026-11-27 | 0.25 | (con T95) | 2026-11-27 | #134 |
| T95 | H42 · Probar la conectividad | Agregada el 2026-11-27 | 2 | 0.25, con T94 | 2026-11-27 | #134 |
| T97 | Mejora: checkpoint en el PDF | Agregada el 2026-11-27 | 3 | 1.25 | 2026-11-27 | #137 |
| ~~T96~~ | ~~H41 · Sincronizar con 24HM~~ | Agregada el 2026-11-27 | ~~2~~ | — | Descartada el 2026-11-27 | #136 |

Las aprobaciones T89, T91 y T94 se hicieron en la rama de su historia, así que sus horas reales van con T90, T92 y T95. Las horas de T79 son solo de la construcción: el diseño y las decisiones previas no tienen commits.

Otros PR del sprint, sin tarea propia: #97 (rule del lienzo para toda vista), #99 (T74 a T77 sumadas al sprint), #103, #105, #110 y #122 (marcas de T75, T76, T77 y T86), #108 (`package.json` versionado), #111 (H38 en el backlog y en el sprint), #113 (T80 sumada al sprint), #117 (`LocalTestingSeeder` solo en local), #126, #131 y #135 (número del PR de T71, T90 y T95).

## 5. Horas estimadas contra reales

| Grupo | Tareas | Estimadas | Reales |
| --- | --- | --- | --- |
| Con horas reales | 27 tareas (T89, T91 y T94 con su historia) | 48.5 h | unas 22 h |
| Sin horas reales (aprobación, diseño en el lienzo y T70) | T67, T68 y T70 | 6.5 h | sin medir |
| **Total** | 30 tareas | **55 h** | — |

En las tareas medidas, las horas reales son **menos de la mitad** de las estimadas (22 de 48.5 h, 45 %). Sin T93, que llevó el doble de lo estimado, son 17 de 46 h (37 %), cerca del 34 % del Sprint 3.

**Ojo con esta lectura.**
- Las correcciones chicas (T80 a T88) llevaron casi siempre menos de un cuarto de lo estimado: 2.45 h reales en 12.25 h estimadas. Ahí está la mayor diferencia.
- Las historias de E6 se acercaron más a lo estimado: T69, T71 y T72 llevaron 5.25 de 6.5 h (81 %), con la prueba del usuario incluida.
- T93 llevó 5 h frente a 2.5 h porque la prueba del usuario sumó dos correcciones que van en el mismo PR (las horas en hora de Lima y las fotos del checkpoint como las de la inspección, unas 1.75 h entre las dos).
- No incluye el tiempo del Scrum Master fuera de las ramas: el diseño en el lienzo, las preguntas antes de crear la rama y las revisiones. En T67 pasaron unas 5.75 h de reloj, con pausas, sin un commit.
- Varias tareas se contaron hasta el último commit o hasta el merge, no hasta el visto bueno.

Dos sprints seguidos con datos apuntan a lo mismo: las estimaciones del trabajo de Claude Code están altas, sobre todo en las correcciones.

## 6. Avance por día

| Día | Horas estimadas terminadas | Acumulado |
| --- | --- | --- |
| Lunes 23 | 1 | 1 |
| Martes 24 | 16 | 17 |
| Miércoles 25 | 16.75 | 33.75 |
| Jueves 26 | 6.5 | 40.25 |
| Viernes 27 | 14.75 | 55 |

El compromiso (13 h) quedó terminado el jueves 26, con H36. El martes y el miércoles concentraron seis de cada diez horas del sprint: las cuatro correcciones del Sprint 3, H37, H38 y siete correcciones del miércoles. El viernes sumó 10 tareas y una épica nueva (E10).

## 7. Cambios de alcance durante el sprint

- **Lunes 23.**
  - El usuario aprobó H33 a H36 (T67) y sumó T74 a T77, las correcciones del Sprint 3.
  - Se anotó la rule de que toda vista va al lienzo y que cada corrección actualiza o crea su diseño (#97).
- **Martes 24.**
  - E6 pasó a un colega del equipo, en su rama; el usuario siguió con las correcciones desde `main`.
  - El usuario sumó H37 (anuncio de ingreso de lotes, T78) y H38 (reemplazo con dispositivos extra, T79), que cambian historias ya entregadas.
  - El usuario cambió el menú de Checkpoint al aprobar los diseños de E6 (T68).
- **Miércoles 25.** Siete tareas nuevas: T80 (la encontró Claude Code al revisar H38), T81, T82, T83, T84, T85 y T86 (esta última al probar T85).
- **Jueves 26.** El usuario sumó T87, el PDF del despacho a granel.
- **Viernes 27, último día.** Todo lo que entró el viernes lo marcó el usuario como urgente (acuerdo 3):
  - T88, las fotos del PDF con su título.
  - De la daily con el equipo y los interesados salió E10, con H39 (T89 y T90), H40 (T91 y T92) y H41 y H42 (T94 a T96).
  - De una reunión con el equipo salió T93, la cinta de puerta sin reinspección.
  - Después del merge de T93, T97: el checkpoint en el PDF de la certificación.
  - El usuario descartó H41 (T96), con su diseño sin aprobar.

Con eso el sprint sumó 55 h estimadas contra unas 18 h de capacidad para tareas (306 %). Como en los Sprints 2 y 3, el exceso es **trabajo agregado después del plan**, y el compromiso de 13 h se cumplió. Esta vez la capacidad no contó al colega que construyó E6, así que el 306 % compara con menos personas de las que trabajaron.

## 8. Decisiones tomadas en el sprint

- **Checkpoint (E6).**
  - Las secciones de checkpoint son un tipo nuevo del catálogo, enlazadas a secciones internas o externas. El catálogo de secciones (17 internas, 16 externas y 12 de checkpoint) entra con una migración de datos, porque el despliegue no corre seeders (T69).
  - La reinspección rehace solo las secciones enlazadas a las series con ✗, con los dispositivos nuevos de un despacho a granel por Operación (H35).
  - Un paso puede marcarse «Una ✗ aquí no pide reinspección», y el checkpoint se cierra entonces como «Cerrado con observación» (T93).
  - El checkpoint no tiene PDF propio: cerrado, va al final del PDF de la certificación (T97).
  - «A medias» pasó a «En proceso» en todo el sistema (T72).
- **Lotes y kits.**
  - Un anuncio de ingreso de lotes trae n lotes y cada lote n dispositivos, con un tope de 10 000 series por anuncio (H37).
  - La funda es un dispositivo del kit, de la categoría Funda; un tipo de kit lleva como máximo una (T76).
  - Cada naviera tiene un modo de despacho, lineal o aleatorio; en lineal los dispositivos salen en orden de correlativo, al armar kits y al despachar a granel (T82).
  - Los dispositivos que sobran en el kit quedan sin sección: los lleva el conductor (T83).
  - El descarte pide una foto obligatoria (T77).
- **Certificación.**
  - La naviera de SIGLA 1.0 se busca por pasos y la elección del inspector queda recordada; ya no se crean navieras desde la asignación (T74).
  - La certificación guarda el origen de su solicitud (`api` o `web`); por ahora solo `api` (T81).
  - Un dispositivo del kit se reemplaza por uno extra, despachado a granel para esa asignación, con motivo y foto (H38).
  - Las horas se muestran en hora de Lima y el PDF arma las fotos desde la foto completa (T85, T93).
  - Cada sección tiene un lugar fijo en los dibujos del contenedor (`ContainerDrawing`), marcado con su número en negro (T73).
- **Candados GPS (E10).**
  - El catálogo de modelos tiene una categoría fija en el código y su marca es un proveedor de E8 (H39).
  - Los dispositivos GPS se registran a mano; no se sincronizan con 24HM (H40, H41 descartada).
  - El código consulta una plataforma de monitoreo genérica, con 24HM como la primera, y su `FechaHora` llega en UTC (H42).
- **Rules nuevas o cambiadas:** toda vista va al lienzo, y cada corrección actualiza o crea su diseño antes de construir (#97).

## 9. Retrospectiva

Para conversar con el Scrum Master. Son lecturas de los datos; la retrospectiva la decide el equipo.

### 9.1 Observaciones

**Lo que funcionó**
- **Sprint Goal y compromiso:** E6 quedó cerrada el jueves 26 y, con ella, todas las épicas del Product Goal salvo H24.
- **Trabajo en paralelo:** el colega construyó E6 en sus ramas mientras el usuario corregía desde `main`, sin esperar merges.
- **Horas reales:** se registraron en 27 de 30 tareas (en el Sprint 3, en 18 de 21), con la creación de la rama como inicio (acuerdo 2).
- **Aprobar antes de construir:** las seis historias nuevas cerraron sus suposiciones antes de construirse, y cada pantalla tuvo su diseño aprobado en el lienzo (acuerdos 3 y rule #97).
- **Estimaciones más bajas:** el compromiso bajó de 19 h a 13 h (acuerdo 1) y se terminó un día antes del cierre.

**Lo que se puede mejorar**
- **El último día, otra vez cargado.** El viernes entraron 10 tareas y una épica nueva, todas marcadas como urgentes. El acuerdo 3 se cumplió en la forma, pero la excepción fue la regla: casi 15 h del sprint se pidieron y se hicieron el último día.
- **Números de tarea que chocan.** Con dos personas sumando tareas al sprint en ramas distintas, T88, T93 y T94 se usaron dos veces. Hubo que renumerar al traer `main` (T93 a T97), y las ramas y los commits quedaron con el número viejo.
- **Marcas fuera de su PR.** Siete PR (#103, #105, #110, #122, #126, #131 y #135) solo anotaron la marca o el número de un PR que se fusionó antes. El acuerdo 4 evita números corridos, pero suma un PR por cada tarea que se fusiona rápido.
- **Correcciones dentro de otra tarea.** T93 llevó dos correcciones que salieron al probar y no tienen tarea propia. Sigue pendiente decidir si se anotan aparte.
- **Revisión en el navegador.** Claude Code no pudo abrir `sigla-tos.test` desde el navegador conectado en E6, ni achicar Chrome a 390 px en T90; el usuario hizo esas revisiones (acuerdo 5). Quedan pendientes las de T90 y T92 a 390 px y la foto del contenedor de T70 con un contenedor real.
- **Capacidad sin el colega.** El sprint se planificó con 20 h (Scrum Master y asistente), pero un colega construyó E6, T93 y T97.

### 9.2 Acuerdos propuestos para el Sprint 5

Por confirmar con el Scrum Master.

1. **Capacidad con todo el equipo.** Sumar las horas del colega a la capacidad, y seguir bajando las estimaciones de Claude Code, sobre todo en las correcciones (unas 2.45 h reales en 12.25 h estimadas).
2. **Número de tarea en `main` primero.** Antes de crear la rama de una tarea nueva, se suma la tarea al sprint en `main`, para que dos personas no tomen el mismo número.
3. **La marca en el mismo PR.** Después de abrir el PR, Claude Code suma la marca con su número en la misma rama, y el merge se hace después. Así no hace falta un PR solo para la marca.
4. **Épicas nuevas, no el último día.** Lo que llegue el último día pasa al siguiente sprint (acuerdo 3); si el Scrum Master lo marca urgente, entra solo si cabe en una historia chica, y una épica nueva se planifica en el sprint siguiente.
5. **Lo que sale al probar.** Una corrección que aparece en la prueba y no es un criterio de la historia se anota como tarea propia antes de construirla.

## 10. Lo que pasa al Sprint 5

**Para construir**
- H24 · Roles y permisos: sin aprobar, con E6 ya probado. Debe decidir también qué rol anula asignaciones (H32), qué rol descarta dispositivos (T65) y el permiso especial para reabrir inspecciones (daily del 2026-11-27).

**Para armar como historias (daily del 2026-11-27, E10)**
- El despacho del candado desde bodega, vinculado a un kit, y el candado como dato del despacho.
- Reasignar un candado desde bodega si falla la inspección.
- El aviso en la certificación para instalar el candado y en el checkpoint para retirarlo, con su bitácora desde la instalación hasta el retiro.
- La consulta con los hitos (certificación, instalación, checkpoint, reinspección, reasignación y retiro) y su detalle.
- El diseño del dashboard y de los reportes gerenciales.
- Cuánto se guardan las fotos de la app (de 1 a 2 años como máximo, por confirmar) y la tarea programada que borre las más antiguas.

**Por decidir**
- Si las dos correcciones que salieron al probar T93 (hora de Lima y fotos del checkpoint, unas 1.75 h) se anotan como tareas aparte.
- Lo que falta del documento «Proceso de certificación de contenedores»: la cantidad de fotos por ítem, las fotos extra al final de cada inspección, las cintas numeradas y los tipos de dispositivo que nombra y aún no existen (bolsa de seguridad, precinto GPS y precintos de vacío).
- El módulo de solicitudes de SIGLA 2.0, para pasar el origen a `web` (T81).
- Las horas estimadas de T78 a T92, T94 y T95 fueron propuesta de Claude Code; no todas tienen la aprobación del usuario anotada.

**Abiertas desde antes**
- Los filtros de naviera de las tablas de kits, anuncios y certificaciones, que muestran solo el código (fuera de T84).
- Licencia de la plantilla de ThemeForest: revisar qué permite antes de usarla como referencia en producción. Arrastrada del Sprint 1.
- Al terminar la prueba con el cliente: correr `migrate:fresh` en producción y solo los seeders necesarios (T57, y la decisión de partir de cero de T76 y T78).
- Revisar a 390 px las pantallas de T90 y T92, y la foto del contenedor de T70 con un contenedor real.
