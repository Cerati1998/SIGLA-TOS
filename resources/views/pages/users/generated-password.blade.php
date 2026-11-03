@props([
    'password',
])

<div
    x-data="{
        copied: false,
        copy() {
            const input = this.$root.querySelector('input');

            if (navigator.clipboard) {
                navigator.clipboard.writeText(input.value);
            } else {
                input.select();
                document.execCommand('copy');
            }

            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        },
    }"
    class="flex flex-wrap items-end gap-3"
>
    <div class="min-w-0 flex-1 basis-60">
        <x-wire-input id="generated-password" label="Contraseña generada" readonly :value="$password" class="[&_input]:font-mono" />
    </div>

    <flux:button type="button" x-on:click="copy()">
        <span class="flex items-center gap-2" x-show="! copied">
            <x-feather name="copy" class="size-4" />
            Copiar
        </span>
        <span class="flex items-center gap-2" x-show="copied" x-cloak>
            <x-feather name="check" class="size-4" />
            Copiada
        </span>
    </flux:button>
</div>
