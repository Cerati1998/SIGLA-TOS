@props([
    'title' => null,
    'breadcrumbs' => [],
])

<x-layouts::app.sidebar :title="$title">
    <main class="flex flex-col gap-5.25 p-5.25">
        @if ($breadcrumbs)
            <x-panel.page-header :title="$title" :breadcrumbs="$breadcrumbs" />
        @endif

        {{ $slot }}
    </main>
</x-layouts::app.sidebar>
