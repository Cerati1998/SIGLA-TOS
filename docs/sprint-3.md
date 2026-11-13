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
- **H30 · Despachar dispositivos a granel (E4).** Es un despacho de dispositivos sueltos, sin kit. Es una propuesta y se aprueba antes de construirla.

## Sprint Goal
Dejar construido el recorrido del kit en bodega: activar los dispositivos para despacho por rango, armar kits solo con dispositivos activados, despacharlos a un trabajador o a granel, y consultar kits y despachos. Con esto se cierra E4.

## Historias del sprint
- **H29** · Activar dispositivos para despacho (nueva del 2026-11-13, T47 y T48).
- **H14** · Crear un kit y agregarle dispositivos (T49).
- **H15** · Despachar kits (T50).
- **H16** · Consultar kits y despachos (T51).
- **H30** · Despachar dispositivos a granel (nueva del 2026-11-13, propuesta por aprobar, T47 y T52).
- **Adelanto opcional:** H17 · Iniciar una certificación (T53) y la revisión de lo posterior a la asignación del kit (T54).

Sigue el orden de épicas aprobado: E1, E7, E8, E2, E3, E4, E9, E5, E6. H29 es de E2, pero se construye primero porque H14 y los despachos la necesitan.

## Acuerdos de la retrospectiva del Sprint 2
1. **Horas reales.** Al marcar una tarea se anotan, junto a la fecha y el PR, las horas entre el primer commit de su rama y el visto bueno del usuario. Ejemplo: "(Hecho el 2026-11-16, PR #61, 3.5 h reales.)".
2. **Marca en el mismo PR.** La marca de la tarea va siempre en el PR de su historia, no en uno aparte.
3. **Aprobar antes de construir.** No se construye una historia propuesta o con supuestos abiertos hasta que el usuario la apruebe. Aplica a H29 y H30.
4. **Capacidad.** Se planifica con horas estimadas y al cierre se comparan con las reales.

## Tareas
Marca de avance: `[ ]` pendiente, `[x]` terminada. Las tareas siguen desde T46.

### Compromiso
- [ ] **T46** · Preparar H14, H15, H16, H29 y H30: cerrar sus "por confirmar" (los de H14 y H15 son los datos obligatorios del kit, los estados que acepta, si se guarda un kit incompleto y si se despacha uno vacío; el de H16, los estados del kit; los de H29, las reglas del rango; los de H30, a quién se despacha, cómo se eligen los dispositivos y en qué estado quedan) y revisar sus diseños en el lienzo. Quién: Scrum Master. 1.5 h. Frena a T48 y las siguientes.
- [ ] **T47** · Diseñar y aprobar en el lienzo la activación por rango (H29), desde el lote y con serie inicial y final, y el despacho a granel (H30). Quién: Scrum Master con Claude Design. 1 h. Después de T46.
- [ ] **T48** · H29 · Activar dispositivos para despacho. Incluye la marca en `security_devices` y deja `docs/database.md` al día en el mismo PR. Quién: Claude Code. 3 h. Empieza cuando T46 y T47 estén cerradas.
- [ ] **T49** · H14 · Crear un kit y agregarle dispositivos (solo dispositivos activados). Incluye la tabla `kits` y su relación con los dispositivos. Quién: Claude Code. 4 h. Parte de la rama de T48: no espera su merge.
- [ ] **T50** · H15 · Despachar kits. Quién: Claude Code. 3 h. Parte de la rama de T49.
- [ ] **T51** · H16 · Consultar kits y despachos. Quién: Claude Code. 2.5 h. Parte de la rama de T50.
- [ ] **T52** · H30 · Despachar dispositivos a granel. Quién: Claude Code. 3 h. **H30 es una propuesta sin aprobar:** el Scrum Master la aprueba en T46 antes de construir. Parte de la rama de T50 si comparte el despacho de H15, o de la de T48 si no (se decide en T46).

