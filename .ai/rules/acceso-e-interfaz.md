---
paths:
  - 'app/Http/**'
  - 'app/Livewire/**'
  - 'resources/css/**'
  - 'resources/js/**'
  - 'resources/views/**'
  - 'routes/**'
---

# Acceso e interfaz

## Permisos
Cada pantalla y cada acción de Livewire valida el rol en el servidor, no solo oculta el menú. Las rutas de intranet van separadas de las de una futura extranet.

## Origen de los componentes
Se crean a partir de la plantilla `documentation/TEMPLATE/html/main` (carpeta ignorada por git, solo referencia local), tomando solo lo necesario.

El lienzo de Claude Design da la idea general de la identidad de la aplicación: colores, tipografías (Rubik e IBM Plex Sans), escala de texto y disposición de las pantallas, usados como tokens del tema y sin colores hex sueltos. El detalle sale de la plantilla: tamaños, espaciados, bordes, sombras, iconos (Feather, salvo en los botones, que usan Font Awesome), menús, tablas, filtros, formularios, comportamiento y responsividad. Si chocan, el lienzo manda en colores, tipografía y disposición; la plantilla manda en todo lo demás.

Bootstrap 5 no se usa en el proyecto: cada clase, propiedad y estilo de la plantilla se pasa a Tailwind para respetar el stack TALL. Tampoco se copian su CSS, su jQuery ni sus plugins.

Las tablas de listas usan Rappasoft Livewire Tables. Los modales, sus inputs y los botones de fila usan WireUI. El resto de la interfaz usa Flux. Todos con los tokens del tema, siempre que se pueda replicar la plantilla; si el componente no existe o no se puede ajustar, se arma un componente Blade a partir de la plantilla. Se mantienen: controles de al menos 44 px en celular, tablas con desplazamiento horizontal y solo tema claro.

Excepción: las tablas de listas siguen el estilo del tema Tailwind de Rappasoft (encabezado en mayúsculas, filas alternadas y barra nativa de búsqueda y filtros), con los colores de la marca y los tokens del tema.

## Acciones de fila
Los botones de cada fila de una tabla van en un partial llamado `actions.blade.php`, dentro de `resources/views/pages/<recursos>/partials/`. Son botones `x-wire-button` de tamaño `xs`, solo con icono Font Awesome (solid), con `title` y `aria-label`. Razón: que todas las tablas tengan sus opciones en el mismo sitio y con el mismo aspecto.

## Colores de botones en tablas
El botón de creación usa `x-panel.create-button`, en el naranja de acento. En las filas, editar va en verde, eliminar o desactivar en rojo y las demás acciones en azul. Razón: que cada acción se reconozca igual en todas las tablas.

## Botones que llaman al componente
Todo botón que ejecuta un método de un componente Livewire, con `wire:click`, `$wire` o `form="..."` fuera del `<form>`, lleva `wire:loading.attr="disabled"` y `wire:target` con ese método, para que quede desactivado mientras se ejecuta. Un botón de envío dentro del `<form>` no lo necesita, porque `wire:submit` ya lo desactiva. Razón: que varios clics no repitan la acción.

## Avisos y confirmaciones
Los avisos son toasts de SweetAlert2, con el evento `toast`, y las confirmaciones usan `confirmAction`. Razón: que todos los avisos y confirmaciones se vean igual.

## Modales de creación
Todo modal de creación es propio: no reutiliza el de edición y se abre solo con Alpine (`x-on:click="$openModal('...')"`), sin petición al servidor. Razón: abrir no gasta una petición y crear no hereda datos de una edición.

## Menú lateral
Se arma desde el arreglo `$links` de `partials/panel/sidebar.blade.php`. Cada enlace lleva `name`, `icon` (Feather), `route`, `active` y `can`; los grupos usan `header` y los desplegables `submenu`. No se escriben enlaces sueltos en el HTML. Ocultar un enlace no reemplaza validar el permiso en el servidor.

