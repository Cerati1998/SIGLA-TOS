{{-- The photos of a photo step, or the extra ones when $stepId is null, with the ones still uploading (H34). --}}
<div class="grid grid-cols-3 gap-2 md:grid-cols-5">
    @foreach ($photos as $photo)
        <button type="button" wire:key="photo-{{ $photo->id }}"
            x-on:click="openViewer({ stepId: @js($stepId), photoId: {{ $photo->id }}, section: @js($label), url: @js(route('intranet.checkpoint-photos.show', $photo)), by: @js($photo->uploader->name), at: @js($photo->created_at?->copy()->timezone(config('app.display_timezone'))->format('d/m/Y').' a las '.$photo->created_at?->copy()->timezone(config('app.display_timezone'))->format('H:i')) })"
            class="relative aspect-square overflow-hidden rounded-lg bg-control" aria-label="Ver la foto {{ $loop->iteration }}">
            <img src="{{ route('intranet.checkpoint-photos.thumbnail', $photo) }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover">
            <span class="absolute top-1.5 right-1.5 flex size-5.5 items-center justify-center rounded-full bg-status-green text-white" aria-hidden="true">
                <i class="fa-solid fa-check text-[0.625rem]"></i>
            </span>
        </button>
    @endforeach

    <div wire:ignore class="contents">
        <template x-for="item in pendingOf(@js($stepId))" x-bind:key="item.key">
            <button type="button" x-on:click="retry(item)" class="relative aspect-square overflow-hidden rounded-lg bg-control"
                x-bind:class="item.status === 'failed' && 'ring-2 ring-danger'" x-bind:aria-label="label(item)">
                <img x-bind:src="item.url" alt="" class="absolute inset-0 size-full object-cover">
                <span class="absolute inset-0 flex flex-col items-center justify-center gap-1.5 p-1.5 text-center text-xs font-medium text-white"
                    x-bind:class="item.status === 'failed' ? 'bg-badge-danger-ink/75' : 'bg-ink/55'">
                    <span x-text="label(item)"></span>
                    <span x-show="item.status === 'uploading'" class="h-1.25 w-[70%] overflow-hidden rounded-full bg-white/30">
                        <span class="block h-full bg-white" x-bind:style="`width: ${item.progress}%`"></span>
                    </span>
                </span>
            </button>
        </template>
    </div>
</div>
