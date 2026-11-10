# Sprint 2 · SIGLA 2.0 (TOS)

**Estado:** aprobado por el Scrum Master el 2026-11-09, con el orden de trabajo ajustado para que las tareas no esperen un merge.
**Fechas:** lunes 9 al viernes 13 de noviembre de 2026 (1 semana).
**Capacidad:** 20 horas en total (10 del Scrum Master y 10 del asistente desarrollador). Unas 2 horas se reservan para los eventos del sprint; quedan unas 18 para tareas.
**Unidad de estimación:** horas.
**Repositorio:** https://github.com/Cerati1998/SIGLA-TOS.git
**Lienzo de Claude Design:** https://claude.ai/artifact/3zgDe8hgTDesp32V3Shhca
**Sprint anterior:** `docs/sprint-1.md` y su reporte `docs/sprint-1-report.md`.

## Dónde quedamos
Al cierre del Sprint 1 (con lo registrado hasta el 2026-11-04) están en `main` la base del proyecto, el acceso (H01 a H03), los usuarios (H04), las líneas navieras (H09), los tipos de dispositivo (H06) y la empresa con sus sucursales (H27). Falta H10, proveedores (T25). Los adelantos T26 a T28 no se iniciaron.

## Sprint Goal (propuesta)
Cerrar las listas maestras con proveedores (consulta de RUC y DNI incluida) y dejar construido el registro de dispositivos de seguridad: ingresar un lote y consultarlo. Si la API de SIGLA 1.0 está lista, dejar también el puente para consultar solicitudes de retiro.

## Historias del sprint (propuesta)
- **H10** · Gestionar proveedores (arrastrada del Sprint 1, T25).
- **H04, criterio 12** · Completar el nombre con la consulta de DNI al crear un usuario (T31). Depende de H10.
- **H07** · Ingresar un lote de dispositivos (T32).
- **H08** · Consultar lotes y dispositivos (T33).
- **H05** · Consultar la solicitud de retiro desde SIGLA 1.0 (T26, condicionada a la API de SIGLA 1.0).

Sigue el orden de épicas aprobado: E1, E7, E8, E2, E3, E4, E9, E5, E6. Con esto se cierra E8 y avanza E2.

## Tareas
Marca de avance: `[ ]` pendiente, `[x]` terminada. Las tareas arrastradas conservan su ID; las nuevas siguen desde T30.

