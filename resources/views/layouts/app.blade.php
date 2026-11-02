<x-layouts::app.sidebar :title="$title ?? null">
    <main class="flex flex-col gap-5.25 p-5.25">
        {{ $slot }}
    </main>
</x-layouts::app.sidebar>
