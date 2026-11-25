# Sprint 4 · SIGLA 2.0 (TOS)

**Estado:** aprobado por el Scrum Master el 2026-11-23.
**Fechas:** lunes 23 al viernes 27 de noviembre de 2026 (1 semana).
**Capacidad:** 20 horas en total (10 del Scrum Master y 10 del asistente desarrollador), como en el Sprint 3 (confirmado por el usuario el 2026-11-23). Unas 2 horas se reservan para los eventos del sprint; quedan unas 18 para tareas.
**Unidad de estimación:** horas.
**Repositorio:** https://github.com/Cerati1998/SIGLA-TOS.git
**Lienzo de Claude Design:** https://claude.ai/artifact/3zgDe8hgTDesp32V3Shhca
**Sprint anterior:** `docs/sprint-3.md` y su reporte `docs/sprint-3-report.md`.

## Dónde quedamos
Al cierre del Sprint 3 están en `main` 31 historias: E1 (menos H24), E2, E3, E4, E5, E7, E8 y E9 completas. Falta E6 (checkpoint), que pasó del Sprint 3, y H24 (roles y permisos), que se construye al culminar E6.

El 2026-11-23 el usuario armó con Claude Code las historias de E6 a partir del video del checkpoint actual (`documentation/checkpoint.mp4`): H33 a H36, en `docs/product-backlog.md`. Sus suposiciones se cerraron y el usuario las aprobó ese mismo día (T67). H24 pasa al Sprint 5, con E6 ya probado (decisión del usuario del 2026-11-23).

## Sprint Goal
Dejar construido el checkpoint: las secciones de checkpoint en el catálogo, el paso a paso que verifica las cintas y sellos de una unidad certificada, la reinspección cuando algo falla y la consulta de los checkpoints. Con esto se cierra E6.

## Historias del sprint
- **H33** · Secciones de checkpoint (T69).
- **H34** · Hacer el checkpoint de una unidad (T70).
- **H35** · Reinspección desde el checkpoint (T71).
- **H36** · Consultar checkpoints (T72).
- **Adelanto opcional:** mejora de H20, las marcas numeradas sobre los dibujos del contenedor en el PDF de la certificación (T73).
- **Corrección del Sprint 3** (agregada el 2026-11-23 a pedido del usuario): la naviera de SIGLA 1.0 en la asignación (H17, T74), la cantidad al ingresar un lote (H07 y H28, T75), la funda como dispositivo del kit (H06, H13 y H14, T76) y la foto al descartar un dispositivo (H08, T77).
- **H37** · Ingresar un anuncio de ingreso de lotes (T78), agregada el 2026-11-24 a pedido del usuario: un anuncio de ingreso de lotes trae n lotes y cada lote, n dispositivos. Cambia H07, H08, H28 y H29.
- **H38** · Reemplazar dispositivos del kit con dispositivos extra (T79), agregada el 2026-11-24 a pedido del usuario: el inspector ve los dispositivos despachados a granel para su asignación y reemplaza con ellos uno del kit, con su registro. Cambia H18, H19, H20 y H30, y la usa H35.

Sigue el orden de épicas aprobado: E6 es la última. H24 queda para el Sprint 5.

## Acuerdos de la retrospectiva del Sprint 3
Propuestos en `docs/sprint-3-report.md` (sección 9.2) y confirmados por el usuario el 2026-11-23.
1. **Capacidad.** Con unas 15.75 h reales en 46.5 h estimadas, se planifica con estimaciones más bajas para el trabajo de Claude Code y se sigue midiendo.
2. **Horas reales desde el inicio.** El primer commit se hace apenas se crea la rama, para contar las horas desde ahí.
3. **Pedidos del último día.** Lo que llegue el último día del sprint pasa al siguiente, salvo que el Scrum Master lo marque como urgente.
4. **Número de PR.** El número del PR se anota en el sprint después de abrirlo, no antes.
5. **Revisión en el navegador.** Antes de pedir el visto bueno de una pantalla con cambios de estilo, se revisa en el navegador, en escritorio y a 390 px.

