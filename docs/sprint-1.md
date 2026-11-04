# Sprint 1 · SIGLA 2.0 (TOS)

**Fechas:** lunes 2 al viernes 6 de noviembre de 2026 (1 semana).
**Capacidad:** 20 horas en total (10 del Scrum Master y 10 del asistente desarrollador). Unas 2 horas se reservan para los eventos del sprint; quedan unas 18 para tareas.
**Unidad de estimación:** horas.
**Repositorio:** https://github.com/Cerati1998/SIGLA-TOS.git
**Lienzo de Claude Design:** https://claude.ai/artifact/3zgDe8hgTDesp32V3Shhca

## Sprint Goal
Dejar lista la base del proyecto (stack TALL instalado, identidad visual y plantillas aprobadas, guideline y rules base en el repo) y tener construidas las listas maestras hasta tipos de dispositivos de seguridad: usuarios, líneas navieras, proveedores y tipos de dispositivo. (Ampliado por el usuario el 2026-11-04.)

## Historias del sprint
H01 · Iniciar sesión, H02 · Cerrar sesión, H03 · Recuperar contraseña y H04 · Gestionar usuarios intranet, sumadas por el usuario el 2026-11-02 junto con la plantilla base (T10) y las rutas de intranet (T14). La extranet no entra en este sprint. Antes: ninguna (opción A).
El 2026-11-04 el usuario sumó, por el tiempo que sobraba, tareas que iban al Sprint 2: el modelo de datos (T18, T19), el glosario (T20), la preparación de H09, H06 y H10 (T21, T22) y esas tres historias (T23 a T25). T26 a T28 quedan como adelanto opcional.

## Tareas (preparación base)
Marca de avance: `[ ]` pendiente, `[x]` terminada.

