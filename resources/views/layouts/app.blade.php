<x-layouts::app.sidebar :title="$title ?? null">
    <main class="flex flex-col gap-5 p-5">
        {{ $slot }}
    </main>
</x-layouts::app.sidebar>
