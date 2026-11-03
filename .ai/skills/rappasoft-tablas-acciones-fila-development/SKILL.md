---
name: rappasoft-tablas-acciones-fila-development
description: "Usa este skill siempre que agregues o cambies botones o acciones de fila en una tabla Rappasoft: partial actions.blade.php, botones x-wire-button con icono Font Awesome, confirmación con confirmAction y avisos con toast (SweetAlert2), orden de una acción de fila y cómo abrir un modal desde la tabla."
---

# Acciones de fila en tablas Rappasoft

La estructura de la tabla está en `rappasoft-tablas-listado-development`. Los modales, en `wireui-modales-inputs-development`.

## El partial `actions.blade.php`

Los botones de cada fila van siempre en `resources/views/pages/<recursos>/partials/actions.blade.php`, enlazado desde la columna:

```php
Column::make('Acciones')
    ->label(fn (User $row): View => view('pages.users.partials.actions', ['user' => $row])),
```

`setConfigurableAreas` no sirve para estos botones: un área se renderiza una vez por tabla y no recibe la fila. Úsala para lo
que es de toda la tabla: el botón de creación va en `partials/options.blade.php`, enlazado a `toolbar-right-end`, siempre con
`x-panel.create-button`, que lo muestra en el naranja de acento de la marca (`highlight`) y con el icono `plus`:

```blade
<x-panel.create-button x-on:click="$openModal('user-create')">Nuevo</x-panel.create-button>
```

```blade
<div x-data="{ id: {{ $user->id }}, name: @js($user->name) }" class="flex items-center gap-1 [&_button]:max-md:size-11">
    <x-wire-button positive x-on:click="$dispatch('edit-user', { userId: id })" title="Editar" aria-label="Editar" xs>
        <i class="fa-solid fa-pen text-sm" aria-hidden="true"></i>
    </x-wire-button>

    @can('delete', $user)
        <x-wire-button
            negative
            x-on:click="confirmAction('¿Desactivar a ' + name + '?', 'Ya no podrá iniciar sesión.', () => $wire.deactivate(id))"
            wire:loading.attr="disabled"
            wire:target="deactivate"
            title="Desactivar"
            aria-label="Desactivar"
            xs
        >
            <i class="fa-solid fa-user-xmark text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @endcan
</div>
```

- **Botón:** `x-wire-button` con `xs`, solo con icono. Lleva `title` y `aria-label` en español, porque no tiene texto visible.
- **Icono:** Font Awesome solid, `<i class="fa-solid fa-pen text-sm" aria-hidden="true"></i>`. Solo se carga el estilo solid: las
  clases `fa-regular` y `fa-brands` no se ven. El menú, la barra superior y los campos siguen con Feather (`x-feather`).
- **Color por acción, siempre igual:**

  | Acción | Botón | Color |
  |---|---|---|
  | Editar | `positive` | Verde SIGLA (`status-green`), con icono blanco como las demás acciones |
  | Eliminar o desactivar | `negative` | Rojo de error (`danger`) |
  | Otras, como reactivar | `primary` | Azul Macromar (`brand`) |

  Esos colores están enlazados a los tokens del tema. Otro color (`green`, `blue`, `red`) usa la paleta de Tailwind y no los
  tokens.
- **Mientras se ejecuta:** el botón que llama a un método lleva `wire:loading.attr="disabled"` y `wire:target` con ese método, también
  si lo llama con `$wire`. Así se desactiva en todas las filas. El que solo emite un evento, como editar, no lo necesita: el
  método es de otro componente y `wire:loading` no lo ve.
- **Botones que se alternan en el mismo lugar**, como desactivar y reactivar: los dos llevan `wire:target="deactivate,reactivate"`.
  Livewire lee el target una sola vez y lo conserva cuando reutiliza el botón.
- **Celular:** `[&_button]:max-md:size-11` en el contenedor, para que midan 44 px.
- **Datos de la fila:** van en el `x-data`, con `@js()` para los textos, porque escapa comillas y apóstrofos (`O'Brien`).
- No uses comillas dobles dentro de un atributo de un componente: por ejemplo, `{{ Js::from("...") }}` corta el atributo y deja
  una expresión rota en Alpine.
