<flux:card {{ $attributes->class('flex max-w-190 flex-col gap-6') }}>
    <div class="grid grid-cols-[repeat(auto-fit,minmax(15rem,1fr))] items-start gap-5">
        {{ $slot }}
    </div>

    <div class="flex flex-wrap justify-end gap-3 border-t border-line pt-5">
        {{ $actions }}
    </div>
</flux:card>
