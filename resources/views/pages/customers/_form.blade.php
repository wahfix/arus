@php($customer ??= null)

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <flux:input
        name="full_name"
        :label="__('Nama Lengkap')"
        :value="old('full_name', $customer?->full_name)"
        required
        autofocus
    />

    <flux:input
        name="national_id_number"
        :label="__('Nomor Induk Kependudukan (NIK)')"
        :value="old('national_id_number', $customer?->national_id_number)"
        required
        inputmode="numeric"
        maxlength="20"
    />

    <flux:input
        name="phone"
        :label="__('Nomor Telepon')"
        :value="old('phone', $customer?->phone)"
        required
        inputmode="tel"
    />

    <flux:input
        name="email"
        :label="__('Email')"
        type="email"
        :value="old('email', $customer?->email)"
    />

    <flux:input
        name="date_of_birth"
        :label="__('Tanggal Lahir')"
        type="date"
        :value="old('date_of_birth', $customer?->date_of_birth?->toDateString())"
    />

    <flux:select name="gender" :label="__('Jenis Kelamin')">
        <flux:select.option value="">{{ __('Pilih…') }}</flux:select.option>
        <flux:select.option value="MALE" :selected="(old('gender', $customer?->gender)) === 'MALE'">{{ __('Laki-laki') }}</flux:select.option>
        <flux:select.option value="FEMALE" :selected="(old('gender', $customer?->gender)) === 'FEMALE'">{{ __('Perempuan') }}</flux:select.option>
    </flux:select>

    <flux:input
        name="city"
        :label="__('Kota')"
        :value="old('city', $customer?->city)"
    />

    <flux:textarea
        name="address"
        :label="__('Alamat')"
        :value="old('address', $customer?->address)"
        rows="3"
    />

    <flux:input
        name="emergency_contact_name"
        :label="__('Kontak Darurat – Nama')"
        :value="old('emergency_contact_name', $customer?->emergency_contact_name)"
    />

    <flux:input
        name="emergency_contact_phone"
        :label="__('Kontak Darurat – Telepon')"
        :value="old('emergency_contact_phone', $customer?->emergency_contact_phone)"
        inputmode="tel"
    />

    <flux:select name="status" :label="__('Status')" required>
        @foreach (App\Enums\CustomerStatus::cases() as $status)
            <flux:select.option
                :value="$status->value"
                :selected="(old('status', $customer?->status ?? 'ACTIVE')) === $status->value"
            >
                {{ $status->label() }}
            </flux:select.option>
        @endforeach
    </flux:select>
</div>

<div class="flex items-center gap-3">
    <flux:button type="submit" variant="primary">
        {{ $submitLabel }}
    </flux:button>

    <flux:button :href="route('customers.index')" wire:navigate>
        {{ __('Batal') }}
    </flux:button>
</div>