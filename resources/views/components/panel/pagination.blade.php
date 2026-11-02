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

    $linkClasses = 'flex h-9.5 min-w-9.5 items-center justify-center rounded-[2px] bg-white px-3.5 py-1.75 text-sm text-ink-muted max-md:h-11';
@endphp

<span>Mostrando {{ $paginator->firstItem() ?? 0 }} a {{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }} registros</span>

<nav aria-label="Paginación" class="flex flex-wrap items-center">
    <button type="button" wire:click="previousPage" @disabled($paginator->onFirstPage()) class="{{ $linkClasses }} cursor-pointer hover:bg-surface disabled:cursor-default disabled:opacity-50">Anterior</button>

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="{{ $linkClasses }}">{{ $element }}</span>
        @else
            @foreach (array_keys($element) as $page)
                @if ($page === $paginator->currentPage())
                    <span aria-current="page" class="{{ $linkClasses }} bg-brand! text-white!">{{ $page }}</span>
                @else
                    <button type="button" wire:key="page-{{ $page }}" wire:click="gotoPage({{ $page }})" class="{{ $linkClasses }} cursor-pointer hover:bg-surface">{{ $page }}</button>
                @endif
            @endforeach
        @endif
    @endforeach

    <button type="button" wire:click="nextPage" @disabled(! $paginator->hasMorePages()) class="{{ $linkClasses }} cursor-pointer hover:bg-surface disabled:cursor-default disabled:opacity-50">Siguiente</button>
</nav>
