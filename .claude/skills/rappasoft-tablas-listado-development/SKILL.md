---
name: rappasoft-tablas-listado-development
description: "Usa este skill siempre que crees o cambies una tabla de listado con rappasoft/laravel-livewire-tables (clases *Table): BaseTable, configure(), builder(), setAdditionalSelects, columnas, estilos con tokens, pruebas y qué hacer en celular."
---

# Tablas de listado (Rappasoft)

Filtros y búsqueda: `rappasoft-tablas-filtros-busqueda-development`. Botones y acciones de fila:
`rappasoft-tablas-acciones-fila-development`.

## Versión y assets

- `rappasoft/laravel-livewire-tables` `^3.10`. Las versiones anteriores a la 3.10 piden Livewire 3 y no se instalan aquí.
- En `config/livewire-tables.php` la inyección de assets está apagada (`inject_core_assets_enabled` e
  `inject_third_party_assets_enabled` en `false`).
- El JS y el CSS del núcleo se importan en `resources/js/app.js` desde
  `vendor/rappasoft/laravel-livewire-tables/resources/imports/laravel-livewire-tables`. Los de terceros (flatpickr) no se
  importan porque ninguna tabla los usa todavía.

## Dónde vive

| Qué | Dónde |
|---|---|
| Base común | `app/Livewire/Tables/BaseTable.php` |
| Tabla | `app/Livewire/Tables/<Recurso>Table.php`, creada con `php artisan make:datatable Tables/<Recurso>Table <Modelo>` y luego cambiada para extender `BaseTable` |
| Página que la monta | `resources/views/pages/<recursos>/⚡index.blade.php`, dentro de `<x-panel.list-card>`, con `<livewire:tables.<recurso>-table />` |
| Botones de cada fila | `resources/views/pages/<recursos>/partials/actions.blade.php`, siempre con ese nombre |
| Otras vistas de columna y filtros | `resources/views/pages/<recursos>/partials/` |

## BaseTable

Toda tabla extiende `BaseTable` y llama a `parent::configure()`. La base aplica la plantilla con los métodos de atributos y
tokens del tema, quita la barra de herramientas del paquete y pone el pie `partials/panel/table-footer` en `after-wrapper`,
con `x-panel.pagination`.

No se fuerzan estilos de la tabla por CSS y no se agregan variantes `dark:`: el proyecto solo tiene tema claro.

## configure()

```php
public function configure(): void
{
    parent::configure();

    $this->setDefaultSort('name')
        ->setAdditionalSelects(['users.id as id'])
        ->setEmptyMessage('No se encontraron usuarios.')
        ->setConfigurableAreas([
            'before-tools' => 'pages.users.partials.filters',
            'after-wrapper' => 'partials.panel.table-footer',
        ]);
}
```

- **`setAdditionalSelects` con la clave primaria es obligatorio.** El paquete solo selecciona las columnas declaradas, y
  `setPrimaryKey('id')` no agrega el `id`. Sin él, `$row->id` llega `null`: los botones quedan sin id y una policy con
  `$user->is($model)` da un resultado equivocado.
- También va ahí todo campo que una vista lee y que no es una columna.
- Las áreas se declaran completas en la tabla, incluida `after-wrapper`: `setConfigurableAreas` reemplaza las anteriores.
- Lo que es de toda la tabla (filtros, botón "Nuevo") va en un partial enlazado con `setConfigurableAreas`. Un área se
  renderiza una sola vez y no recibe la fila, así que los botones de fila van en `actions.blade.php`, desde la columna.
- Si una página monta más de una tabla, cada una llama a `setTableName('<nombre>')`. Con el nombre por defecto `table`
  comparten `?page`; con nombre propio la página va en `?<nombre>Page`.

## builder()

```php
public function builder(): Builder
{
    return User::withTrashed();
}
```

- Solo lleva lo que define qué registros lista la tabla. Lo que el usuario elige va en `filters()`.
- Las relaciones que lee una vista se cargan con `with()`, para evitar N+1.
- Hoy no hay ámbito por empresa o sede. Cuando exista, se aplica aquí.

## Columnas

```php
Column::make('Nombre', 'name')->searchable(),
Column::make('Estado', 'deleted_at')
    ->format(fn ($value, User $row): View => view('pages.users.partials.status', ['user' => $row])),
Column::make('Acciones')
    ->label(fn (User $row): View => view('pages.users.partials.actions', ['user' => $row])),
```

- Con campo: `Column::make('Título', 'campo')`. Sin campo: `Column::make('Título')->label(...)`.
- Una columna con marcado devuelve una vista. No hace falta `->html()`.
- `->html()` no escapa: si se usa, todo valor dinámico pasa por `e()`.
- Títulos en español.
- Nada de consultas ni reglas de negocio en `format()` o `label()`: corren por cada fila.
- `->sortable()` muestra los iconos Heroicons del paquete, y la rule pide Feather. Antes de usarlo, consulta al usuario.

## Celular

La tabla usa desplazamiento horizontal (`overflow-x-auto` y `min-w-160` en `BaseTable`), como pide la rule. No uses
`collapseOnTablet()` ni `collapseOnMobile()`.

## Pruebas

```php
Livewire::test(UsersTable::class)
    ->set('search', 'dora@')
    ->assertSeeText('Dora Desactivada');
```

Comprueba con una prueba positiva que cada fila recibe su id. Una aserción negativa (`assertDontSeeHtml`) pasa aunque el id
llegue vacío.

## Checklist

- [ ] Extiende `BaseTable` y llama a `parent::configure()`.
- [ ] `setAdditionalSelects` incluye la clave primaria y lo que leen las vistas.
- [ ] Títulos y mensaje de lista vacía en español.
- [ ] Sin `dark:`, sin colores sueltos y sin `collapseOn*`.
- [ ] Revisada en el navegador a 390 px y sin errores en la consola.
