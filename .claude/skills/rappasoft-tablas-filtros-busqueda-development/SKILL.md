---
name: rappasoft-tablas-filtros-busqueda-development
description: "Usa este skill siempre que agregues o cambies filtros (SelectFilter y otros) o la búsqueda de una tabla Rappasoft: barra nativa con menú de filtros, estilos de los controles, cómo se compone filters() sobre el builder y qué cuidar en el SQL."
---

# Filtros y búsqueda en tablas Rappasoft

La estructura de la tabla está en `rappasoft-tablas-listado-development`.

## Principio

Un filtro solo estrecha lo que devuelve `builder()`. Su closure recibe el `Builder` y le agrega condiciones; nunca empieza una
consulta nueva con `Model::query()`.

## Barra de búsqueda y filtros

La barra es la nativa del paquete. `BaseTable` le da los estilos, y cada tabla solo declara lo suyo:

- **Búsqueda:** se activa con `->searchable()` en alguna columna. El texto se define con `setSearchPlaceholder('...')`.
- **Filtros:** el botón "Filtros" y su menú salen solos cuando `filters()` devuelve algo.
- **Etiquetas de filtros aplicados:** son nativas, con el color de la marca.
- **URL:** el estado queda en `?table-search=...` y `?table-filters[status]=...`.

## Filtro de selección

```php
SelectFilter::make('Estado', 'status')
    ->options(['' => 'Todos', 'active' => 'Activo', 'inactive' => 'Inactivo'])
    ->setInputAttributes(self::CONTROL_ATTRIBUTES)
    ->filter(fn (Builder $builder, string $value) => $value === 'active'
        ? $builder->whereNull('deleted_at')
        : $builder->whereNotNull('deleted_at')),
```

- La primera opción es `'' => 'Todos'`.
- `setInputAttributes(self::CONTROL_ATTRIBUTES)` en cada filtro, para que tenga el estilo y los 44 px en celular.
- Un valor que no está en `options` no se aplica.
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
- [ ] `options` empieza con `''` para "Todos", y el filtro usa `CONTROL_ATTRIBUTES`.
- [ ] Toda búsqueda con closure usa `orWhere`, y el SQL crudo usa bindings.
- [ ] Ningún dato personal queda en la URL.