### Adelanto opcional
Solo se hacen si sobra tiempo; no suman al total.
- [ ] **T53** · H17 · Iniciar una certificación. Quién: Claude Code. 4 h. Antes falta decidir cómo se reconoce en el catálogo la naviera que trae la API y qué pasa si no existe (propuesta del 2026-11-10 en H17), y si un kit se usa en una sola certificación (criterio 8).
- [ ] **T54** · Revisar con el usuario lo posterior a la asignación del kit (H18 a H20 y E6), para que E5 y E6 se puedan estimar. Quién: Scrum Master. 1 h.

## Total estimado
| Bloque | Tareas | Horas |
| --- | --- | --- |
| Compromiso | T46, T47, T48, T49, T50, T51, T52 | 18 h |
| Adelanto opcional (no suma) | T53, T54 | 5 h |

Capacidad para tareas: unas 18 h. El compromiso la usa entera, sin margen. Si el tiempo no alcanza, se corta desde abajo: T52 (H30) es la primera en pasar al Sprint 4.

Referencia: el Sprint 2 terminó 42.5 h estimadas contra esa misma capacidad, pero sin horas reales no se sabe cuánto rinde de verdad. Este plan se queda en la capacidad declarada hasta compararla con las horas reales al cierre (acuerdo 4).

## Orden de trabajo
Ninguna tarea espera un merge. Lo único que frena el trabajo es una decisión pendiente (T46), un diseño sin aprobar (T47) o el visto bueno de la prueba de una historia.

| Orden | Tarea | Rama | Parte de | Espera |
| --- | --- | --- | --- | --- |
| 1 | T46 · Preparar H14, H15, H16, H29 y H30 | — | — | nada |
| 2 | T47 · Diseños de H29 y H30 | — | — | T46 |
| 3 | T48 · H29 | `feature/H29-activar-dispositivos` | `main` | T46 y T47 |
| 4 | T49 · H14 | `feature/H14-crear-kit` | rama de T48 | nada |
| 5 | T50 · H15 | `feature/H15-despachar-kits` | rama de T49 | nada |
| 6 | T51 · H16 | `feature/H16-consultar-kits` | rama de T50 | nada |
| 7 | T52 · H30 | `feature/H30-despachar-granel` | rama de T50 o de T48 | H30 aprobada |
| 8 | T53 · H17 (adelanto) | `feature/H17-iniciar-certificacion` | `main` | decisiones de la naviera y del kit |

- **Scrum Master, el lunes:** T46 en la mañana y T47 justo después, para que T48 no se frene.
- **Ramas encadenadas.** Cada historia de E4 parte de la anterior porque usa lo que esta deja: la marca de H29, la tabla de kits de H14 y el despacho de H15. Su PR va hacia `main` y, mientras la base no se mergee, muestra también los commits de la base.
- **Orden de merge:** T48, T49, T50 y T51, en ese orden. T52 después de su base.
- **Si la tarea base cambia** por la revisión, Claude Code lleva el cambio a las ramas encadenadas antes de pedir su visto bueno.
- **Viernes:** Sprint Review y Retrospectiva; reporte en `docs/sprint-3-report.md`, con las horas estimadas y las reales de cada tarea.

## Impedimentos y dependencias
- **H29 antes que todo E4:** H14 solo acepta dispositivos activados y todo despacho exige la marca. Sin H29 no se puede probar H14.
- **Dispositivos que ya existen:** los lotes ingresados antes de H29 quedan sin activar. Bodega tendrá que activarlos por rango antes de armar kits con ellos.
- **H17:** depende de la decisión sobre la naviera de la API y de H18, que sigue en revisión (criterio 10).

## Incertidumbres
- El escáner de códigos como teclado (regla común de escaneo) sigue por confirmar. Afecta a H14, H15, H16 y H30. Conviene confirmarlo en T46, antes de construir H14.
- Licencia de la plantilla de ThemeForest: revisar qué permite antes de usarla como referencia en producción. Arrastrada del Sprint 1.
- Despacho a granel: a quién se despacha (trabajador o zona de patio) y si sirve para la reinspección de E6, que está en revisión.

## Temas a resolver durante el sprint
- Aprobar H29 y H30 en el backlog (T46).
- Registrar las horas reales de cada tarea (acuerdo 1).
- Lo posterior a la asignación del kit (H18 a H20 y E6) sigue en revisión (T54).
