---
name: livewire-forms-pantalla-development
description: "Usa este skill siempre que crees o cambies un form object de una pantalla completa de captura (muchos campos, arreglos de ítems o totales): propiedades, rules() con reglas condicionales, mensajes en español, arreglos, totales calculados en el servidor, setX() para editar, #[Url] y #[Locked]."
---

# Form objects de pantalla completa

Para el form de una ventana pequeña usa `livewire-forms-modal-development`.

Hoy ninguna pantalla del proyecto usa este tipo de form: los ejemplos son genéricos. Cuando exista la primera, cópiala como
referencia y actualiza este skill.

## Principio

El form valida lo capturado, deriva lo que depende de eso (totales, valores por defecto) y contiene su lógica de guardado,
como pide la rule de Form Objects. Lo que escribe en varias tablas se guarda todo o nada, en una transacción, y la lógica de
negocio con más de un paso va en un Action o Service que el form llama.

## Dónde vive

`app/Livewire/Forms/<Recurso>Form.php`, usado como `public <Recurso>Form $form;`. Si crear y editar validan distinto, se
separan en `Create<Recurso>Form` y `Edit<Recurso>Form`.

## Propiedades

```php
class CertificationForm extends Form
{
    #[Url]
    public string $tab = 'general';

    public ?int $carrierId = null;

    public string $notes = '';

    /** @var array<int, array{code: string, quantity: string}> */
    public array $items = [];

    #[Locked]
    public string $total = '0.000';
}
```

- Propiedades con tipo, agrupadas por bloque de la pantalla.
- `#[Url]` solo en valores que el usuario elige libremente y que se validan. Nunca datos personales: el DNI y el brevete no
  van en URLs.
- Lo que fija el servidor (totales, responsables, estados) lleva `#[Locked]` y se recalcula antes de guardar.
- Cantidades e importes son strings decimales, nunca floats.

## Reglas

```php
protected function rules(): array
{
    return [
        'carrierId' => ['required', Rule::exists('carriers', 'id')],
        'notes' => [Rule::requiredIf($this->tab === 'observed'), 'nullable', 'string', 'max:500'],
        'items' => ['required', 'array', 'min:1'],
        'items.*.code' => ['required', 'string', 'max:50'],
        'items.*.quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,3'],
    ];
}
```

- Reglas que dependen de otro campo: `Rule::when(...)` y `Rule::requiredIf(...)`.
- Pertenencia a un catálogo: `Rule::exists(...)`.
- Arreglos del navegador: `items.*.campo`.
- Los nombres en inglés siguen `docs/glossary.md`.

## Mensajes

- Los errores salen en español con `lang/es/validation.php`.
- `messages()` solo para los textos que cambian de la regla por defecto.
- `validationAttributes()` para los campos propios del form, incluidos los comodines (`'items.*.quantity' => 'cantidad'`).
  Sin eso, el error muestra el nombre de la propiedad.

## Totales y edición

- `calculateTotals()` recalcula en el servidor después de cada cambio en los ítems y antes de guardar.
- Un total nunca se acepta del navegador.
- Una fórmula, un sitio: el importe de una línea lo calcula un solo método, que usan el form y quien guarda.
- `setX()` llena el form desde un registro al editar.
- Para operar con decimales, usa `Brick\Math\BigDecimal`, con `RoundingMode::HalfUp`, y redondea una sola vez. Hoy llega al
  proyecto como dependencia de Laravel: si se usa directo, pide aprobación para declararla en `composer.json`.

## Nunca

- `Modelo::create($this->all())`: se guarda con `only([...])`.
- Floats para importes o cantidades.
- Guardar en varias tablas fuera de una transacción.

## Checklist

- [ ] Propiedades con tipo; lo que fija el servidor con `#[Locked]`.
- [ ] Reglas y mensajes en español, con comodines.
- [ ] Los totales se recalculan antes de guardar.
- [ ] Lo que escribe en varias tablas va en una transacción.
- [ ] Revisados el glosario y la rule de datos personales.
