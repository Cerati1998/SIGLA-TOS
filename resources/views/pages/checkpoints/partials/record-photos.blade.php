{{-- The photos of a step, or the extra ones, in the detail of a checkpoint (H36, criterion 3), opened in its viewer. --}}
@if ($photos->isEmpty())
    <span class="text-sm text-ink-muted">Sin fotos.</span>
@else
    @php
        $first = $photos->first();
        $last = $photos->last();
        $uploaders = $photos->map(fn ($photo) => $photo->uploader->name)->unique()->implode(', ');
    @endphp
    <div class="grid grid-cols-3 gap-2 sm:grid-cols-6">
        @foreach ($photos as $photo)
            <button type="button" wire:key="photo-{{ $photo->id }}"
                x-on:click="show(@js(route('intranet.checkpoint-photos.show', $photo)), @js($photo->uploader->name), @js($lima($photo->created_at)?->format('d/m/Y H:i')))"
                class="aspect-square overflow-hidden rounded-control border border-line bg-surface max-md:min-h-11"
                aria-label="Ver la foto {{ $loop->iteration }} de {{ $label }}">
                <img src="{{ route('intranet.checkpoint-photos.thumbnail', $photo) }}" alt="" loading="lazy" class="size-full object-cover">
            </button>
        @endforeach
    </div>
    <span class="text-sm text-ink-muted">
        {{ $photos->count() === 1 ? 'Subida' : 'Subidas' }} por {{ $uploaders }} ·
        @if ($photos->count() === 1)
            {{ $lima($first->created_at)?->format('d/m/Y H:i') }}
        @else
            {{ $lima($first->created_at)?->format('d/m/Y') }}, de {{ $lima($first->created_at)?->format('H:i') }} a {{ $lima($last->created_at)?->format('H:i') }}
        @endif
    </span>
@endif