## Migas de pan
Toda pantalla pasa sus migas al layout como parámetro `breadcrumbs`, un arreglo de elementos con `name` y, salvo el último, `route` (la URL ya generada). El layout las muestra junto al título; una sola miga solo muestra el título. Las vistas no escriben migas en su HTML. Se pasan en la etiqueta del layout, en la vista.

## Título y migas en la vista
Toda pantalla es una vista Blade que abre con `<x-layouts::app title="..." :breadcrumbs="[...]">` y monta adentro sus componentes Livewire, que no definen título ni layout. Razón: ver en cada vista su título, sus migas y lo que contiene.

## Títulos
Los títulos de pantalla van en `h2` con el componente `x-page-title` (Rubik 400, 24 px, sin negrita) y no se usa `h1`. Los títulos de sección o de tarjeta van en `h3` (20 px).

## Componentes Livewire livianos
Un componente Livewire nunca debe estar sobrecargado: una sola responsabilidad y tamaño pequeño; si crece, se divide en componentes anidados o componentes Blade. Buenas prácticas y clean code: nombres claros, métodos cortos, sin duplicar código. La lógica de negocio y las consultas pesadas van en clases propias (Actions o Services), no en el componente, que solo coordina la pantalla.

## Componentes Livewire en clase
Cada componente es una clase en `app/Livewire` que pinta su vista con `render()`, y la vista va aparte en `resources/views/pages`. No se usan componentes de un solo archivo. Razón: separar el PHP de la vista.

## Form Objects
Los formularios grandes usan Livewire Form Objects, con sus reglas de validación y su lógica de guardado ahí y no en el componente.

## Controladores y componentes
No deben tener lógica de negocio, esta se debe llevar a un action o services.

## Escaneo
El dato escaneado se guarda solo, de forma asíncrona, y la pantalla muestra qué se guardó y qué falló, con reintento.

## Correos
Todos los correos del sistema, sea cual sea su propósito, usan por defecto un mismo layout o partials de correo con la identidad del lienzo y de la marca. Si ya existe un layout, se adapta en vez de crear otro. Razón: que todos los correos se vean como la aplicación.

## Rutas por tipo de acceso
Cada tipo de acceso tiene su archivo de rutas, su prefijo de URL y su prefijo de nombre (`intranet/` e `intranet.`, `extranet/` y `extranet.`). Lo común a ambos, como el login, la recuperación de contraseña y el perfil, va fuera de esos grupos. Razón: que sus rutas no se mezclen ni choquen.

## Roles hasta culminar E6
Hasta culminar E6 no se validan roles; las pantallas solo exigen sesión iniciada. Razón: los roles se definen con todo el panorama armado.

## Enlaces en los correos
Todo enlace de un correo va en un botón; la URL no se escribe de forma explícita en el cuerpo, ni siquiera en la versión de texto plano. Razón: evitar enlaces largos en el cuerpo del correo.

## Título de la pestaña
El `<title>` de toda pantalla con sesión iniciada es `Pantalla | Empresa | Nombre de la app`, con la empresa tomada de `session('company.name')`; la sucursal no va en el título. Las pantallas sin sesión (iniciar sesión, recuperar contraseña) usan `Pantalla | Nombre de la app`. Razón: que cada pestaña muestre en qué pantalla y en qué empresa se está trabajando.

## Acciones junto al título de un campo
Una acción sobre un campo, como «+ Agregar», va al otro extremo de su título, en el slot `corner` del input o select de WireUI, con el componente `x-label-link`: un enlace `<a>` de solo texto, sin forma de botón ni icono; el «+» se escribe como parte del texto, para que se lea como uno solo. Esto aplica solo a estos enlaces sobre el título de un campo; los botones siguen con su icono Font Awesome. Razón: que estas acciones se vean igual en todos los formularios y no compitan con los botones del modal.

