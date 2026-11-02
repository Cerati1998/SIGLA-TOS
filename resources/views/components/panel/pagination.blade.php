@props([
    'paginator',
])

<span>Mostrando {{ $paginator->count() }} de {{ $paginator->total() }}</span>

<div class="flex gap-2">
    <flux:button wire:click="previousPage" :disabled="$paginator->onFirstPage()">Anterior</flux:button>
    <flux:button wire:click="nextPage" :disabled="! $paginator->hasMorePages()">Siguiente</flux:button>
</div>
