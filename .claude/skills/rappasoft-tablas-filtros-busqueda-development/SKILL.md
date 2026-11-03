---
name: rappasoft-tablas-filtros-busqueda-development
description: "Usa este skill siempre que agregues o cambies filtros (SelectFilter y otros) o la búsqueda de una tabla Rappasoft: barra de filtros con Flux en before-tools, enlace a search y filterComponents, cómo se compone filters() sobre el builder y qué cuidar en el SQL."
---

# Filtros y búsqueda en tablas Rappasoft

La estructura de la tabla está en `rappasoft-tablas-listado-development`.

## Principio

Un filtro solo estrecha lo que devuelve `builder()`. Su closure recibe el `Builder` y le agrega condiciones; nunca empieza una
consulta nueva con `Model::query()`.

## Barra de filtros

`BaseTable` quita la barra del paquete, así que los filtros se muestran en `pages/<recursos>/partials/filters.blade.php`,
enlazado al área `before-tools` con `setConfigurableAreas`, con componentes de Flux y las medidas de la plantilla:

```blade
<div class="flex flex-wrap items-end gap-3 border-b border-line p-5.25">
    <flux:field class="min-w-60 flex-1">
        <flux:label>Buscar</flux:label>
        <flux:input wire:model.live.debounce.300ms="search" type="search" placeholder="Nombre o correo" />
    </flux:field>

    <flux:field class="w-full sm:w-50">
        <flux:label>Estado</flux:label>
        <flux:select wire:model.live="filterComponents.status">
            <flux:select.option value="">Todos</flux:select.option>
            <flux:select.option value="active">Activo</flux:select.option>
        </flux:select>
    </flux:field>
</div>
```

- La búsqueda se enlaza a `search`.
- Cada filtro se enlaza a `filterComponents.<clave>`, y la clave es el segundo argumento de `make()`.
- El estado queda en la URL: `?table-search=...` y `?table-filters[status]=...`.

## Filtro de selección

```php
SelectFilter::make('Estado', 'status')
    ->options(['' => 'Todos', 'active' => 'Activo', 'inactive' => 'Inactivo'])
    ->filter(fn (Builder $builder, string $value) => $value === 'active'
        ? $builder->whereNull('deleted_at')
        : $builder->whereNotNull('deleted_at')),
```

- La primera opción es `'' => 'Todos'`.
- Las opciones del `SelectFilter` y las de la vista deben coincidir: un valor que no está en `options` no se aplica.
- Las claves son valores en inglés (`active`) y las etiquetas, en español.
- Un filtro por catálogo arma sus opciones desde el modelo:
  `['' => 'Todas'] + Category::query()->orderBy('name')->pluck('name', 'id')->all()`.

## Búsqueda

- Búsqueda simple: `->searchable()` en la columna.
- Columna calculada: closure en `->searchable()`, siempre con `orWhere`, porque el paquete agrupa las búsquedas de todas las
  columnas.

```php
Column::make('Nombre completo')
    ->label(fn ($row) => $row->first_name.' '.$row->last_name)
    ->searchable(fn (Builder $query, string $search) => $query
        ->orWhere('first_name', 'like', "%{$search}%")
        ->orWhere('last_name', 'like', "%{$search}%")),
```

- SQL crudo siempre con bindings (`?`). Nunca concatenes lo que escribió el usuario.
- Marca como buscables solo las columnas que el usuario escribe (nombre, código, correo), no ids ni importes.
- DNI y brevete: revisa la rule de datos personales antes de mostrarlos o buscarlos. El término buscado queda en la URL, y
  esos datos no pueden ir en URLs.
- El motor es MariaDB 10.6. Prueba contra MariaDB cualquier consulta sobre columnas JSON.

## Sin probar aún en este proyecto

`DateRangeFilter` necesita flatpickr, que no se importa. Antes de usarlo:

1. Importa los assets de terceros en `resources/js/app.js`.
2. Compruébalo con `wire:navigate`.
3. Define cómo se convierten las fechas. `app.timezone` es `UTC` y el proyecto todavía no tiene zona por sede.

## Pruebas

```php
Livewire::test(UsersTable::class)
    ->set('filterComponents.status', 'inactive')
    ->assertSeeText('Dora Desactivada')
    ->assertDontSeeText('Ana Activa');
```

## Checklist

- [ ] Cada filtro compone sobre el `$builder` recibido.
- [ ] `options` y la vista tienen los mismos valores, con `''` para "Todos".
- [ ] Toda búsqueda con closure usa `orWhere`, y el SQL crudo usa bindings.
- [ ] Ningún dato personal queda en la URL.
