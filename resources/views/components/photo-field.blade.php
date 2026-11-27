@props([
    'label',
    'name',
    'title',
    'gallery' => false,
])

{{--
    A photo of a form inside a modal (T77). It lives inside an element with `x-data="photoField(...)"`
    (resources/js/photo-field.js), which takes, compresses and uploads the photo; the modal puts it on its root so its
    save button can wait for the upload. `name` is the property whose error is shown. With `gallery`, the photo can
    also be chosen from the phone instead of taken, and «Cambiar» opens the phone's own choice (H40); whether the photo
    is required is up to the rules of the form.
--}}
<div class="flex flex-col gap-1.5">
    <span class="text-sm font-medium text-ink">{{ $label }}</span>
    <input type="file" accept="image/*" capture="environment" x-ref="camera" x-on:change="captured" class="hidden" aria-hidden="true" tabindex="-1">
    @if ($gallery)
        <input type="file" accept="image/*" x-ref="gallery" x-on:change="captured" class="hidden" aria-hidden="true" tabindex="-1">
    @endif

    <div x-show="! hasPhoto && ! uploading" @class(['grid gap-2.5', 'grid-cols-2' => $gallery])>
        @foreach ($gallery ? ['take' => ['camera', 'Tomar foto'], 'pick' => ['image', 'Elegir de la galería']] : ['take' => ['camera', 'Tomar foto']] as $action => [$icon, $text])
            <button
                type="button"
                x-on:click="{{ $action }}"
                @class([
                    'flex h-30 w-full cursor-pointer flex-col items-center justify-center gap-1.5 rounded-card border-2 border-dashed text-base font-medium text-brand',
                    'border-danger bg-badge-danger' => $errors->has($name),
                    'border-control bg-surface' => ! $errors->has($name),
                ])
            >
                <x-feather :name="$icon" class="size-6.5" />
                {{ $text }}
            </button>
        @endforeach
    </div>

    <p x-show="uploading" x-cloak role="status" class="m-0 flex h-30 items-center justify-center gap-2 rounded-card border border-line bg-surface text-sm text-ink-muted">
        <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
        Preparando y subiendo la foto…
    </p>

    <div x-show="hasPhoto" x-cloak class="flex flex-col gap-2">
        <button type="button" x-on:click="openViewer" class="block cursor-zoom-in overflow-hidden rounded-card" aria-label="Ver la foto en grande">
            <img x-bind:src="thumbnailUrl" alt="" class="h-40 w-full object-cover">
        </button>
        <div class="grid grid-cols-3 gap-2">
            <flux:button size="sm" x-on:click="openViewer" class="max-md:h-11">Ver en grande</flux:button>
            <flux:button size="sm" x-on:click="{{ $gallery ? 'pick' : 'take' }}" class="max-md:h-11">{{ $gallery ? 'Cambiar' : 'Volver a tomar' }}</flux:button>
            <flux:button size="sm" x-on:click="remove" class="text-danger! max-md:h-11">Quitar</flux:button>
        </div>
    </div>

    <p x-show="failed" x-cloak role="alert" class="m-0 text-sm text-danger">No se pudo preparar o subir la foto. Tómala otra vez.</p>
    <flux:error :name="$name" />

    <template x-teleport="body">
        <div x-show="viewerOpen" x-cloak role="dialog" aria-modal="true" aria-label="{{ $title }}" x-on:keydown.escape.window="closeViewer" class="fixed inset-0 z-70 flex flex-col bg-ink text-white">
            <div class="flex h-16 flex-none items-center justify-between px-4">
                <strong class="font-heading text-lg font-medium">{{ $title }}</strong>
                <button type="button" x-on:click="closeViewer" class="inline-flex size-11 items-center justify-center rounded-full bg-white/15" aria-label="Cerrar la foto">
                    <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="flex min-h-0 flex-1 items-center justify-center bg-black p-2">
                <img x-bind:src="photoUrl" alt="{{ $title }}" class="max-h-full max-w-full object-contain">
            </div>
            <div class="flex flex-none justify-center gap-2.5 p-4 pb-6">
                <flux:button x-on:click="{{ $gallery ? 'pick' : 'take' }}" class="max-md:h-11">{{ $gallery ? 'Cambiar' : 'Volver a tomar' }}</flux:button>
                <flux:button x-on:click="remove" class="text-danger! max-md:h-11">Quitar</flux:button>
                <flux:button variant="primary" x-on:click="closeViewer" class="max-md:h-11">Cerrar</flux:button>
            </div>
        </div>
    </template>
</div>