- [x] **T01** · Registrar las rules base en `.ai/rules/` (Claude Code, con `record-rule`, sin preguntar). Solo se puede ejecutar cuando T03 haya instalado el proyecto con Boost. Quién: Claude Code. 0.5 h. (Hecho el 2026-11-02, PR #4. Archivos escritos a mano e índice generado con `boost:index-rules`: el MCP de Boost no estaba conectado y `record-rule` no agrupa por tema.)
- [x] **T02** · Crear el repositorio vacío en GitHub. Quién: Scrum Master. 0.25 h.
- [x] **T03** · Instalar Laravel 13, el starter kit Livewire (TALL), Reverb y Boost desde cero, con el prompt de instalación. Quién: Claude Code. 1.5 h. (Hecho el 2026-10-29 en la rama `chore/instalacion-base`; PR #1 mergeado a `main` el 2026-10-30.)
- [x] **T04** · Revisar lo instalado en el repo y confirmarlo con el usuario. 0.5 h. (Aprobado por el usuario el 2026-10-29.)
- [x] **T05** · Crear el lienzo de Claude Design con su organización por páginas. Quién: Scrum Master. 0.25 h. (Creado el 2026-10-29 con páginas de identidad visual, plantillas base y una por épica E1 a E9; aprobado por el usuario.)
- [x] **T06** · Armar y aprobar la identidad visual (colores, tipografías, logos y estilo). 1.5 h. (Aprobada por el usuario el 2026-10-29; está en la página "Identidad visual" del lienzo.)
- [x] **T07** · Diseñar y aprobar las plantillas base (acceso y panel de administración, por ahora). 2 h. (Aprobadas por el usuario el 2026-10-29, con versión móvil y colores de insignias de Flowbite; están en la página "Plantillas base" del lienzo.)
- [x] **T08** · Escribir la guideline `.ai/guidelines/flujo-de-trabajo.md` (flujo de Scrum con Claude Code, ligado a los IDs del backlog). 1.25 h. (Aprobada por el usuario el 2026-10-30, las seis partes. Se sube al repo en T09.)
- [x] **T09** · Subir `docs/` y la guideline con un PR, hacer el merge y correr `php artisan boost:update`. 0.5 h. (Hecho el 2026-10-30: PR #2 mergeado a `main` con `docs/` y la guideline; `boost:update` agregó el flujo de trabajo a `CLAUDE.md`, sin skills nuevas.)


## Tareas agregadas el 2026-11-02
- [x] **T10** · Plantilla base: tema, layouts y componentes del lienzo y la plantilla. Quién: Claude Code. 2 h. (Hecho el 2026-11-02, PR #5.)
- [x] **T11** · H01 · Iniciar sesión. Quién: Claude Code. 1 h. (Hecho el 2026-11-02, PR #6.)
- [x] **T12** · H02 · Cerrar sesión. Quién: Claude Code. 0.5 h. (Hecho el 2026-11-02, PR #7.)
- [x] **T13** · H03 · Recuperar contraseña. Quién: Claude Code. 1 h. (Hecho el 2026-11-03, PR #15.)
- [x] **T14** · Rutas de intranet. Quién: Claude Code. 1 h. (Hecho el 2026-11-03, PR #16.)
- [x] **T15** · H04 · Gestionar usuarios intranet. Quién: Claude Code. 2.75 h. (Hecho el 2026-11-03, PR #19.)

## Tareas agregadas el 2026-11-03
- [x] **T16** · Diseñar y aprobar en el lienzo el layout de los correos, con la identidad visual, el logo de SIGLA y sin enlaces largos en el cuerpo. Quién: Claude Code; aprueba el usuario. 0.5 h. (Aprobado por el usuario el 2026-11-03, PR #21.)
- [x] **T17** · Adaptar el layout de los correos desde el diseño aprobado (uno solo para todos los correos, sea cual sea su propósito) y aplicarlo al correo de recuperar contraseña. Quién: Claude Code. 1 h. (Hecho el 2026-11-03, PR #21.)

## Tareas agregadas el 2026-11-04
Bloque pedido por el usuario con el sprint casi cerrado. T18 a T25 son el compromiso; T26 a T28 solo se hacen si sobra tiempo (no suman al total).

- [x] **T18** · Documentar el modelo de datos en `docs/database.md` (versionado, con diagrama Mermaid y diccionario por tabla, solo hasta este sprint), corregir en el backlog la nota que dice que el ER es solo referencia, proponer los cambios al backlog que salen del cuestionario (H04, H15, H17, historias nuevas H25 y H26) y anotar la regla de que toda migración actualiza `database.md`. Quién: Claude Code; aprueba el usuario. 1 h. (Aprobado por el usuario el 2026-11-04, PR #25.)
- [ ] **T19** · Tabla de tipos de documento (`identities`, por seeder), tabla de sucursales (`branches`, por seeder) y los campos de tipo de documento, número de documento y sucursal en `users`, con el cambio al modal de H04. Debe dejar `docs/database.md` al día. Quién: Claude Code. 2.5 h.
- [ ] **T20** · Llenar el glosario (`docs/glossary.md`) con los nombres en inglés de los términos del negocio (sello, precinto, cinta, funda, gate out, remolcador, semirremolque, tipo de documento). Quién: Scrum Master decide, Claude Code escribe. 0.5 h.
- [ ] **T21** · Cerrar los puntos por confirmar de H09, H10 y H06 (quién usa cada pantalla, datos obligatorios del proveedor, borrado de tipos usados) y comprobar que sus diseños en el lienzo estén aprobados; si falta alguno, diseñarlo y aprobarlo. Quién: Scrum Master. 1 h.
- [ ] **T22** · Elegir el servicio externo de consulta de RUC y DNI (costo y condiciones) y dejar listo el cliente que usarán H10, H21 y H22. Quién: Scrum Master elige; Claude Code integra. 1 h.
- [ ] **T23** · H09 · Gestionar líneas navieras. Quién: Claude Code. 1.5 h.
- [ ] **T24** · H06 · Gestionar tipos de dispositivo. Quién: Claude Code. 1.5 h.
- [ ] **T25** · H10 · Gestionar proveedores (con la consulta de T22). Quién: Claude Code. 3 h.
- [ ] **T26** · (Adelanto opcional) Puente con SIGLA 1.0: autenticación y consulta en vivo para H05. Quién: Claude Code. 3 h.
- [ ] **T27** · (Adelanto opcional) Preparar H07 y H08: cerrar los criterios pendientes de anuncio y lote, y revisar sus diseños en el lienzo. Quién: Scrum Master. 1 h.
- [ ] **T28** · (Adelanto opcional) Guardar la empresa y la sucursal del usuario en sesión (`session('company')`, `session('branch')`) y llevar la empresa al título de las pantallas (`Pantalla | Empresa | Nombre de la app`). Separada de T19 por pedido del usuario el 2026-11-04. Quién: Claude Code. 1 h.

**Total estimado:** 18 h para unas 18 disponibles (sin margen). El usuario pidió el 2026-11-03 ajustar la duración de todas las tareas para que encajen en la semana, sin mover las fechas; antes sumaban 35.5 h (T01 a T09: 16.5 h; T10 a T15: 16 h; T16 y T17: 3 h).

**Total con las tareas del 2026-11-04:** 18 h ya hechas (T01 a T17) + 12 h de compromiso (T18 a T25) = 30 h para unas 18 disponibles. Quedan 12 h por encima de la capacidad (T26 a T28, 5 h más, quedan aparte como adelanto). El usuario amplió el Sprint Goal el 2026-11-04 para incluir estas listas, así que T18 a T25 se mantienen. La capacidad no se cambió.

## Incertidumbres
- Licencia de la plantilla de ThemeForest: revisar qué permite (copiar y adaptar su estilo, reutilizar imágenes y fuentes) antes de usarla como referencia en producción.

## Decisiones de la preparación base
- Laravel 13 con el starter kit oficial Livewire (Livewire 4, Flux, Tailwind 4, Fortify). Sin Jetstream.
- Se aceptan los paquetes extra que vinieron con el kit y con la instalación (vite-plus, chisel, blaze, pao, larastan, sail).
- Pruebas con PHPUnit.
- Colas, sesión y caché con driver `database`. Verificación de correo y 2FA desactivadas hasta nueva decisión. Registro público desactivado.
- No se usa GSD: el trabajo se organiza solo con Scrum (decisión del 2026-10-29).
- Base de datos de producción: MariaDB 10.6 (confirmado por el usuario desde el cPanel).
- Hosting de producción: cPanel con PHP 8.5 y acceso SSH. Colas por cron; Reverb depende de la configuración del servidor (acceso a WHM/root confirmado).

## Identidad visual aprobada (2026-10-29)
- Referencia de estilo: plantilla Bootstrap 5 de ThemeForest, variante `main` (tema claro con menú lateral). Se pasa a Tailwind tomando solo lo necesario, como componentes y layouts del proyecto.
- Colores: azul Macromar `#002F7F` como principal; naranja `#D2430A` como acento (versión ajustada del naranja del logo, `#F25010`, para llegar a contraste 4.6 con letra blanca); verde `#97BF0E`, celeste `#229BD2` y naranja `#F0930A` de SIGLA solo para estados y gráficos. El magenta del logo SIGLA no se usa en la interfaz. Los códigos son medidos sobre las imágenes de los logos (aproximados).
- Colores de apoyo: fondo `#F3F5F9`, borde `#D9DEE8`, encabezado de tabla `#EEF1F7`, texto `#1F2937`, texto secundario `#4B5563` y error `#D0021B`.
- Insignias de estado (colores de Flowbite, aprobados el 2026-10-29): éxito fondo `#ECFDF5` y texto `#004F3B`; información (azul) fondo `#EFF6FF` y texto `#1C398E`; advertencia fondo `#FFF7ED` y texto `#7E2A0C`. Disponibles si hacen falta: peligro fondo `#FFF1F2` y texto `#8B0836`; gris fondo `#F9FAFB` y texto `#101828`. Tamaño de texto de 14 px según nuestra escala.
- Tipografías: Rubik para títulos e IBM Plex Sans para texto, tablas y formularios.
- Escala tipográfica (aprobada el 2026-10-29): base de 16 px. h2 24 px y h3 20 px, en Rubik 400, sin negrita; los títulos de pantalla van en h2, los de sección o tarjeta en h3, y no se usa h1 (cambios pedidos por el usuario el 2026-11-02), texto base 16 px (párrafos, campos, botones, celdas de tabla y menú), texto pequeño 14 px (etiquetas, ayudas, texto secundario, encabezados de tabla, migas) y grupos del menú 12 px en mayúsculas. Interlineado 1.2 en títulos y 1.5 en texto.
- Estilo: fondo gris muy claro con tarjetas blancas (esquinas de 10 px, sombra suave); botones y campos con esquinas de 5 px y al menos 44 px de alto en celular; menú lateral azul con el logo arriba; tablas con desplazamiento horizontal en pantallas chicas; solo tema claro por ahora.
- Menú en celular: se esconde y se abre con un botón de hamburguesa. Al abrirse se despliega sobre el contenido, justo debajo de la barra superior, que queda fija arriba. Por debajo de 768 px (celular y `sm`) la barra superior va en una sola fila: el botón del menú a la izquierda, el logo a su derecha y el menú de usuario al final (decisión del usuario del 2026-11-02).

## Plantillas base aprobadas (2026-10-29)
- Acceso: iniciar sesión, olvidé mi contraseña y restablecer contraseña (H01 a H03). Sin enlace de registro.
- Panel de administración: layout base (menú lateral azul con el logo, barra superior con usuario, rol y "Cerrar sesión"), lista con tabla (buscador, filtro, botón "Nuevo", estados, paginación) y formulario (campos con ayuda y error, observaciones, Cancelar y Guardar).
- Versión móvil (390 × 844) de las siete pantallas: las tres de acceso, el panel con el menú cerrado y abierto, la lista y el formulario. Están en la fila 3 de la página "Plantillas base".
- El menú de las plantillas es de ejemplo y solo incluye secciones de las épicas aprobadas; todavía no tiene iconos.

## Guideline de flujo de trabajo (T08, aprobada el 2026-10-30)
Seis partes, escritas para Claude Code:
1. Antes de empezar una historia: el usuario indica el ID.
2. Diseño en el lienzo: el usuario pasa el enlace del lienzo.
3. Construcción: rama por historia y pruebas con PHPUnit.
4. Revisión y PR: Claude Code se autorrevisa y avisa; el usuario prueba y da su visto bueno; después Claude Code abre el PR con `gh`. El merge lo hace el usuario.
5. Rules: pregunta en el momento "¿Quieres que sea siempre así? ¿Lo anoto en las rules?".
6. Marcar las tareas en `docs/sprint-N.md` en el mismo PR, y proponer en ese PR los cambios al `docs/product-backlog.md` si el trabajo cambió algo del producto.

Alineada con la Definition of Done el 2026-10-30. La Definition of Done y el backlog se corrigieron ese día: ya no mencionan Jetstream y las pruebas se escriben con PHPUnit.

## Temas a resolver durante el sprint
- Reparto de tareas entre el Scrum Master y el asistente cuando trabajen en paralelo, para no tocar los mismos archivos.
- Quién prueba y da el visto bueno al trabajo del asistente (la Definition of Done habla del "usuario").
- Si el tiempo alcanza para las pruebas automáticas exigidas por la Definition of Done.
- Suposiciones por confirmar en la primera historia: que Claude Code pueda leer el lienzo con el enlace, que tenga `gh` con sesión iniciada, que `record-rule` esté disponible en la versión de Boost instalada y que los PR vayan a `main`.
