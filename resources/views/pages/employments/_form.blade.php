@php($employment ??= null)

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <flux:input
        name="company_name"
        :label="__('Nama Perusahaan')"
        :value="old('company_name', $employment?->company_name)"
        required
        autofocus
    />

    <flux:input
        name="position"
        :label="__('Jabatan')"
        :value="old('position', $employment?->position)"
        required
    />

    <flux:input
        name="department"
        :label="__('Departemen')"
        :value="old('department', $employment?->department)"
    />

    <flux:select name="employment_type" :label="__('Jenis Pekerjaan')">
        <flux:select.option value="">{{ __('Pilih…') }}</flux:select.option>
        @foreach (['FULL_TIME' => __('Full time'), 'PART_TIME' => __('Part time'), 'CONTRACT' => __('Kontrak'), 'SELF_EMPLOYED' => __('Wiraswasta')] as $value => $label)
            <flux:select.option :value="$value" :selected="(old('employment_type', $employment?->employment_type)) === $value">
                {{ $label }}
            </flux:select.option>
        @endforeach
    </flux:select>

    <flux:input
        name="employment_start_date"
        :label="__('Tanggal Mulai Bekerja')"
        type="date"
        :value="old('employment_start_date', $employment?->employment_start_date?->toDateString())"
    />

    <flux:input
        name="estimated_monthly_income"
        :label="__('Perkiraan Penghasilan Bulanan (Rp)')"
        type="number"
        min="0"
        inputmode="numeric"
        :value="old('estimated_monthly_income', $employment?->estimated_monthly_income)"
    />

    <flux:select name="employment_status" :label="__('Status Pekerjaan')" required>
        @foreach (App\Enums\EmploymentStatus::cases() as $status)
            <flux:select.option
                :value="$status->value"
                :selected="(old('employment_status', $employment?->employment_status ?? 'ACTIVE')) === $status->value"
            >
                {{ $status->label() }}
            </flux:select.option>
        @endforeach
    </flux:select>

    <div class="lg:col-span-2">
        <flux:textarea
            name="notes"
            :label="__('Catatan')"
            :value="old('notes', $employment?->notes)"
            rows="3"
        />
    </div>
</div>

<div class="flex items-center gap-3">
    <flux:button type="submit" variant="primary">
        {{ $submitLabel }}
    </flux:button>

    <flux:button :href="route('customers.show', $customer)" wire:navigate class="cursor-pointer">
        {{ __('Batal') }}
    </flux:button>
</div>