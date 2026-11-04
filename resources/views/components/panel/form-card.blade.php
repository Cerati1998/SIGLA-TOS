<flux:card {{ $attributes->class('flex max-w-190 flex-col p-0!') }}>
    @isset($heading)
        <div class="border-b border-line p-5.25">
            <h3 class="m-0 font-heading text-h3 font-normal">{{ $heading }}</h3>
        </div>
    @endisset

    <div class="grid grid-cols-[repeat(auto-fit,minmax(15rem,1fr))] items-start gap-x-6 gap-y-3.5 p-5.25">
        {{ $slot }}
    </div>

    @isset($actions)
        <div class="flex flex-wrap justify-end gap-3 border-t border-line p-5.25">
            {{ $actions }}
        </div>
    @endisset
</flux:card>
