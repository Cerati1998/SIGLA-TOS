---
name: wireui-modales-inputs-development
description: "Usa este skill siempre que crees o cambies un modal o los inputs dentro de un modal con WireUI: prefijo x-wire-*, modal-card con wire:model, botón de envío en el pie, modal persistente, medidas de la plantilla y escaneo de Tailwind 4."
---

# Modales e inputs con WireUI

Los modales, sus inputs y los botones de fila de las tablas usan WireUI. El resto de la interfaz usa Flux, incluidos los botones
del pie del modal. Los botones de fila están en `rappasoft-tablas-acciones-fila-development`. El form object está en `livewire-forms-modal-development`.

## Instalación en este proyecto

- `wireui/wireui` `^2.6`, con el prefijo `wire-` en `config/wireui.php`: `<x-wire-input>`, `<x-wire-modal-card>`.
- `@wireUiScripts` va antes de `@fluxScripts` en `layouts/app/sidebar.blade.php`.
- En `resources/css/app.css` **no se importa** `ts/global.css` de WireUI: vuelve a importar Tailwind, trae su paleta y exige
  el paquete npm `@tailwindcss/forms`. Solo se importan sus cuatro archivos de plugins.
- Sin `@tailwindcss/forms`, `x-wire-checkbox` y `x-wire-radio` no tienen estilos propios. Antes de usarlos, pruébalos y avisa
  al usuario. `x-wire-input` y el modal no lo necesitan.
- El escaneo de Tailwind está acotado a `src/Components/{Button,Card,Label,Modal,TextField,Wrapper}` más
  `@source inline('z-50')`, porque el `z-50` del modal se define en `src/WireUiConfig.php`. Si usas otro componente de WireUI,
  agrega su carpeta a `@source`.
- Sus colores (`primary`, `secondary`, `negative`, `background`) están enlazados a los tokens del tema en `@theme`. Si falta
  un tono en una clase, agrégalo enlazado a un token, sin colores hex.

## Modal con formulario

```blade
<x-wire-modal-card :title="$form->userId === null ? 'Nuevo usuario' : 'Editar usuario'" wire:model="form.openModal">
    <form id="user-form" wire:submit="save" class="grid gap-4">
        <x-pages::users.fields />
    </form>

    <x-slot:footer>
        <div class="flex justify-end gap-3">
            <flux:button type="button" x-on:click="close">Cancelar</flux:button>
            <flux:button variant="primary" type="submit" form="user-form">Guardar</flux:button>
        </div>
    </x-slot:footer>
</x-wire-modal-card>
```

- El pie queda fuera del `<form>`: el botón de envío se enlaza con `form="<id>"`.
- `x-on:click="close"` cierra el modal desde dentro, sin ir al servidor.
- Un mismo componente puede tener varios modales, cada uno con su `wire:model`.

## Modal persistente

`persistent` impide cerrarlo con clic afuera o con Esc, y quita la X. Úsalo cuando cerrar por error haga perder algo, como una
contraseña que no se vuelve a mostrar, y deja un botón explícito para cerrar.

## Inputs

```blade
<x-wire-input wire:model="form.name" label="Nombre" type="text" required autocomplete="off" />
```

- El error de validación se muestra solo, según el `wire:model`.
- `class` se aplica al contenedor, no al `<input>`. Para llegar al input: `class="[&_input]:font-mono"`.
- `readonly` también bloquea los clics y la selección (`pointer-events-none`). Para un valor que se copia, agrega un botón.
- Altura: 34 px en escritorio y 44 px en celular, definida en `resources/css/app.css` igual que en Flux.

## Componente y pruebas

- El modal es un componente propio: `resources/views/pages/<recursos>/⚡form-modal.blade.php`, montado en la página.
- Se abre con `#[On('...')]`.

```php
Livewire::test('pages::users.form-modal')
    ->dispatch('edit-user', userId: $user->id)
    ->assertSet('form.openModal', true);
```

Las pruebas de PHP no detectan clases que Tailwind no generó. Abre el modal en el navegador: el fondo debe cubrir el
encabezado y el menú lateral.

## Checklist

- [ ] Usa `x-wire-*` y botones de Flux.
- [ ] El texto, el título y los errores están en español.
- [ ] Revisado en el navegador a 390 px, con controles de 44 px.
- [ ] Sin errores en la consola, también al llegar con `wire:navigate`.
