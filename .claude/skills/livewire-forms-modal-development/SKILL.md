---
name: livewire-forms-modal-development
description: "Usa este skill siempre que crees o cambies un form object de una ventana modal o de una acción puntual (un alta de dos o tres campos, pedir un motivo, enviar por correo): qué lleva el form, rules(), id bloqueado, openModal y cómo lo usa el componente."
---

# Form objects de ventana modal

Para el form de una pantalla completa usa `livewire-forms-pantalla-development`. Para el marcado del modal,
`wireui-modales-inputs-development`.

## Principio

El form valida lo que el usuario escribió, guarda el estado de la ventana y contiene su lógica de guardado, como pide la rule
de Form Objects. La lógica de negocio con más de un paso va en un Action o Service, y el form lo llama. El componente solo
coordina la ventana.

## Dónde vive

| Qué | Dónde |
|---|---|
| Form | `app/Livewire/Forms/<Recurso o Accion>Form.php` |
| En el componente | `public UserForm $form;` |
| En la vista | `wire:model="form.name"`, `wire:model="form.openModal"` |

Crear y editar son modales distintos, y pueden compartir el form si validan lo mismo (ver `UserForm`).

## Qué lleva

```php
class UserForm extends Form
{
    public bool $openModal = false;

    #[Locked]
    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public function setUser(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
    }

    protected function rules(): array
    {
        return $this->profileRules($this->userId);
    }
}
```

- Propiedades con tipo.
- El id del registro lleva `#[Locked]` y lo fija el servidor al abrir: el usuario no lo escribe.
- Las reglas van en `rules()`, sin `#[Validate]`.
- Los errores salen en español con `lang/es/validation.php`. Agrega el atributo ahí (`attributes`), o usa
  `validationAttributes()` en el form si el nombre es propio de esa ventana.
- Un dato de catálogo se valida con `Rule::exists(...)`.
- Cantidades e importes: la propiedad guarda un string decimal, nunca un float.

## Uso en el componente

```php
#[On('edit-user')]
public function edit(int $userId): void
{
    $this->form->reset();
    $this->form->resetValidation();
    $this->form->setUser(User::withTrashed()->findOrFail($userId));
    $this->form->openModal = true;
}
```

- Al abrir para editar: `reset()` y `resetValidation()`, para no heredar datos ni errores de la vez anterior.
- El modal de creación no pasa por el servidor al abrir: nunca fija el id, y limpia sus datos y errores como indica
  `wireui-modales-inputs-development`.
- Si la validación falla, la excepción corta `save()` antes de cerrar y el modal queda abierto con sus errores.
- Un dato sensible que se muestra una sola vez, como una contraseña generada, se borra del componente al cerrar: vive en el
  snapshot de Livewire mientras exista.

## Cuándo pasa a ser un form de pantalla

Si empieza a mantener arreglos de ítems, a calcular importes o crece a muchos campos, sigue
`livewire-forms-pantalla-development`.

## Checklist

- [ ] Propiedades con tipo y reglas en `rules()`.
- [ ] El id del registro afectado es `#[Locked]`.
- [ ] Se limpian los datos y los errores de la vez anterior.
- [ ] Los errores salen en español con el nombre correcto del campo.
- [ ] La lógica de negocio con más de un paso está en un Action o Service.
