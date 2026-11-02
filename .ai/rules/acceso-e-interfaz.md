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
Se crean a partir del template entregado con el lienzo de Claude Design (carpeta `templates/`, ignorada por git, solo referencia local), tomando solo lo necesario.

Lo visual lo define el lienzo aprobado: colores, tipografías (Rubik e IBM Plex Sans), insignias de estado, escala de texto y componentes base (acceso, panel, lista, formulario), usados como tokens del tema y sin colores hex sueltos. El comportamiento UX y la responsividad siguen los del template (menú lateral, apertura del menú en celular, tablas, filtros, formularios). Si el template y el lienzo chocan en lo visual, manda el lienzo; en comportamiento, manda el template.

Se usan los componentes de Flux con los tokens del lienzo, siempre que se pueda replicar el comportamiento del template; si Flux no trae uno equivalente, se arma un componente Blade a partir del template. Se mantienen: controles de al menos 44 px en celular, tablas con desplazamiento horizontal y solo tema claro.

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
