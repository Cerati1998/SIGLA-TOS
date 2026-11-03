---
name: wireui-modales-inputs-development
description: "Usa este skill siempre que crees o cambies un modal o los inputs dentro de un modal con WireUI: prefijo x-wire-*, modal-card con wire:model, modal de creación aparte que abre con $openModal, botón de envío en el pie, modal persistente, medidas de la plantilla y escaneo de Tailwind 4."
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
<x-wire-modal-card title="Editar usuario" wire:model="form.openModal">
    <form id="user-edit-form" wire:submit="save" class="grid gap-4">
        <x-pages::users.fields id-prefix="user-edit" />
    </form>

    <x-slot:footer>
        <div class="flex justify-end gap-3">
            <flux:button type="button" x-on:click="close">Cancelar</flux:button>
            <flux:button variant="primary" type="submit" form="user-edit-form" wire:loading.attr="disabled" wire:target="save">Guardar</flux:button>
        </div>
    </x-slot:footer>
</x-wire-modal-card>
```

- El pie queda fuera del `<form>`: el botón de envío se enlaza con `form="<id>"`.
- `wire:submit` solo desactiva los botones que están dentro del `<form>`. El del pie necesita `wire:loading.attr="disabled"` y
  `wire:target`, o se puede pulsar varias veces. Flux muestra el indicador de carga al desactivarse.
- `x-on:click="close"` cierra el modal desde dentro, sin ir al servidor.
- Un mismo componente puede tener varios modales, cada uno con su `wire:model`.

## Modal de creación

Es un modal propio: no reutiliza el de edición, y abrirlo no hace ninguna petición.

```blade
<x-panel.create-button x-on:click="$openModal('user-create')">Nuevo</x-panel.create-button>
```

```blade
<x-wire-modal-card
    title="Nuevo usuario"
    name="user-create"
    wire:model="form.openModal"
    x-on:open="$wire.form.name = ''; $wire.form.email = ''"
    x-on:close="$wire.$errors.any() && $wire.clearErrors()"
>
```

- `name` es el que usa `$openModal`, y `wire:model` deja que el servidor lo cierre al guardar.
- `x-on:open` vacía los campos en el navegador. Hay que nombrar cada campo.
- `x-on:close` limpia los errores en el servidor, porque Blade los pinta y el navegador no puede borrarlos. Solo hace la petición
  si hay errores. El método es propio del componente y llama a `$this->form->resetValidation()`.
- En las pruebas, `->set('form.openModal', true)` hace lo que el navegador hace al abrir.

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

- Cada modal es un componente propio: `app/Livewire/<Recursos>/CreateModal.php` y `EditModal.php`, con sus vistas
  `resources/views/pages/<recursos>/create-modal.blade.php` y `edit-modal.blade.php`, montados en la página.
- El de edición se abre con `#[On('...')]`, y el de creación con `$openModal`.

```php
Livewire::test(EditModal::class)
    ->dispatch('edit-user', userId: $user->id)
    ->assertSet('form.openModal', true);
```

Las pruebas de PHP no detectan clases que Tailwind no generó. Abre el modal en el navegador: el fondo debe cubrir el
encabezado y el menú lateral.

## Checklist

- [ ] Usa `x-wire-*` y botones de Flux.
- [ ] El modal de creación es propio y abre con `$openModal`, sin petición.
- [ ] Los botones del pie que llaman a un método se desactivan mientras se ejecuta.
- [ ] El texto, el título y los errores están en español.
- [ ] Revisado en el navegador a 390 px, con controles de 44 px.
- [ ] Sin errores en la consola, también al llegar con `wire:navigate`.
