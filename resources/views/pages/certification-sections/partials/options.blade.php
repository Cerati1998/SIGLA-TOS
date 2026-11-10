<div class="flex flex-wrap gap-2 max-md:[&>*]:flex-1">
    <flux:button x-on:click="$openModal('certification-section-order')" class="h-10.25! max-md:h-11!">
        <i class="fa-solid fa-sort text-sm" aria-hidden="true"></i>
        Ordenar
    </flux:button>
    <x-panel.create-button x-on:click="$openModal('certification-section-create')">Nueva sección</x-panel.create-button>
</div>
