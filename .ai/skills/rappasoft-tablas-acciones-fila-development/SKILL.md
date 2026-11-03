---
name: rappasoft-tablas-acciones-fila-development
description: "Usa este skill siempre que agregues o cambies botones o acciones de fila en una tabla Rappasoft: vista por columna con x-data, confirmación con confirmAction (SweetAlert2), avisos con Flux::toast, orden de una acción de fila y cómo abrir un modal desde la tabla."
---

# Acciones de fila en tablas Rappasoft

La estructura de la tabla está en `rappasoft-tablas-listado-development`. Los modales, en `wireui-modales-inputs-development`.

## Vista de la columna

Los datos de la fila van en un `x-data` sobre un contenedor HTML normal, y los botones de Flux los usan desde Alpine:

```blade
<div x-data="{ id: {{ $user->id }}, name: @js($user->name) }" class="flex items-center gap-1">
    <flux:button variant="ghost" x-on:click="$dispatch('edit-user', { userId: id })">Editar</flux:button>

    @can('delete', $user)
        <flux:button
            variant="ghost"
            x-on:click="confirmAction('¿Desactivar a ' + name + '?', 'Ya no podrá iniciar sesión.', () => $wire.deactivate(id))"
        >Desactivar</flux:button>
    @endcan
</div>
```

- `@js()` para los textos: escapa comillas y apóstrofos (`O'Brien`).
- No uses comillas dobles dentro de un atributo de un componente `<flux:*>`: por ejemplo, `{{ Js::from("...") }}` corta el
  atributo y deja una expresión rota en Alpine.
- El `id` debe estar seleccionado (`setAdditionalSelects`). Si la expresión queda como `{ id: , ...`, falta ese select.
- Los iconos son Feather (`x-feather`). Los Heroicons solo aparecen dentro de los componentes de WireUI o del paquete.
- `@can` solo oculta el botón. La autorización real está en el método.

## Confirmar y avisar

| Qué | Con qué |
|---|---|
| Confirmar una acción | `confirmAction(titulo, texto, callback)`, de `resources/js/confirm.js`, con SweetAlert2 |
| Avisar el resultado | `Flux::toast(variant: 'success', text: '...')` |
| Avisar que no procede | `Flux::toast(variant: 'warning', text: '...')` |

- El texto de SweetAlert2 se pasa con `text`, nunca con `html`, porque puede traer datos del usuario.
- No uses `wire:confirm` ni `confirm()` del navegador.
- Los avisos salen arriba a la derecha: `position="top end"` en los dos layouts.

## Una acción de fila, en este orden

```php
public function deactivate(int $id): void
{
    $user = $this->builder()->findOrFail($id);

    $this->authorize('delete', $user);

    if ($user->trashed()) {
        Flux::toast(variant: 'warning', text: 'Este usuario ya está desactivado.');

        return;
    }

    $user->delete();

    Flux::toast(variant: 'success', text: 'Usuario desactivado.');
}
```

1. **Resolver** con `$this->builder()->findOrFail($id)`. Un id fuera de la tabla responde 404.
2. **Autorizar.** La policy necesita el registro, por eso va después de resolver.
3. **Verificar el estado.** Si no procede, aviso `warning` y `return`.
4. **Ejecutar.** La lógica de negocio con más de un paso va en un Action o Service.
5. **Avisar** con `Flux::toast`.

- El método recibe `int $id`, no el modelo.
- Hasta culminar E6 no se validan roles, pero sí las reglas de la policy (por ejemplo, nadie se desactiva a sí mismo).
- Al usuario se le muestra un mensaje propio; el detalle de una excepción va a `report($th)`.

## Modales desde la tabla

- El modal es un componente Livewire aparte, montado en la página junto a la tabla. No pongas form objects en la tabla: la
  rule pide componentes livianos.
- Se abre con un evento: `$dispatch('edit-user', { userId: id })`, recibido con `#[On('edit-user')]`.
- Después de guardar, el modal refresca la tabla con `$this->dispatch('refreshDatatable')`.
- Una acción de la propia tabla no necesita refresco: Livewire vuelve a renderizarla.

## Pruebas

```php
Livewire::test(UsersTable::class)
    ->call('deactivate', $this->admin->id)
    ->assertForbidden();
```

Cubre el camino normal, la policy y el estado que no procede (`assertDispatched('toast-show')`).

## Checklist

- [ ] La vista recibe los datos por `x-data`, con `@js()` para los textos.
- [ ] Las acciones destructivas usan `confirmAction`.
- [ ] El método recibe `int $id` y resuelve con `$this->builder()`.
- [ ] Autoriza en el método, no solo con `@can`.
- [ ] Sin errores de Alpine en la consola, porque las pruebas de PHP no los detectan.
