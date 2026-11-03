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

El lienzo de Claude Design da la idea general de la identidad de la aplicación: colores, tipografías (Rubik e IBM Plex Sans), escala de texto y disposición de las pantallas, usados como tokens del tema y sin colores hex sueltos. El detalle sale de la plantilla: tamaños, espaciados, bordes, sombras, iconos (Feather), menús, tablas, filtros, formularios, comportamiento y responsividad. Si chocan, el lienzo manda en colores, tipografía y disposición; la plantilla manda en todo lo demás.

Bootstrap 5 no se usa en el proyecto: cada clase, propiedad y estilo de la plantilla se pasa a Tailwind para respetar el stack TALL. Tampoco se copian su CSS, su jQuery ni sus plugins.

Las tablas de listas usan Rappasoft Livewire Tables. Los modales, sus inputs y los botones de fila usan WireUI. El resto de la interfaz usa Flux. Todos con los tokens del tema, siempre que se pueda replicar la plantilla; si el componente no existe o no se puede ajustar, se arma un componente Blade a partir de la plantilla. Se mantienen: controles de al menos 44 px en celular, tablas con desplazamiento horizontal y solo tema claro.

Excepción: las tablas de listas siguen el estilo del tema Tailwind de Rappasoft (encabezado en mayúsculas, filas alternadas y barra nativa de búsqueda y filtros), con los colores de la marca y los tokens del tema.

## Acciones de fila
Los botones de cada fila de una tabla van en un partial llamado `actions.blade.php`, dentro de `resources/views/pages/<recursos>/partials/`. Son botones `x-wire-button` de tamaño `xs`, solo con icono Feather, con `title` y `aria-label`. Razón: que todas las tablas tengan sus opciones en el mismo sitio y con el mismo aspecto.

## Colores de botones en tablas
El botón de creación usa `x-panel.create-button`, en el naranja de acento. En las filas, editar va en verde, eliminar o desactivar en rojo y las demás acciones en azul. Razón: que cada acción se reconozca igual en todas las tablas.

## Botones que llaman al componente
Todo botón que ejecuta un método de un componente Livewire, con `wire:click`, `$wire` o `form="..."` fuera del `<form>`, lleva `wire:loading.attr="disabled"` y `wire:target` con ese método, para que quede desactivado mientras se ejecuta. Un botón de envío dentro del `<form>` no lo necesita, porque `wire:submit` ya lo desactiva. Razón: que varios clics no repitan la acción.

## Menú lateral
Se arma desde el arreglo `$links` de `partials/panel/sidebar.blade.php`. Cada enlace lleva `name`, `icon` (Feather), `route`, `active` y `can`; los grupos usan `header` y los desplegables `submenu`. No se escriben enlaces sueltos en el HTML. Ocultar un enlace no reemplaza validar el permiso en el servidor.

## Migas de pan
Toda pantalla pasa sus migas al layout como parámetro `breadcrumbs`, un arreglo de elementos con `name` y, salvo el último, `route` (la URL ya generada). El layout las muestra junto al título; una sola miga solo muestra el título. Las vistas no escriben migas en su HTML. En páginas Livewire se pasan con `layoutData`.

## Títulos
Los títulos de pantalla van en `h2` con el componente `x-page-title` (Rubik 400, 24 px, sin negrita) y no se usa `h1`. Los títulos de sección o de tarjeta van en `h3` (20 px).

## Componentes Livewire livianos
Un componente Livewire nunca debe estar sobrecargado: una sola responsabilidad y tamaño pequeño; si crece, se divide en componentes anidados o componentes Blade. Buenas prácticas y clean code: nombres claros, métodos cortos, sin duplicar código. La lógica de negocio y las consultas pesadas van en clases propias (Actions o Services), no en el componente, que solo coordina la pantalla.

## Form Objects
Los formularios grandes usan Livewire Form Objects, con sus reglas de validación y su lógica de guardado ahí y no en el componente.

## Alpine para la UX en el navegador
Todo lo de UX que no necesita el estado del servidor se resuelve con Alpine, aprovechándolo al máximo (menús, modales, pestañas, mostrar y ocultar, confirmaciones, filtros en pantalla sobre datos ya cargados, máscaras de campos). No se hace una petición al backend para algo que Alpine resuelve en el navegador; se usa `$wire` solo cuando hay que hablar con el componente. Alpine ya viene con Livewire: no se instala aparte.

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
