# Sprint 3 · SIGLA 2.0 (TOS)

**Estado:** aprobado por el Scrum Master el 2026-11-13.
**Fechas:** lunes 16 al viernes 20 de noviembre de 2026 (1 semana).
**Capacidad:** 20 horas en total (10 del Scrum Master y 10 del asistente desarrollador). Unas 2 horas se reservan para los eventos del sprint; quedan unas 18 para tareas.
**Unidad de estimación:** horas.
**Repositorio:** https://github.com/Cerati1998/SIGLA-TOS.git
**Lienzo de Claude Design:** https://claude.ai/artifact/3zgDe8hgTDesp32V3Shhca
**Sprint anterior:** `docs/sprint-2.md` y su reporte `docs/sprint-2-report.md`.

## Dónde quedamos
Al cierre del Sprint 2 están en `main` 20 historias: E1, E7, E8, E2, E3 y E9 completas, y de E4 los tipos de kit (H13), los trabajadores (H25) y las zonas de patio (H26). De las historias aprobadas faltan H14, H15 y H16 (E4) y H17 (E5). H18 a H20 y E6 siguen en revisión, y H24 se construye al culminar E6.

El 2026-11-13 el usuario sumó dos historias que no estaban al inicio:
- **H29 · Activar dispositivos para despacho (E2).** Bodega envía las series a la naviera por correo, fuera de SIGLA. Cuando la naviera responde que sí, en SIGLA se activan para despacho, por rango. La activación es obligatoria para armar un kit y para todo despacho.
- **H30 · Despachar dispositivos a granel (E4).** Es un despacho de dispositivos sueltos, sin kit. Aprobada el 2026-11-13, en T46.

## Sprint Goal
Dejar construido el recorrido del kit en bodega: activar los dispositivos para despacho por rango, armar kits solo con dispositivos activados, despacharlos a un trabajador o a granel, y consultar kits y despachos. Con esto se cierra E4.

## Historias del sprint
- **H29** · Activar dispositivos para despacho (nueva del 2026-11-13, aprobada en T46, T47 y T48).
- **H14** · Crear un kit y agregarle dispositivos (T49).
- **H15** · Despachar kits (T50).
- **H16** · Consultar kits y despachos (T51).
- **H30** · Despachar dispositivos a granel (nueva del 2026-11-13, aprobada en T46, T47 y T52).
- **H17** · Iniciar una certificación (T53) y la revisión de lo posterior a la asignación del kit (T54). Eran adelanto opcional; se sumaron al sprint el 2026-11-17 a pedido del usuario.

Sigue el orden de épicas aprobado: E1, E7, E8, E2, E3, E4, E9, E5, E6. H29 es de E2, pero se construye primero porque H14 y los despachos la necesitan.

## Acuerdos de la retrospectiva del Sprint 2
1. **Horas reales.** Al marcar una tarea se anotan, junto a la fecha y el PR, las horas entre el primer commit de su rama y el visto bueno del usuario. Ejemplo: "(Hecho el 2026-11-16, PR #61, 3.5 h reales.)".
2. **Marca en el mismo PR.** La marca de la tarea va siempre en el PR de su historia, no en uno aparte.
3. **Aprobar antes de construir.** No se construye una historia propuesta o con supuestos abiertos hasta que el usuario la apruebe. Aplica a H29 y H30.
4. **Capacidad.** Se planifica con horas estimadas y al cierre se comparan con las reales.

## Tareas
Marca de avance: `[ ]` pendiente, `[x]` terminada. Las tareas siguen desde T46.

