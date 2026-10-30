# Sprint 1 · SIGLA 2.0 (TOS)

**Fechas:** lunes 2 al viernes 6 de noviembre de 2026 (1 semana).
**Capacidad:** 20 horas en total (10 del Scrum Master y 10 del asistente desarrollador). Unas 2 horas se reservan para los eventos del sprint; quedan unas 18 para tareas.
**Unidad de estimación:** horas.
**Repositorio:** https://github.com/Cerati1998/SIGLA-TOS.git
**Lienzo de Claude Design:** https://claude.ai/artifact/3zgDe8hgTDesp32V3Shhca

## Sprint Goal
Dejar lista la base del proyecto: stack TALL instalado, identidad visual y plantillas aprobadas, guideline y rules base en el repo.

## Historias del sprint
Ninguna por ahora (opción A). Si la base sale rápido, se evalúa sumar H01 · Iniciar sesión durante el sprint. Si a mitad de semana se va justo, la base no se aprieta.

## Tareas (preparación base)
Marca de avance: `[ ]` pendiente, `[x]` terminada.

- [ ] **T01** · Registrar las rules base en `.ai/rules/` (Claude Code, con `record-rule`, sin preguntar). Solo se puede ejecutar cuando T03 haya instalado el proyecto con Boost. Quién: Claude Code. 1 h.
- [x] **T02** · Crear el repositorio vacío en GitHub. Quién: Scrum Master. 0.5 h.
- [x] **T03** · Instalar Laravel 13, el starter kit Livewire (TALL), Reverb y Boost desde cero, con el prompt de instalación. Quién: Claude Code. 3 h. (Hecho el 2026-10-29 en la rama `chore/instalacion-base`; PR #1 mergeado a `main` el 2026-10-30.)
- [x] **T04** · Revisar lo instalado en el repo y confirmarlo con el usuario. 1 h. (Aprobado por el usuario el 2026-10-29.)
- [x] **T05** · Crear el lienzo de Claude Design con su organización por páginas. Quién: Scrum Master. 0.5 h. (Creado el 2026-10-29 con páginas de identidad visual, plantillas base y una por épica E1 a E9; aprobado por el usuario.)
- [x] **T06** · Armar y aprobar la identidad visual (colores, tipografías, logos y estilo). 3 h. (Aprobada por el usuario el 2026-10-29; está en la página "Identidad visual" del lienzo.)
- [x] **T07** · Diseñar y aprobar las plantillas base (acceso y panel de administración, por ahora). 4 h. (Aprobadas por el usuario el 2026-10-29, con versión móvil y colores de insignias de Flowbite; están en la página "Plantillas base" del lienzo.)
- [x] **T08** · Escribir la guideline `.ai/guidelines/flujo-de-trabajo.md` (flujo de Scrum con Claude Code, ligado a los IDs del backlog). 2.5 h. (Aprobada por el usuario el 2026-10-30, las seis partes. Se sube al repo en T09.)
- [ ] **T09** · Subir `docs/` y la guideline con un PR, hacer el merge y correr `php artisan boost:update`. 1 h.

**Total estimado:** 16.5 h para unas 18 disponibles (margen de 1.5 h).

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
- Escala tipográfica (aprobada el 2026-10-29): base de 16 px. h1 32 px (28 px en celular, Rubik 600), h2 24 px (Rubik 500), h3 20 px (Rubik 500), texto base 16 px (párrafos, campos, botones, celdas de tabla y menú), texto pequeño 14 px (etiquetas, ayudas, texto secundario, encabezados de tabla, migas) y grupos del menú 12 px en mayúsculas. Interlineado 1.2 en títulos y 1.5 en texto.
- Estilo: fondo gris muy claro con tarjetas blancas (esquinas de 10 px, sombra suave); botones y campos con esquinas de 5 px y al menos 44 px de alto en celular; menú lateral azul con el logo arriba; tablas con desplazamiento horizontal en pantallas chicas; solo tema claro por ahora.
- Menú en celular: se esconde y se abre con un botón de hamburguesa. Al abrirse se despliega sobre el contenido, justo debajo de la barra superior, que queda fija arriba. En celular el logo no se muestra (la barra superior todavía no lo lleva; pendiente de decidir).

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
