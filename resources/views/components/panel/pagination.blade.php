@props([
    'paginator',
])

@php
    $window = \Illuminate\Pagination\UrlWindow::make($paginator);

    $elements = array_filter([
        $window['first'],
        is_array($window['slider']) ? '...' : null,
        $window['slider'],
        is_array($window['last']) ? '...' : null,
        $window['last'],
    ]);

    $linkClasses = 'flex h-9.5 min-w-9.5 items-center justify-center -ms-px rounded-[2px] border border-line bg-white px-3.5 py-1.75 text-sm text-ink-muted max-md:h-11';
@endphp

<span>Mostrando {{ $paginator->firstItem() ?? 0 }} a {{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }} registros</span>

<nav aria-label="Paginación" class="flex flex-wrap items-center">
    <button type="button" wire:click="previousPage" wire:loading.attr="disabled" wire:target="previousPage,gotoPage,nextPage" @disabled($paginator->onFirstPage()) class="{{ $linkClasses }} cursor-pointer hover:bg-surface disabled:cursor-default disabled:opacity-50">Anterior</button>

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="{{ $linkClasses }}">{{ $element }}</span>
        @else
            @foreach (array_keys($element) as $page)
                @if ($page === $paginator->currentPage())
                    <span aria-current="page" class="{{ $linkClasses }} bg-brand! text-white!">{{ $page }}</span>
                @else
                    <button type="button" wire:key="page-{{ $page }}" wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" wire:target="previousPage,gotoPage,nextPage" class="{{ $linkClasses }} cursor-pointer hover:bg-surface disabled:cursor-default disabled:opacity-50">{{ $page }}</button>
                @endif
            @endforeach
        @endif
    @endforeach

    <button type="button" wire:click="nextPage" wire:loading.attr="disabled" wire:target="previousPage,gotoPage,nextPage" @disabled(! $paginator->hasMorePages()) class="{{ $linkClasses }} cursor-pointer hover:bg-surface disabled:cursor-default disabled:opacity-50">Siguiente</button>
</nav>