### Arrastradas del Sprint 1
- [x] **T25** · H10 · Gestionar proveedores (con la consulta de T22). Quién: Claude Code. 3 h. (Hecho el 2026-11-09, PR #36.)
- [x] **T27** · Preparar H07 y H08: cerrar los pendientes del backlog (estados de un dispositivo, relleno con ceros de la serie, cuántas series trae el lote más grande, quién ingresa lotes y estado inicial de las unidades) y revisar sus diseños en el lienzo. Quién: Scrum Master. 1 h. Debe estar lista antes de T32. (Hecho el 2026-11-10, PR #40.)
- [x] **T28** · Guardar la empresa y la sucursal del usuario en sesión (`session('company')`, `session('branch')`) y llevar la empresa al título de las pantallas (`Pantalla | Empresa | Nombre de la app`). Quién: Claude Code. 1 h. (Hecho el 2026-11-09, PR #39.)
- [ ] **T26** · Puente con SIGLA 1.0: autenticación y consulta en vivo para H05. Quién: Claude Code. 3 h. **Condicionada:** solo entra si la API de SIGLA 1.0 ya entrega todos los datos de la solicitud filtrando por número de contenedor y booking. Si el lunes no está lista, pasa al adelanto y se avisa como impedimento.

### Nuevas
- [x] **T30** · Diseñar y aprobar en el lienzo la pantalla de ingreso de lote (H07) con la vista previa de cantidad, primera y última serie. Solo si T27 concluye que esa vista no sale de la plantilla de formulario. Quién: Scrum Master con Claude Design. 1 h. (Hecho el 2026-11-10, PR #40.)
- [x] **T31** · H04, criterio 12 · Al crear un usuario con DNI, consultar el servicio externo (al completar 8 dígitos o con la lupa) y completar el nombre, con la misma regla de H10. Quién: Claude Code. 1 h. Parte de la rama de T25: no espera su merge. (Hecho el 2026-11-09, PR #38.)
- [ ] **T32** · H07 · Ingresar un lote de dispositivos. Incluye las tablas de lote y dispositivos, y deja `docs/database.md` al día en el mismo PR. Quién: Claude Code. 4 h. Empieza cuando T27 (y T30, si hace falta) estén cerradas.
- [ ] **T33** · H08 · Consultar lotes y dispositivos. Quién: Claude Code. 3 h. Parte de la rama de T32: no espera su merge.

### Adelanto opcional
Solo se hacen si sobra tiempo; no suman al total.
- [ ] **T34** · Preparar H11, H12 y H13: cerrar los "por confirmar" (orden de las secciones, si las puertas son un tercer tipo, cantidades del tipo de kit) y revisar sus diseños en el lienzo. Quién: Scrum Master. 1 h.
- [ ] **T35** · H11 · Gestionar secciones de certificación. Quién: Claude Code. 2.5 h. Depende de T34.

## Total estimado
| Bloque | Tareas | Horas |
| --- | --- | --- |
| Compromiso | T25, T27, T28, T30, T31, T32, T33 | 14 h |
| Condicionada a la API de SIGLA 1.0 | T26 | 3 h |
| **Compromiso con T26** | | **17 h** |
| Adelanto opcional (no suma) | T34, T35 | 3.5 h |

Capacidad para tareas: unas 18 h. Con T26 quedan 1 h de margen; sin T26, 4 h, que cubren el adelanto.

Referencia: el Sprint 1 terminó 31.5 h estimadas con esa misma capacidad, pero con un compromiso que se infló durante la semana. Este plan se queda en la capacidad declarada para que el compromiso no cambie a mitad de sprint.

## Orden de trabajo
Ninguna tarea espera un merge. Lo único que frena el trabajo es una decisión pendiente (T27) o el visto bueno de la prueba de una historia.

| Orden | Tarea | Rama | Parte de | Espera |
| --- | --- | --- | --- | --- |
| 1 | T25 · H10 | `feature/H10-gestionar-proveedores` | `main` | nada |
| 2 | T31 · H04, criterio 12 | `feature/H04-consulta-dni` | rama de T25 | nada |
| 3 | T28 · empresa y sucursal en sesión | `feature/T28-empresa-en-sesion` | `main` | nada |
| 4 | T32 · H07 | `feature/H07-ingresar-lote` | `main` | T27 cerrada (y T30, si hace falta) |
| 5 | T33 · H08 | `feature/H08-consultar-lotes` | rama de T32 | nada |
| 6 | T26 · H05 | `feature/H05-consultar-solicitud` | `main` | la API de SIGLA 1.0 |

- **Scrum Master, en paralelo:** T27 el lunes, para que T32 no se frene al terminar T28. T30 solo si T27 lo pide.
- **Ramas encadenadas.** T31 parte de la rama de T25, y T33 de la de T32. Su PR va hacia `main` y, mientras la tarea base no se mergee, muestra también los commits de esa tarea. Al mergear la base, el PR queda solo con lo suyo.
- **Orden de merge:** primero la tarea base (T25 antes que T31, y T32 antes que T33). Las demás se mergean en cualquier orden.
- **Si la tarea base cambia** por la revisión, Claude Code lleva el cambio a la rama encadenada antes de pedir su visto bueno.
- **Viernes:** Sprint Review y Retrospectiva (incluida la del Sprint 1); reporte en `docs/sprint-2-report.md`.

Para no tocar los mismos archivos a la vez: T25 y T31 tocan proveedores y usuarios (por eso T31 se encadena); T28 toca la sesión y el layout; T32 y T33 abren el módulo nuevo de dispositivos.

## Impedimentos y dependencias
- **API de SIGLA 1.0:** creada a medias. Falta que entregue todos los datos de la solicitud filtrando por `numero_contenedor` + `booking`. Es trabajo del sistema viejo. Define si T26 entra.
- **Decisiones de T27:** H07 y H08 tienen estados, relleno de ceros y tamaño de lote por confirmar. Sin ellas no se puede construir T32.

## Incertidumbres
- Licencia de la plantilla de ThemeForest: revisar qué permite (copiar y adaptar su estilo, reutilizar imágenes y fuentes) antes de usarla como referencia en producción. Arrastrada del Sprint 1.
- El escáner de códigos como teclado (regla común de escaneo) sigue por confirmar; afecta a H07 y H08 (búsqueda por serie).
- Cuántas series trae el lote más grande: define si T32 necesita procesar en cola.

## Temas a resolver durante el sprint
- Aprobar H27 en el backlog (está construida como propuesta). H25 y H26 siguen como propuestas.
- Registrar horas reales de cada tarea para calibrar cuánto rinde la capacidad de 20 h con Claude Code.
- Confirmar qué se hizo el 5 y el 6 de noviembre y no quedó marcado en `docs/sprint-1.md`.
- Temas abiertos del Sprint 1: reparto de tareas entre el Scrum Master y el asistente, quién prueba el trabajo del asistente y si alcanzan las pruebas automáticas de la Definition of Done.
- Lo posterior a la asignación del kit (H18 a H20 y E6) sigue en revisión; conviene programar esa revisión para tener E5 y E6 estimables.
