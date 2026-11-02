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

Se usan los componentes de Flux con los tokens del tema, siempre que se pueda replicar la plantilla; si Flux no trae uno equivalente o no se puede ajustar, se arma un componente Blade a partir de la plantilla. Se mantienen: controles de al menos 44 px en celular, tablas con desplazamiento horizontal y solo tema claro.

## Menú lateral
Se arma desde el arreglo `$links` de `partials/panel/sidebar.blade.php`. Cada enlace lleva `name`, `icon` (Feather), `route`, `active` y `can`; los grupos usan `header` y los desplegables `submenu`. No se escriben enlaces sueltos en el HTML. Ocultar un enlace no reemplaza validar el permiso en el servidor.

## Migas de pan
Toda pantalla pasa sus migas al layout como parámetro `breadcrumbs`, un arreglo de elementos con `name` y, salvo el último, `route` (la URL ya generada). El layout las muestra junto al título; una sola miga solo muestra el título. Las vistas no escriben migas en su HTML. En páginas Livewire se pasan con `layoutData`.

## Componentes Livewire livianos
Un componente Livewire nunca debe estar sobrecargado: una sola responsabilidad y tamaño pequeño; si crece, se divide en componentes anidados o componentes Blade. Buenas prácticas y clean code: nombres claros, métodos cortos, sin duplicar código. La lógica de negocio y las consultas pesadas van en clases propias (Actions o Services), no en el componente, que solo coordina la pantalla.

## Form Objects
Los formularios grandes usan Livewire Form Objects, con sus reglas de validación y su lógica de guardado ahí y no en el componente.

## Alpine para la UX en el navegador
Todo lo de UX que no necesita el estado del servidor se resuelve con Alpine, aprovechándolo al máximo (menús, modales, pestañas, mostrar y ocultar, confirmaciones, filtros en pantalla sobre datos ya cargados, máscaras de campos). No se hace una petición al backend para algo que Alpine resuelve en el navegador; se usa `$wire` solo cuando hay que hablar con el componente. Alpine ya viene con Livewire: no se instala aparte.

## Escaneo
El dato escaneado se guarda solo, de forma asíncrona, y la pantalla muestra qué se guardó y qué falló, con reintento.