- El `id` debe estar seleccionado (`setAdditionalSelects`). Si la expresión queda como `{ id: , ...`, falta ese select.
- `@can` solo oculta el botón. La autorización real está en el método.
- Las pruebas buscan el botón por su `title`, porque no tiene texto: `assertSeeHtml('title="Desactivar"')`.

## Confirmar y avisar

| Qué | Con qué |
|---|---|
| Confirmar una acción | `confirmAction(titulo, texto, callback)`, de `resources/js/confirm.js`, con SweetAlert2 |
| Avisar el resultado | `$this->dispatch('toast', icon: 'success', text: '...')`, de `resources/js/toast.js`, con SweetAlert2 |
| Avisar que no procede | `$this->dispatch('toast', icon: 'warning', text: '...')` |

- El texto de SweetAlert2 se pasa con `titleText` y `text`, nunca con `title` ni `html`: esos dos interpretan HTML y pueden traer
  datos del usuario.
- No uses `wire:confirm` ni `confirm()` del navegador.
- Los avisos salen arriba a la derecha, con sombra marcada, icono de color y fondo y texto suaves según el tipo (tokens `badge-*`). `icon` acepta `success`, `warning` y
  `error`. SweetAlert2 muestra uno a la vez: un aviso nuevo reemplaza al anterior.

## Una acción de fila, en este orden

```php
public function deactivate(int $id): void
{
    $user = $this->builder()->findOrFail($id);

    $this->authorize('delete', $user);

    if ($user->trashed()) {
        $this->dispatch('toast', icon: 'warning', text: 'Este usuario ya está desactivado.');

        return;
    }

    $user->delete();

    $this->dispatch('toast', icon: 'success', text: 'Usuario desactivado.');
}
```

1. **Resolver** con `$this->builder()->findOrFail($id)`. Un id fuera de la tabla responde 404.
2. **Autorizar.** La policy necesita el registro, por eso va después de resolver.
3. **Verificar el estado.** Si no procede, aviso `warning` y `return`.
4. **Ejecutar.** La lógica de negocio con más de un paso va en un Action o Service.
5. **Avisar** con el evento `toast`.

- El método recibe `int $id`, no el modelo.
- Hasta culminar E6 no se validan roles, pero sí las reglas de la policy (por ejemplo, nadie se desactiva a sí mismo).
- Al usuario se le muestra un mensaje propio; el detalle de una excepción va a `report($th)`.

## Modales desde la tabla

- El modal es un componente Livewire aparte, montado en la página junto a la tabla. No pongas form objects en la tabla: la
  rule pide componentes livianos.
- El de edición se abre con un evento, porque carga el registro: `$dispatch('edit-user', { userId: id })`, recibido con
  `#[On('edit-user')]`.
- El de creación es otro modal y se abre solo con Alpine: `$openModal('user-create')`.
- Después de guardar, el modal refresca la tabla con `$this->dispatch('refreshDatatable')`.
- Una acción de la propia tabla no necesita refresco: Livewire vuelve a renderizarla.

## Pruebas

```php
Livewire::test(UsersTable::class)
    ->call('deactivate', $this->admin->id)
    ->assertForbidden();
```

Cubre el camino normal, la policy y el estado que no procede (`assertDispatched('toast', icon: 'warning')`).

## Checklist

- [ ] Los botones están en `partials/actions.blade.php`, como `x-wire-button` `xs` con icono Font Awesome, `title` y `aria-label`.
- [ ] Los botones que llaman a un método tienen `wire:loading.attr="disabled"` y `wire:target`.
- [ ] Los datos de la fila van por `x-data`, con `@js()` para los textos.
- [ ] Las acciones destructivas usan `confirmAction`.
- [ ] El método recibe `int $id` y resuelve con `$this->builder()`.
- [ ] Autoriza en el método, no solo con `@can`.
- [ ] Sin errores de Alpine en la consola, porque las pruebas de PHP no los detectan.