### Compromiso
- [x] **T46** · Preparar H14, H15, H16, H29 y H30: cerrar sus "por confirmar" (los de H14 y H15 son los datos obligatorios del kit, los estados que acepta, si se guarda un kit incompleto y si se despacha uno vacío; el de H16, los estados del kit; los de H29, las reglas del rango; los de H30, a quién se despacha, cómo se eligen los dispositivos y en qué estado quedan) y revisar sus diseños en el lienzo. Quién: Scrum Master. 1.5 h. Frena a T48 y las siguientes. (Hecho el 2026-11-13 con Claude Code, PR #62, unas 0.5 h reales contadas desde la primera pregunta, porque el primer commit llegó al final. H29 y H30 quedaron aprobadas. En el lienzo no había diseños de H14, H15 ni H16: pasan a T47.)
- [x] **T47** · Diseñar y aprobar en el lienzo la activación por rango (H29), desde el lote y con serie inicial y final; el despacho a granel (H30); crear un kit con su escaneo y avance (H14), y despachar kits (H15). H16 se construye desde la plantilla "Lista con tabla", sin diseño propio. Quién: Scrum Master con Claude Design. 2 h. Después de T46. (Cambiado el 2026-11-13 en T46, a pedido del usuario: sumó H14 y H15 y pasó de 1 h a 2 h.) (Hecho el 2026-11-16, PR #65. H29, H30 y H15 se aprobaron el 2026-11-13; H14 se rediseñó a pedido del usuario y se aprobó el 2026-11-16. Sin horas reales: el trabajo fue en el lienzo, sin rama.)
- [x] **T48** · H29 · Activar dispositivos para despacho. Incluye la marca en `security_devices` y deja `docs/database.md` al día en el mismo PR. Quién: Claude Code. 3 h. Empieza cuando T46 y T47 estén cerradas. (Hecho el 2026-11-16, PR #64, unas 0.25 h reales contadas desde que se creó la rama, porque los commits llegaron al final. Se marca en el PR #65 porque el PR #64 se unió sin la marca.)
- [x] **T49** · H14 · Crear un kit y agregarle dispositivos (solo dispositivos activados). Incluye la tabla `kits` y su relación con los dispositivos. Quién: Claude Code. 4 h. Parte de la rama de T48: no espera su merge. (Hecho el 2026-11-16, PR #65, unas 2 h reales contadas desde que se creó la rama, porque los commits llegaron al final. Partió de `main`, porque T48 ya estaba unida.)
- [x] **T50** · H15 · Despachar kits. Quién: Claude Code. 3 h. Parte de la rama de T49. (Hecho el 2026-11-17, PR #68, unas 1.5 h reales contadas desde que se creó la rama, con el rediseño en el lienzo incluido. Partió de `main`, porque T49 ya estaba unida. Corrección del 2026-11-17 a pedido del usuario, PR #71, unas 0.5 h reales con el lienzo incluido: el PDF del kit muestra solo su contenido y se ofrece desde Armado; el despacho tiene su propio PDF, con sus kits por funda y su contenido.)
- [x] **T51** · H16 · Consultar kits y despachos. Quién: Claude Code. 2.5 h. Parte de la rama de T50. (Hecho el 2026-11-17, PR #75, unas 4.5 h reales contadas desde el primer commit de la rama hasta el visto bueno, como pide el acuerdo 1; en ese tiempo también se hicieron T56 y T52, y el trabajo propio de H16 fue de unas 1.5 h. Partió de `main`, porque T50 ya estaba unida. El criterio 9 cambió al rediseñar H30: los despachos a granel tienen su propia lista.)
- [x] **T52** · H30 · Despachar dispositivos a granel. Quién: Claude Code. 3 h. H30 se aprobó en T46 el 2026-11-13. Parte de la rama de T51, porque sus despachos se ven en la lista de despachos de H16 (decidido en T46). (Hecho el 2026-11-17, PR #76, unas 2 h reales contadas desde el primer commit de la rama hasta el visto bueno, con el rediseño en el lienzo incluido. Rediseñada ese día a pedido del usuario, siguiendo el despacho a granel de BUMMOCK: lista propia y no la de H16, motivo de salida con cliente o kit, patio de destino, observación, tres formas de agregar dispositivos y página de detalle sin PDF. Necesitó H31, que se sumó como T56. Al probar, el usuario pidió que «Por cantidad» acepte el prefijo de la serie.)

### Corrección del Sprint 2
- [x] **T55** · H06 y H13 · Corrección: cinta de inspección. La cinta interna y la externa son un solo tipo de dispositivo, la cinta de inspección (seeder corregido). Una casilla en el tipo de dispositivo indica si se reparte entre asignación interna y externa, y el tipo de kit pide entonces cuántas van a cada una. Quién: Claude Code. 2 h. Agregada el 2026-11-16 a pedido del usuario, antes de empezar el compromiso. (Hecho el 2026-11-16, PR #63, unas 0.5 h reales contadas desde que se creó la rama, porque los commits llegaron al final.)

### Agregada el 2026-11-17
- [x] **T56** · H31 · Gestionar clientes: copia del catálogo de proveedores (H10) con la condición de pago, Contado o Crédito, por defecto Contado. Incluye su diseño en el lienzo y deja `docs/database.md` al día. Quién: Claude Code. 2 h. Agregada el 2026-11-17 a pedido del usuario, porque H30 la necesita para el despacho a granel por venta. Va antes que T52, en su propia rama, `feature/H31-gestionar-clientes`, que parte de `main`. (Hecho el 2026-11-17, PR #74, unas 0.5 h reales contadas desde el primer commit de la rama, con el lienzo incluido. Al revisar, el usuario pidió los días de crédito: con Crédito se piden de 1 a 365 días.)

### Sumadas al sprint el 2026-11-17
Eran adelanto opcional. El usuario las sumó al sprint el 2026-11-17, con el compromiso ya terminado.
- [x] **T53** · H17 · Iniciar una certificación. Quién: Claude Code. 4 h. Antes falta decidir cómo se reconoce en el catálogo la naviera que trae la API y qué pasa si no existe (propuesta del 2026-11-10 en H17), y si un kit se usa en una sola certificación (criterio 8). (Hecho el 2026-11-18, PR #78, unas 1 h reales contadas desde que empezó el diseño en el lienzo hasta el aviso para probar, el 2026-11-17, porque los commits llegaron al final; el visto bueno llegó al día siguiente. Incluye el diseño de H17, aprobado el 2026-11-17. Las dos decisiones se tomaron ese día: la naviera se enlaza por su nombre, se reactiva si está desactivada y se crea si falta; el kit va en una sola certificación.)
- [x] **T54** · Revisar con el usuario lo posterior a la asignación del kit (H18 a H20 y E6), para que E5 y E6 se puedan estimar. Quién: Scrum Master. 1 h. (Cambiado el 2026-11-19 a pedido del usuario: E6, el checkpoint, pasa al Sprint 4; aquí se revisan solo H18 a H20, y la tarea frena a T58 y las siguientes.) (Hecho el 2026-11-20, PR #85, 1 h real. H18, H19 y H20 quedaron aprobadas con sus criterios.)

### Agregada el 2026-11-18
- [x] **T57** (Hecho el 2026-11-19, PR #79 a #83) · Despliegue continuo a producción: un workflow de GitHub Actions que, cuando las pruebas pasan en `main`, sube SIGLA por SSH al dominio final del cPanel, con su base final. Mientras dure la prueba con el cliente, esa base solo tiene datos de prueba; al terminar se corre `migrate:fresh` y solo los seeders necesarios. Lo del entorno va en un environment de GitHub: como secreto solo la clave privada SSH; el sitio, el host, el puerto, el usuario y la ruta van como variables. Quién: Claude Code; el Scrum Master genera la clave SSH y prepara el dominio, la base, el `.env` del servidor y el cron. 3 h. Agregada el 2026-11-18 a pedido del usuario, para adelantar la implementación con el cliente. Va en su propia rama, `chore/T57-despliegue`, que parte de `main`.

### Agregadas el 2026-11-19, con urgencia alta
El usuario las sumó el 2026-11-19 para dejar la certificación completa en este sprint, porque se prueba en campo con el cliente. Se trabajan el 2026-11-20. Las horas son una propuesta de Claude Code, por confirmar.
- [x] **T58** · Diseñar y aprobar en el lienzo H18, H19 y H20, a partir de la plantilla "Formulario" y de la página de la certificación iniciada de H17. Quién: Scrum Master con Claude Design. 1.5 h. Después de T54. (Hecho el 2026-11-20, PR #88. Sin horas reales medidas: el trabajo fue en el lienzo y los commits llegaron al final. Al revisar la propuesta, el usuario cambió el flujo: Inspección pasa a ser un desplegable con Certificaciones, un panel con tres tarjetas (Asignación, Inspección interna e Inspección externa); la interna y la externa se hacen en cualquier orden o en paralelo; se cierra y se reapertura, con motivo, desde el listado de Asignaciones, que es también la consulta de H20. H17 a H20 se ajustaron en el backlog en este mismo PR. Claude Code calcula unas 3 h más de construcción para T59 a T61, por confirmar.)
- [ ] **T59** · H18 · Completar las secciones con fotos. Incluye comprimir las fotos en el navegador antes de subirlas (de unos 7 MB a unos 700 KB) y deja `docs/database.md` al día. Quién: Claude Code. 5 h. Empieza cuando T54 y T58 estén cerradas. Va en su propia rama, `feature/H18-secciones-con-fotos`, que parte de `main`.
- [ ] **T60** · H19 · Cerrar la certificación. Quién: Claude Code. 2 h. Parte de la rama de T59.
- [ ] **T61** · H20 · Consultar certificaciones, con su PDF. Quién: Claude Code. 3 h. Parte de la rama de T60.

### Corrección agregada el 2026-11-20
- [x] **T62** · H06, H07, H08, H28, H29 y H30 · Corrección: tipos de dispositivo sin serie y sin aprobación. El tipo de dispositivo suma dos casillas: «No utiliza serie», desmarcada por defecto, y «Requiere aprobación en lote», marcada por defecto. Al ingresar un lote de un tipo sin serie se ocultan el inicio y el final de serie, y se piden el mínimo de dígitos de siempre y el correlativo inicial y el final: la serie es solo el correlativo con ceros. En esos tipos la serie es única dentro del tipo, no en todo SIGLA, así que la búsqueda de dispositivos muestra todas las coincidencias y el despacho a granel suma un selector de tipo opcional. Los lotes de un tipo que no requiere aprobación nacen con sus dispositivos Disponibles, listos para armar un kit o despacharlos a granel. Incluye ajustar y aprobar en el lienzo el formulario del lote y el despacho a granel, y dejar `docs/database.md` al día. Quién: Claude Code. 4 h (propuesta de Claude Code, por confirmar; subió de 3 h al sumar H08 y H30). Agregada el 2026-11-20 a pedido del usuario. Sus "por confirmar" se cerraron con el usuario el 2026-11-20 y quedaron en H06, H07, H08, H28, H29 y H30 del backlog; antes de construir falta aprobar en el lienzo el formulario del lote y el despacho a granel. Va en su propia rama, `fix/T62-tipos-sin-serie-y-sin-aprobacion`, que parte de `main`. (Hecho el 2026-11-20, PR #90, unas 0.75 h reales contadas desde que se creó la rama hasta el visto bueno, con los diseños en el lienzo incluidos, porque los commits llegaron al final. Las preguntas al usuario y su registro en el backlog fueron antes, en el PR #89. Los diseños se aprobaron el 2026-11-20. Al construir se sumó el kit (H14): con series compartidas, el escaneo busca primero en el tipo del campo.)

## Total estimado
| Bloque | Tareas | Horas |
| --- | --- | --- |
| Compromiso | T46, T47, T48, T49, T50, T51, T52 | 19 h |
| Corrección del Sprint 2 (agregada el 2026-11-16) | T55 | 2 h |
| Agregada el 2026-11-17 | T56 | 2 h |
| Sumadas el 2026-11-17 (antes adelanto opcional) | T53, T54 | 5 h |
| Agregada el 2026-11-18 | T57 | 3 h |
| Agregadas el 2026-11-19, con urgencia alta | T58, T59, T60, T61 | 11.5 h |
| Corrección agregada el 2026-11-20 | T62 | 4 h |
| **Total** | | **46.5 h** |

Capacidad para tareas: unas 18 h. El compromiso la pasa en 1 h (cambiado el 2026-11-13: T47 sumó los diseños de H14 y H15; antes eran 18 h, sin margen). Si el tiempo no alcanza, se corta desde abajo: T52 (H30) es la primera en pasar al Sprint 4. T55 suma 2 h más (21 h en total), por decisión del usuario del 2026-11-16; va antes que T48 y en su propia rama, `fix/T55-cinta-de-inspeccion`, que parte de `main`. T56 suma otras 2 h (23 h en total), por decisión del usuario del 2026-11-17: H30 la necesita. T53 y T54 suman 5 h más (28 h en total), por decisión del usuario del 2026-11-17, con el compromiso ya terminado. T57 suma 3 h más (31 h en total), por decisión del usuario del 2026-11-18. T58 a T61 suman 11.5 h más (42.5 h en total), por decisión del usuario del 2026-11-19, para dejar la certificación completa; si el viernes no alcanza, se corta desde abajo: T61 (H20) es la primera en pasar al Sprint 4. T62 suma 4 h más (46.5 h en total), por decisión del usuario del 2026-11-20, el último día del sprint; si no alcanza, pasa al Sprint 4 (lo decide el usuario).

Referencia: el Sprint 2 terminó 42.5 h estimadas contra esa misma capacidad, pero sin horas reales no se sabe cuánto rinde de verdad. Este plan se queda en la capacidad declarada hasta compararla con las horas reales al cierre (acuerdo 4).

## Orden de trabajo
Ninguna tarea espera un merge. Lo único que frena el trabajo es una decisión pendiente (T46), un diseño sin aprobar (T47) o el visto bueno de la prueba de una historia.

| Orden | Tarea | Rama | Parte de | Espera |
| --- | --- | --- | --- | --- |
| 1 | T46 · Preparar H14, H15, H16, H29 y H30 | — | — | nada |
| 2 | T47 · Diseños de H29, H30, H14 y H15 | — | — | T46 |
| 3 | T48 · H29 | `feature/H29-activar-dispositivos` | `main` | T46 y T47 |
| 4 | T49 · H14 | `feature/H14-crear-kit` | rama de T48 | nada |
| 5 | T50 · H15 | `feature/H15-despachar-kits` | rama de T49 | nada |
| 6 | T51 · H16 | `feature/H16-consultar-kits` | rama de T50 | nada |
| 7 | T56 · H31 | `feature/H31-gestionar-clientes` | `main` | diseño aprobado |
| 8 | T52 · H30 | `feature/H30-despachar-granel` | rama de T51 | H30 aprobada y T56 |
| 9 | T53 · H17 | `feature/H17-iniciar-certificacion` | `main` | decisiones de la naviera y del kit |
| 10 | T54 · Revisar lo posterior a la asignación del kit | — | — | nada |
| 11 | T57 · Despliegue continuo a producción | `chore/T57-despliegue` | `main` | clave SSH, dominio y `.env` del servidor |
| 12 | T58 · Diseños de H18, H19 y H20 | — | — | T54 |
| 13 | T59 · H18 | `feature/H18-secciones-con-fotos` | `main` | T54 y T58 |
| 14 | T60 · H19 | `feature/H19-cerrar-certificacion` | rama de T59 | nada |
| 15 | T61 · H20 | `feature/H20-consultar-certificaciones` | rama de T60 | nada |
| 16 | T62 · Tipos sin serie y sin aprobación | `fix/T62-tipos-sin-serie-y-sin-aprobacion` | `main` | diseños del lote y del despacho a granel aprobados |

- **Scrum Master, el lunes:** T46 en la mañana y T47 justo después, para que T48 no se frene.
- **Ramas encadenadas.** Cada historia de E4 parte de la anterior porque usa lo que esta deja: la marca de H29, la tabla de kits de H14 y el despacho de H15. Su PR va hacia `main` y, mientras la base no se mergee, muestra también los commits de la base.
- **Orden de merge:** T48, T49, T50, T51, T56 y T52, en ese orden.
- **Si la tarea base cambia** por la revisión, Claude Code lleva el cambio a las ramas encadenadas antes de pedir su visto bueno.
- **Viernes:** Sprint Review y Retrospectiva; reporte en `docs/sprint-3-report.md`, con las horas estimadas y las reales de cada tarea.

## Impedimentos y dependencias
- **H29 antes que todo E4:** H14 solo acepta dispositivos activados y todo despacho exige la marca. Sin H29 no se puede probar H14.
- **Dispositivos que ya existen:** los lotes ingresados antes de H29 quedan sin activar. Bodega tendrá que activarlos por rango antes de armar kits con ellos.
- **H17:** depende de la decisión sobre la naviera de la API y de H18, que sigue en revisión (criterio 10).

## Incertidumbres
- Licencia de la plantilla de ThemeForest: revisar qué permite antes de usarla como referencia en producción. Arrastrada del Sprint 1.
- Cómo se usan los dispositivos despachados a granel en la reinspección: se define al revisar E6 (T54).
- (Resueltas en T46 el 2026-11-13: el escáner funciona como teclado y termina con Enter; el despacho a granel va a un trabajador.)

## Temas a resolver durante el sprint
- Aprobar H29 y H30 en el backlog (T46). (Resuelto el 2026-11-13: el usuario las aprobó.)
- Registrar las horas reales de cada tarea (acuerdo 1).
- Lo posterior a la asignación del kit (H18 a H20 y E6) sigue en revisión (T54).