## Selectores de catálogos en modales
Cuando un campo elige un registro de otro catálogo que puede crecer, se usa el select async de WireUI (`x-wire-select` con `async-data` por POST): busca en el servidor y solo ofrece los activos, sin que lo buscado viaje en la URL. Si el usuario puede crear ese registro, junto al título va el enlace «+ Agregar» (`x-label-link`), que abre encima el modal de creación de ese catálogo y deja elegido lo creado. Razón: elegir rápido entre muchos registros y crear el que falta sin salir del formulario.

## PDF del sistema
Todo PDF se genera con dompdf (`barryvdh/laravel-dompdf`) desde una vista Blade y sigue el formato aprobado en el lienzo («H15 · PDF del kit despachado»): hoja A4 vertical y letra DejaVu Sans; cabecera en tres cajas con borde gris oscuro (logo, título en mayúsculas y caja gris clara con el número del documento); datos en cajas con borde gris oscuro, etiquetas y títulos de sección en negro y mayúsculas; tablas con encabezado en el azul de la marca y texto blanco, y filas alternadas en gris; pie en caja con quién lo generó, cuándo y la página. El color de la marca solo va en los encabezados de las tablas y en el número del documento. Razón: que todos los documentos del sistema se vean iguales, sobrios y con la marca.

## Alpine para la UX en el navegador
Todo lo de UX que no necesita la base de datos ni una validación que solo sabe el servidor se resuelve con Alpine: menús, modales, pestañas, mostrar y ocultar, confirmaciones, marcar filas o casillas, contadores y resúmenes de lo marcado, filtros y orden sobre datos ya cargados, máscaras y validaciones de formato (largo, solo números, rangos con límites ya conocidos). Antes de usar `wire:model.live`, `wire:click` o `$wire`, la acción debe leer o guardar en la base, validar algo que solo sabe el servidor o calcular importes con la fórmula única del servidor. Si no, va en Alpine, y lo elegido se envía al servidor una sola vez, al guardar o confirmar, donde se vuelve a validar. Esta regla manda sobre la guía general de Livewire de mantener el estado en el servidor. Alpine ya viene con Livewire: no se instala aparte. Razón: cada petición de más vuelve a pintar el componente y hace lenta la pantalla, sobre todo en celular.

## Estructura de Alpine en las vistas
Aplica solo a las vistas de pantalla del sitio enlazadas directamente con un componente Livewire, que las pinta con su `render()`. En ellas la lógica de Alpine no va inline: el `div` raíz lleva `x-data="nombre"` y ese nombre se define con `Alpine.data()` en un bloque `@script` al pie de la misma vista. En el marcado solo van nombres de propiedades y métodos (`x-on:click="guardar"`, `x-text="etiqueta"`), y se usa `$wire` directo, no `@entangle`. No aplica a los modales ni a los partials o componentes Blade que se incluyen dentro de otra vista, como las acciones de fila de las tablas Rappasoft: esos siguen su skill. Razón: que el código se lea fácil y la lógica de cada pantalla esté en un solo sitio.

## Archivos de JavaScript
Solo se crea un archivo en `resources/js` para lógica de Alpine que se reutiliza en todo el proyecto, como una directiva (`x-digits-only`) o un helper global. Lo que usa una sola pantalla, modal o partial va en un bloque `@script` al pie de ese mismo archivo Blade, con `Alpine.data()`, y no inline en el marcado. Los campos que solo admiten números llevan `x-digits-only`, y el servidor los vuelve a validar al guardar. Razón: no llenar `resources/js` de archivos de una sola pantalla y tener la lógica junto a la vista que la usa.

## Diseño en el lienzo
Toda vista nueva tiene su diseño en el lienzo, aprobado por el usuario antes de construirla, aunque parta de una plantilla base. Una corrección a una vista que ya existe actualiza en el lienzo el diseño que esa vista ya tiene; si no tiene, la tarea de la corrección lo crea, con la vista actual y su cambio, y se aprueba antes de construir. Razón: que cada pantalla se vea y se apruebe antes de escribir código.
