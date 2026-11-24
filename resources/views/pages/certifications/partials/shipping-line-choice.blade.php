@php
    [$candidates, $others] = $options->partition(fn (\App\Models\ShippingLine $line): bool => in_array($line->id, $candidateIds, true));
    $notListed = '¿No está en la lista? Pide que la registren en Maestros → Líneas navieras.';
@endphp

{{--
    When the shipping line of SIGLA 1.0 squares with no line or with several, the inspector chooses it (T74). On the
    desktop the field goes inside the warning; on the phone, under it.
--}}
<div class="flex flex-col gap-3.5 md:rounded-control md:bg-badge-warning md:p-3.5 md:text-badge-warning-ink">
    <div role="status" class="flex items-start gap-2.5 rounded-control bg-badge-warning p-3 text-base text-badge-warning-ink md:rounded-none md:bg-transparent md:p-0 md:text-sm">
        <i class="fa-solid fa-triangle-exclamation mt-1 md:mt-0.5" aria-hidden="true"></i>
        <div class="flex min-w-0 flex-1 flex-col gap-2.5">
            @if ($candidates->isEmpty())
                <span>SIGLA 1.0 indica la naviera <strong>{{ $sigla1Name }}</strong> y no la encontramos en Líneas navieras. Elige a qué naviera corresponde.</span>
                <span class="text-sm md:hidden">{{ $notListed }}</span>
            @else
                <span>SIGLA 1.0 indica la naviera <strong>{{ $sigla1Name }}</strong> y coincide con más de una línea. Elige la que corresponde.</span>
            @endif
        </div>
    </div>

    <div class="md:ps-6.5">
        <flux:select
            wire:model="form.shippingLineId"
            label="Línea naviera"
            placeholder="Seleccionar"
            :description:trailing="'Tu elección se recuerda: las próximas solicitudes de '.$sigla1Name.' se cuadran solas.'"
            class="max-md:h-13! max-md:text-lg!"
        >
            @if ($candidates->isEmpty())
                @foreach ($others as $line)
                    <flux:select.option wire:key="shipping-line-{{ $line->id }}" value="{{ $line->id }}">{{ $line->code }} · {{ $line->name }}</flux:select.option>
                @endforeach
            @else
                <optgroup label="Coinciden con {{ $sigla1Name }}">
                    @foreach ($candidates as $line)
                        <flux:select.option wire:key="shipping-line-{{ $line->id }}" value="{{ $line->id }}">{{ $line->code }} · {{ $line->name }}</flux:select.option>
                    @endforeach
                </optgroup>
                <optgroup label="Otras navieras">
                    @foreach ($others as $line)
                        <flux:select.option wire:key="shipping-line-{{ $line->id }}" value="{{ $line->id }}">{{ $line->code }} · {{ $line->name }}</flux:select.option>
                    @endforeach
                </optgroup>
            @endif
        </flux:select>
    </div>

    @if ($candidates->isEmpty())
        <span class="text-sm max-md:hidden md:ps-6.5">{{ $notListed }}</span>
    @endif
</div>
