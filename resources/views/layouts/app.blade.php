<x-layouts::app.sidebar :title="$title ?? null">
    <main class="flex flex-col gap-5 px-4 pt-6 pb-10">
        {{ $slot }}
    </main>
</x-layouts::app.sidebar>
