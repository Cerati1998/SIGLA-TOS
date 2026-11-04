<form wire:submit="save" class="flex max-w-190 flex-col gap-5.25">
    <x-panel.form-card>
        <x-slot:heading>Identificación</x-slot:heading>

        <flux:input wire:model="form.ruc" label="RUC" type="text" inputmode="numeric" maxlength="11" required autocomplete="off" />
        <flux:input wire:model="form.ubigeo" label="Ubigeo" type="text" inputmode="numeric" maxlength="6" required autocomplete="off" />
        <flux:input wire:model="form.businessName" label="Razón social" type="text" required autocomplete="off" />
        <flux:input wire:model="form.tradeName" label="Nombre comercial (opcional)" type="text" autocomplete="off" />

        <div class="col-span-full">
            <flux:input wire:model="form.address" label="Dirección" type="text" required autocomplete="off" />
        </div>

        <x-pages::company.file-field label="Logo" model="form.logo" accept="image/png,image/jpeg" :button="$this->company->logoUrl() !== null ? 'Cambiar logo' : 'Subir logo'" hint="PNG o JPG, hasta 2 MB.">
            @if ($form->logo?->isPreviewable())
                <img src="{{ $form->logo->temporaryUrl() }}" alt="Logo elegido" class="size-16 rounded-card bg-brand/10 object-contain">
            @elseif ($this->company->logoUrl() !== null)
                <img src="{{ $this->company->logoUrl() }}" alt="Logo de la empresa" class="size-16 rounded-card bg-brand/10 object-contain">
            @else
                <x-status-badge>Sin logo</x-status-badge>
            @endif
        </x-pages::company.file-field>
    </x-panel.form-card>

    <x-panel.form-card>
        <x-slot:heading>Facturación electrónica</x-slot:heading>

        <flux:text class="col-span-full">SIGLA todavía no factura: estos datos, todos opcionales, solo se guardan.</flux:text>

        <div class="col-span-full">
            <flux:input wire:model="form.detractionAccount" label="Cuenta de detracción" type="text" autocomplete="off" />
        </div>

        <flux:input wire:model="form.solUser" label="Usuario SOL" type="text" autocomplete="off" />
        <flux:input
            wire:model="form.solPass"
            label="Clave SOL"
            type="password"
            autocomplete="new-password"
            :description:trailing="$this->company->hasCredential('sol_pass') ? 'Cargada. Déjala vacía para conservarla.' : 'Sin cargar.'"
        />
        <flux:input wire:model="form.clientId" label="Client id" type="text" autocomplete="off" />
        <flux:input
            wire:model="form.clientSecret"
            label="Client secret"
            type="password"
            autocomplete="new-password"
            :description:trailing="$this->company->hasCredential('client_secret') ? 'Cargado. Déjalo vacío para conservarlo.' : 'Sin cargar.'"
        />

        <x-pages::company.file-field label="Certificado" model="form.certificate" accept=".pem" :button="$this->company->hasCredential('certificate') ? 'Cambiar certificado' : 'Subir certificado'" hint="Archivo PEM con el certificado y su clave privada, sin contraseña, hasta 32 KB.">
            @if ($form->certificate !== null)
                <x-status-badge variant="info">{{ $form->certificate->getClientOriginalName() }}: falta guardar</x-status-badge>
            @elseif ($this->company->hasCredential('certificate'))
                <x-status-badge variant="success">Cargado</x-status-badge>
            @else
                <x-status-badge>Sin cargar</x-status-badge>
            @endif
        </x-pages::company.file-field>

        <div class="col-span-full">
            <flux:textarea wire:model="form.invoiceHeader" label="Encabezado de factura" rows="3" />
        </div>

        <div class="col-span-full">
            <flux:textarea wire:model="form.invoiceFooter" label="Pie de factura" rows="3" />
        </div>

        <div class="col-span-full">
            <flux:switch wire:model="form.production" label="Modo de producción" description="Apagado por defecto." align="left" />
        </div>

        <x-slot:actions>
            <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="save,form.logo,form.certificate">Guardar</flux:button>
        </x-slot:actions>
    </x-panel.form-card>
</form>
