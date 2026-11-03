<flux:button variant="primary" :attributes="$attributes->class('[--color-accent:var(--color-highlight)] max-md:w-full')">
    <x-feather name="plus" class="size-4" />
    {{ $slot }}
</flux:button>