## Tareas
Marca de avance: `[ ]` pendiente, `[x]` terminada. Las tareas siguen desde T67.

### Compromiso
- [x] **T67** · Aprobar H33 a H36: cerrar sus suposiciones por confirmar (el orden de las secciones de checkpoint, el enlace a una sección desactivada, los seeders de secciones, un solo checkpoint por certificación, qué pasa con el dispositivo reemplazado y con las fotos anteriores, y si el checkpoint verifica de nuevo solo las series reemplazadas). Quién: Scrum Master. 1 h. Frena a T68 y las siguientes. (Hecho el 2026-11-23 con Claude Code, PR #98. Sin horas reales medidas: el primer commit llegó con la aprobación, contra el acuerdo 2. Desde la revisión del video, a las 12:20, hasta la aprobación, a las 18:00, pasaron unas 5.75 h de reloj, con pausas. H33 a H36 quedaron aprobadas; al revisarlas se sumaron los seeders de secciones a T69 y el diseño de H36 a T68.)
- [x] **T68** · Diseñar y aprobar en el lienzo las secciones de checkpoint (H33), el paso a paso del checkpoint con su verificación ✓/✗ (H34), la reinspección (H35), y el listado y el detalle de los checkpoints (H36). Quién: Scrum Master con Claude Design. 2.5 h (subió de 2 h: el usuario pidió el 2026-11-23 que H36 tenga diseño propio). Después de T67. (Hecho el 2026-11-24 con Claude Code, PR #100. Sin horas reales medidas: el trabajo fue en el lienzo, sin rama, como T47 y T58. Se aprobó en tres rondas: el recorrido del checkpoint el 2026-11-23, la reinspección y la sección de checkpoint con la consulta el 2026-11-24. El usuario cambió el menú: Checkpoint pasa a ser un desplegable con «Registrar checkpoint» y «Checkpoints», y H34 se ajustó en el backlog. Por la regla del lienzo se actualizaron también el listado de secciones (E3) y el detalle de la certificación (E5).)
- [x] **T69** · H33 · Secciones de checkpoint. Incluye el tipo nuevo en el catálogo, el enlace a secciones internas o externas, los seeders de secciones (17 internas y 16 externas del documento «Proceso de certificación de contenedores» y 12 de checkpoint del video, con sus enlaces) y deja `docs/database.md` al día. Quién: Claude Code. 2 h (subió de 1.5 h al sumar los seeders, decididos en T67). Empieza cuando T67 y T68 estén cerradas. (Hecho el 2026-11-24 con Claude Code, PR #107. Unas 1.75 h reales de reloj, de las 09:41 a las 11:30, con la decisión del contenedor de H34 en medio; el visto bueno del usuario llegó a las 14:09. El catálogo entra con una migración de datos, porque el deploy no corre seeders. La revisión en el navegador la hizo el usuario: Claude Code no pudo abrir `sigla-tos.test` desde el navegador conectado.)
- [ ] **T70** · H34 · Hacer el checkpoint de una unidad. Reusa la toma y subida de fotos de H18. Quién: Claude Code. 3 h. Parte de la rama de T69.
- [ ] **T71** · H35 · Reinspección desde el checkpoint. Quién: Claude Code. 3 h. Parte de la rama de T70.
- [ ] **T72** · H36 · Consultar checkpoints. Quién: Claude Code. 1.5 h. Parte de la rama de T71.

### Adelanto opcional
- [ ] **T73** · H20 · Mejora: marcas numeradas sobre los dibujos del contenedor en el PDF de la certificación, en el lugar de cada sección. Pasó del Sprint 3. Quién: Claude Code. 2 h. Solo si sobra capacidad. Antes falta su diseño en el lienzo.

### Corrección del Sprint 3 (agregada el 2026-11-23)
Pedidas por el usuario el 2026-11-23, al revisar lo construido en el Sprint 3. Cada una corrige primero su diseño en el lienzo, o lo crea si la vista no tiene uno, y se aprueba antes de construir (rule «Diseño en el lienzo»). Las horas son una propuesta de Claude Code, aprobada por el usuario el 2026-11-23.
- [x] **T74** · H17 · Naviera de SIGLA 1.0 en la asignación. Al buscar la solicitud, la naviera que trae SIGLA 1.0 (por ejemplo `CHIQUITA`) se busca en la tabla maestra por pasos: el nombre de SIGLA 1.0 ya recordado, el nombre o el código exactos, el nombre que empieza con ese texto y el que lo contiene; se toma solo si hay una única coincidencia. Si no hay ninguna, o hay varias, el inspector elige la naviera en la asignación, y su elección queda recordada en la línea (`sigla1_name`) para la próxima vez. Ya no se crean navieras desde la asignación. El kit se valida contra esa naviera desde que se busca, y la asignación guarda su FK. Incluye corregir el diseño de la página E5 del lienzo (el aviso y el selector) y proponer el cambio de H17 en el backlog. Quién: Claude Code. 2 h. (Hecho el 2026-11-24, PR #101, unas 0.8 h reales según los commits: de 18:40 a 19:02 del 23 y la mañana del 24 hasta el merge, sin contar la noche. La marca va en el PR de T75 porque el PR #101 se fusionó antes de anotarla. Al construir se sumó a `LocalTestingSeeder`, que ahora corre solo en local, kits de Chiquita y Seatrade para probar.)
- [x] **T75** · H07 y H28 · Cantidad al ingresar un lote. El formulario del lote suma el campo Cantidad: al llenarlo, el correlativo final se calcula desde el inicial. Incluye corregir el diseño del formulario en la página E2 del lienzo. Quién: Claude Code. 1 h. (Hecho el 2026-11-24, PR #102, unas 0.25 h reales según los commits, de 10:07 al merge de las 10:20. La marca va en un PR aparte porque el PR #102 se fusionó antes de anotarla.)
- [x] **T76** · H06, H13 y H14 · La funda como dispositivo del kit. Hay una categoría nueva de tipo de dispositivo, Funda. Un tipo de kit lleva como máximo un tipo de dispositivo Funda. Al crear un kit, la funda que se escanea en la cabecera debe ser un dispositivo de un lote de tipo Funda y estar Disponible, y entra sola en el contenido del kit, en el tipo de dispositivo Funda. Incluye corregir los diseños de la página E4 del lienzo. Antes de construir hay que decidir qué pasa con los kits que ya existen, confirmar que «lote de tipo bolsa» es un lote de categoría Funda y que la funda no se reparte entre las secciones al asignar (H17, criterio 16). Quién: Claude Code. 3 h. (Hecho el 2026-11-24, PR #104, unas 0.6 h reales: del primer commit, a las 10:31, al merge de las 11:06. Las tres decisiones las tomó el usuario el 2026-11-24: se parte de cero con migrate:fresh, todo es tipo Funda y la funda no se reparte en las secciones. La marca va en un PR aparte porque el PR #104 se fusionó antes de anotarla.)
- [x] **T77** · H08 · Foto al descartar un dispositivo. Además del motivo, el descarte permite tomar una foto con la cámara, comprimida en el navegador como en H18, con un visor para verla en grande, quitarla o tomarla de nuevo antes de confirmar. Incluye crear en la página E2 del lienzo el diseño del modal de descarte con la foto, que hoy no tiene. Falta decidir si la foto es obligatoria y si también se pide al descartar por anulación (H32). Quién: Claude Code. 1.5 h. (Hecho el 2026-11-24, PR #106, unas 1.4 h reales: del primer commit, a las 11:36, al merge de las 12:01, más el diseño y las decisiones previas desde las 10:50. Decisiones del usuario del 2026-11-24: la foto es obligatoria; al anular e inhabilitar el kit se pide una sola foto, que queda en el descarte de cada dispositivo, y los Descartados tienen «Ver descarte» con el motivo y la foto. La marca va en un PR aparte porque el PR #106 se fusionó antes de anotarla.)

### Historia nueva (agregada el 2026-11-24)
Pedida por el usuario el 2026-11-23 como nota del sprint y armada con él el 2026-11-24. Las horas son una propuesta de Claude Code.
- [x] **T78** · H37 · Ingresar un anuncio de ingreso de lotes. El anuncio de ingreso de lotes es la cabecera (naviera, proveedor, fecha y guía) y trae uno o más lotes, cada uno con su tipo, su serie, sus correlativos y sus observaciones; todo se guarda junto, con un tope de 10 000 series por anuncio. Bodega consulta la lista de anuncios, el detalle de cada anuncio con sus lotes y el detalle de cada lote con sus dispositivos. Cambia H07, H08, H28 y H29. Se parte de cero con `migrate:fresh` y deja `docs/database.md` y `docs/glossary.md` al día. Incluye diseñar el ingreso, la lista y el detalle del anuncio en la página E2 del lienzo y que el usuario los apruebe antes de construir. Quién: Claude Code. 4 h. (Hecho el 2026-11-24, PR #109, unas 2.5 h reales: del primer commit, a las 12:59, al visto bueno, a las 15:27, con el diseño y las correcciones de la prueba. La migración conserva los datos, aunque se decidió partir de cero con `migrate:fresh`. Correcciones del usuario al probar: el menú dice «Ingreso de lotes» y los títulos «Anuncios de ingreso de lotes»; en el celular, los lotes del detalle del anuncio van en tarjetas, solo en esa vista.)
- [x] **T79** · H38 · Dispositivos extra y reemplazo en la asignación. La inspección suma «Dispositivos extra» para reemplazar un dispositivo del kit por uno despachado a granel para esa asignación, del mismo tipo, con motivo y foto del descarte; el que sale pasa a Descartado y la sección pide una foto nueva. El PDF y el detalle muestran el nuevo, y el detalle suma «Dispositivos reemplazados». Incluye el cambio de H30, criterio 3 (por Operación se ofrecen las certificaciones en curso, reabiertas y cerradas), y el diseño en la página E5 del lienzo, que el usuario aprueba antes de construir. Quién: Claude Code. 4.5 h (1 h de diseño y 3.5 h de construcción). Va antes de T71. (Hecho el 2026-11-25, PR #112, unas 0.5 h reales de construcción según los commits: de 18:01 a 18:18 del 24 y la revisión en el navegador de la mañana del 25, sin contar la noche; el diseño y las decisiones previas, desde las 16:30 del 24, no tienen commits. Al construir se decidió que el modal ofrece solo los dispositivos de la inspección abierta y que el PDF lleva solo las fotos tomadas después del reemplazo; quedan anotados en H38.)

### Corrección encontrada al revisar H38 (agregada el 2026-11-25)
Encontrada por Claude Code al revisar H38 en el navegador y agregada al sprint a pedido del usuario el 2026-11-25. Las horas son una propuesta de Claude Code.
- [x] **T80** · Plantilla del panel · Migas de pan en el celular. A 390 px, las migas de pan del encabezado de la pantalla (`x-panel.page-header`) no se acomodan al ancho cuando son largas y la página se desplaza de lado: en el detalle de una certificación («Certificaciones — Asignaciones — ABCU3800001») se salen unos 24 px. Que bajen de línea o se recorten sin desplazar la página, en todas las pantallas que las usan, revisadas en el navegador a 390 px y en escritorio. Si cambia cómo se ven, primero se corrige la plantilla del panel en el lienzo y se aprueba. Quién: Claude Code. 0.5 h. (Hecho el 2026-11-25, PR #114, unas 0.2 h reales: del primer commit, a las 09:03, al visto bueno, a las 09:13. Las migas bajan de línea con `flex-wrap` en el encabezado común, sin cambiar su aspecto, así que la plantilla del lienzo no cambió. Revisadas a 390 px las 35 pantallas de la intranet con migas: ninguna se desplaza de lado y en 8 bajan a una segunda línea.)

## Total estimado
| Bloque | Tareas | Horas |
| --- | --- | --- |
| Compromiso | T67, T68, T69, T70, T71, T72 | 13 h |
| Corrección del Sprint 3 (agregada el 2026-11-23) | T74, T75, T76, T77 | 7.5 h |
| Historia nueva (agregada el 2026-11-24) | T78, T79 | 8.5 h |
| Corrección encontrada al revisar H38 (agregada el 2026-11-25) | T80 | 0.5 h |
| Adelanto opcional | T73 | 2 h |
| **Total** | | **31.5 h** |

Capacidad para tareas: unas 18 h. El compromiso deja unas 5 h de margen, por el acuerdo 1 y porque H35 (la reinspección) toca la certificación ya construida. Si el tiempo no alcanza, se corta desde abajo: T72 (H36) es la primera en pasar al Sprint 5. T74 a T77 suman 7.5 h (20.5 h sin el adelanto opcional, 22.5 h con él), por decisión del usuario del 2026-11-23: el compromiso con las correcciones pasa la capacidad en unas 2.5 h. Si el tiempo no alcanza, el usuario decide qué se corta. T78 suma 4 h más (24.5 h sin el adelanto opcional), por decisión del usuario del 2026-11-24; las correcciones T74 a T77 llevaron unas 3 h reales de las 7.5 h estimadas. T79 suma 4.5 h más (29 h sin el adelanto opcional), por decisión del usuario del 2026-11-24; si el tiempo no alcanza, T72 (H36) pasa al Sprint 5. T80 suma 0.5 h más (29.5 h sin el adelanto opcional), por decisión del usuario del 2026-11-25.

## Orden de trabajo
Ninguna tarea espera un merge. Lo único que frena el trabajo es una decisión pendiente (T67), un diseño sin aprobar (T68) o el visto bueno de la prueba de una historia.

| Orden | Tarea | Rama | Parte de | Espera |
| --- | --- | --- | --- | --- |
| 1 | T67 · Aprobar H33 a H36 | — | — | nada |
| 2 | T68 · Diseños de H33, H34, H35 y H36 | — | — | T67 |
| 3 | T69 · H33 | `feature/H33-secciones-checkpoint` | `main` | T67 y T68 |
| 4 | T70 · H34 | `feature/H34-hacer-checkpoint` | rama de T69 | nada |
| 5 | T71 · H35 | `feature/H35-reinspeccion` | rama de T70 | nada |
| 6 | T72 · H36 | `feature/H36-consultar-checkpoints` | rama de T71 | nada |
| 7 | T73 · Marcas numeradas en el PDF | `feature/H20-marcas-numeradas` | `main` | diseño aprobado |
| — | T74 · H17 · Naviera de SIGLA 1.0 | `fix/T74-H17-naviera-sigla1` | `main` | diseño aprobado |
| — | T75 · H07 y H28 · Cantidad del lote | `fix/T75-H07-cantidad-lote` | `main` | diseño aprobado |
| — | T76 · H06, H13 y H14 · Funda en el kit | `fix/T76-funda-en-kit` | `main` | decisiones de T76 y diseño aprobado |
| — | T77 · H08 · Foto al descartar | `fix/T77-H08-foto-descarte` | `main` | decisiones de T77 y diseño aprobado |
| — | T78 · H37 · Anuncio de lotes | `fix/T78-anuncio-de-lotes` | `main` | diseño aprobado |
| — | T79 · H38 · Reemplazo con extras | `feature/H38-reemplazo-dispositivos` | `main` | diseño aprobado; antes de T71 |
| — | T80 · Migas de pan en el celular | `fix/T80-migas-celular` | `main` | nada, salvo que cambie el diseño de la plantilla |

- **Ramas encadenadas.** Cada historia de E6 parte de la anterior porque usa lo que esta deja: las secciones de checkpoint de H33, el checkpoint de H34 y la reinspección de H35. Su PR va hacia `main` y, mientras la base no se mergee, muestra también los commits de la base.
- **Orden de merge:** T69, T70, T71 y T72, en ese orden.
- **Si la tarea base cambia** por la revisión, Claude Code lleva el cambio a las ramas encadenadas antes de pedir su visto bueno.
- **Correcciones en paralelo.** T74 a T77 se trabajan en paralelo con E6, cada una en su rama desde `main` y en este orden: T74, T75, T76 y T77. No esperan a las ramas de E6 ni estas a ellas. Antes de pedir el visto bueno, cada rama trae lo último de `main`.
- **Viernes:** Sprint Review y Retrospectiva; reporte en `docs/sprint-4-report.md`, con las horas estimadas y las reales de cada tarea.

## Impedimentos y dependencias
- **H35 toca la certificación ya construida:** reabre solo algunas secciones, reemplaza dispositivos y suma el estado En reinspección al listado de Asignaciones (H20). Es la tarea con más riesgo del sprint.
- **H30 sin cambios:** la reinspección usa el despacho a granel con motivo Operación tal como está. Si al construir hace falta cambiarlo, se avisa al usuario. (Cambiado el 2026-11-24 con H38: por Operación se ofrecen también las certificaciones en curso y reabiertas, y el reemplazo de H35, criterio 5, usa el de H38.)
- **T79 toca lo que usa E6:** el despacho a granel por Operación, la inspección y el detalle de la certificación, y el reemplazo que H35 reusa. Va antes de T71; al llegar a `main`, se avisa a quien construye E6 para que traiga `main` a su rama.
- **T76 toca lo que usa E6:** el kit, su contenido y el reparto de sus dispositivos entre las secciones al asignar (H17, criterio 16), que H34 y H35 también usan. Si cambia el reparto, se avisa a quien construye E6 para que traiga `main` a su rama.
- **T78 cambia el modelo del lote:** el lote y su anuncio están debajo de los kits, la certificación y el reparto en secciones, que E6 también usa. Al llegar a `main`, se avisa a quien construye E6 para que traiga `main` a su rama.
- **Datos de prueba:** para probar el checkpoint hace falta al menos una certificación cerrada con sus dispositivos repartidos.

## Incertidumbres
- Licencia de la plantilla de ThemeForest: revisar qué permite antes de usarla como referencia en producción. Arrastrada del Sprint 1.
- Al terminar la prueba con el cliente: correr `migrate:fresh` en producción y solo los seeders necesarios (T57).
- El documento «Proceso de certificación de contenedores» (`documentation/`, entregado el 2026-11-23): sus secciones entran por seeder en T69 (decisión del usuario en T67), sin sus dispositivos, que el admin configura en pantalla (H12). Falta decidir si cambia algo más de E5 y en qué sprint: la cantidad de fotos por ítem (hoy, de 1 a n), las fotos extra al final de cada inspección, las cintas numeradas y los tipos de dispositivo que nombra y aún no existen (bolsa de seguridad, precinto GPS, precintos de vacío).

## Temas a resolver durante el sprint
- Aprobar H33 a H36 en el backlog (T67). (Resuelto el 2026-11-23: el usuario las aprobó.)
- Confirmar los acuerdos de la retrospectiva del Sprint 3 y la capacidad. (Resuelto el 2026-11-23: el usuario confirmó los cinco acuerdos y las 20 h.)
- Registrar las horas reales de cada tarea (acuerdo 1).
- Notas del usuario del 2026-11-23 que no entran como corrección: el ingreso de lotes como 1 anuncio → n lotes → n dispositivos, que cambia H07, H08, H28 y H29 y pide una historia nueva en el backlog con su diseño; y el reemplazo de un dispositivo del kit por uno de un despacho a granel, con su registro en la asignación, que es una propuesta por revisar con el equipo y se cruza con la reinspección (H35). (La primera se resolvió el 2026-11-24: es H37, aprobada por el usuario, y entra al sprint como T78. La segunda sigue pendiente: el usuario no la trabaja por ahora. Se resolvió el 2026-11-24: es H38, aprobada por el usuario, y entra al sprint como T79, antes de T71.)
