{{-- The photos of a photo step, or the extra ones when $stepId is null, with the ones still uploading (H34). As in the
     inspection (H18), a tap on a photo shows the eye that opens the viewer and the red bin that deletes it, while the
     checkpoint can still be changed; otherwise the tap opens the viewer. --}}
<div class="grid grid-cols-2 gap-2.5 md:grid-cols-5">
    @foreach ($photos as $photo)
        @php
            $uploadedAt = $photo->created_at?->copy()->timezone(config('app.display_timezone'));
            $viewed = [
                'stepId' => $stepId,
                'photoId' => $photo->id,
                'section' => $label,
                'url' => route('intranet.checkpoint-photos.show', $photo),
                'by' => $photo->uploader->name,
                'at' => $uploadedAt?->format('d/m/Y').' a las '.$uploadedAt?->format('H:i'),
            ];
        @endphp
        <div wire:key="photo-{{ $photo->id }}" class="relative aspect-square overflow-hidden rounded-lg bg-control"
            x-bind:class="isMenuOf({{ $photo->id }}) && 'outline-3 outline-offset-2 outline-brand'">
            <button type="button" x-on:click="tapPhoto(@js($viewed))" class="absolute inset-0 size-full" aria-label="Foto {{ $loop->iteration }}">
                <img src="{{ route('intranet.checkpoint-photos.thumbnail', $photo) }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover">
                <span class="absolute top-1.5 right-1.5 flex size-5.5 items-center justify-center rounded-full bg-status-green text-white" aria-hidden="true">
                    <i class="fa-solid fa-check text-[0.625rem]"></i>
                </span>
            </button>
            @unless ($closed)
                <div x-show="isMenuOf({{ $photo->id }})" x-cloak x-on:click.self="closeMenu" class="absolute inset-0 flex items-center justify-center bg-ink/35">
                    <button type="button" x-on:click="viewFromMenu" class="inline-flex size-11 items-center justify-center" aria-label="Ver la foto {{ $loop->iteration }}">
                        <span class="inline-flex size-8 items-center justify-center rounded-full bg-status-green text-white shadow-[0_1px_4px_rgb(0_0_0/0.3)]">
                            <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
                        </span>
                    </button>
                    <button type="button" x-on:click="removeFromMenu" wire:loading.attr="disabled" wire:target="deletePhoto" class="inline-flex size-11 items-center justify-center" aria-label="Eliminar la foto {{ $loop->iteration }}">
                        <span class="inline-flex size-8 items-center justify-center rounded-full bg-danger text-white shadow-[0_1px_4px_rgb(0_0_0/0.3)]">
                            <i class="fa-solid fa-trash text-sm" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
            @endunless
        </div>
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
