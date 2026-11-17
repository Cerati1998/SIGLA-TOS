@props([
    'title',
    'label' => null,
])

{{--
    A button that opens the camera of the phone to read a barcode (H17). When a code is read, the root of this component
    gets the `barcode-detected` event with the value in `$event.detail.value`. With a label it is a big button; without
    one, an icon button to put beside a field.
--}}
<div x-data="barcodeScanner" {{ $attributes->class('contents') }}>
    @if ($label !== null)
        <button type="button" x-on:click="start" class="inline-flex h-16 w-full items-center justify-center gap-2.5 rounded-control bg-accent px-4 text-lg font-medium text-white">
            <x-feather name="maximize" class="size-6.5" />
            {{ $label }}
        </button>
    @else
        <button type="button" x-on:click="start" class="inline-flex h-13 w-14 flex-none items-center justify-center rounded-control border border-brand bg-white text-brand" aria-label="{{ $title }}" title="{{ $title }}">
            <x-feather name="maximize" class="size-5.5" />
        </button>
    @endif

    <template x-teleport="body">
        <div x-show="open" x-cloak role="dialog" aria-modal="true" aria-label="{{ $title }}" class="fixed inset-0 z-50 flex flex-col bg-ink text-white">
            <div class="flex h-16 flex-none items-center justify-between px-4">
                <strong class="font-heading text-lg font-medium">{{ $title }}</strong>
                <button type="button" x-on:click="close" class="inline-flex size-11 items-center justify-center rounded-full bg-white/15 text-white" aria-label="Cerrar la cámara">
                    <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                </button>
            </div>

            <div class="relative flex flex-1 items-center justify-center overflow-hidden bg-black">
                <video x-ref="video" class="absolute inset-0 size-full object-cover" playsinline muted></video>
                <div x-show="! error" class="relative h-37.5 w-75 rounded-lg shadow-[0_0_0_9999px_rgba(0,0,0,0.45)] ring-4 ring-accent" aria-hidden="true"></div>
                <p x-show="! error" class="absolute inset-x-6 bottom-7 m-0 text-center text-base">Apunta al código de barras. Se lee solo, sin pulsar nada.</p>
                <p x-show="error" x-text="error" role="alert" class="relative m-6 rounded-control bg-badge-danger p-3.5 text-center text-base text-badge-danger-ink"></p>
            </div>

            <div class="flex-none p-4 pb-6">
                <button type="button" x-on:click="close" class="inline-flex h-13 w-full items-center justify-center rounded-control bg-white px-4 text-base font-medium text-brand">Escribir el código a mano</button>
            </div>
        </div>
    </template>
</div>
