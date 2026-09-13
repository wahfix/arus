@php($activeTab = $tab ?? 'overview')

<x-layouts::app :title="$customer->full_name">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('customers.index')" wire:navigate>{{ __('Nasabah') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item :href="route('customers.show', $customer)">{{ $customer->customer_code }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>

                <div class="flex items-center gap-3">
                    <flux:heading size="xl">{{ $customer->full_name }}</flux:heading>
                    <flux:badge :color="match ($customer->status) {
                        'ACTIVE' => 'green',
                        'INACTIVE' => 'amber',
                        'BLOCKED' => 'red',
                    }">
                        {{ App\Enums\CustomerStatus::tryFrom($customer->status)?->label() ?? $customer->status }}
                    </flux:badge>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @can('customer.update')
                    <flux:button :href="route('customers.edit', $customer)" variant="primary" wire:navigate>
                        {{ __('Edit Nasabah') }}
                    </flux:button>
                @endcan

                @can('employment.manage')
                    <flux:button :href="route('customers.employments.create', $customer)" wire:navigate>
                        {{ __('Tambah Pekerjaan') }}
                    </flux:button>
                @endcan
            </div>
        </div>

        <x-auth-session-status :status="session('status')" />

        <div x-data="{ tab: @js($activeTab) }">
            <div class="mb-4 flex flex-wrap gap-1 border-b border-zinc-200 pb-2 dark:border-zinc-700">
                @foreach ([
                    'overview' => __('Ringkasan'),
                    'loans' => __('Pinjaman'),
                    'payments' => __('Pembayaran'),
                    'collateral' => __('Jaminan'),
                    'collection' => __('Penagihan'),
                    'verification' => __('Verifikasi'),
                    'audit' => __('Audit'),
                ] as $key => $label)
                    <flux:button
                        variant="ghost"
                        size="sm"
                        x-on:click="tab = @js($key)"
                        :class="'!rounded-full ' . ($activeTab === $key ? '!bg-zinc-100 !text-zinc-900 dark:!bg-white/10 dark:!text-white' : '')"
                        x-bind:class="tab === @js($key) ? '!bg-zinc-100 !text-zinc-900 dark:!bg-white/10 dark:!text-white' : ''"
                    >
                        {{ $label }}
                    </flux:button>
                @endforeach
            </div>

            @can('customer.view')
                <section x-show="tab === 'overview'" x-cloak>
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <flux:card class="lg:col-span-2">
                            <div class="flex flex-col gap-4">
                                <flux:heading size="lg">{{ __('Data Identitas') }}</flux:heading>

                                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Kode Nasabah') }}</dt>
                                        <dd class="font-mono text-sm">{{ $customer->customer_code }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('NIK') }}</dt>
                                        <dd class="text-sm">{{ $customer->national_id_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tanggal Lahir') }}</dt>
                                        <dd class="text-sm">{{ $customer->date_of_birth?->isoFormat('D MMMM YYYY') ?? '–' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jenis Kelamin') }}</dt>
                                        <dd class="text-sm">{{ match ($customer->gender) {
                                            'MALE' => __('Laki-laki'),
                                            'FEMALE' => __('Perempuan'),
                                            default => '–',
                                        } }}</dd>
                                    </div>
                                    <div class="col-span-full">
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Alamat') }}</dt>
                                        <dd class="text-sm">{{ $customer->address ?? '–' }}</dd>
                                    </div>
                                </dl>

                                <flux:separator />

                                <flux:heading size="lg">{{ __('Kontak') }}</flux:heading>

                                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Telepon') }}</dt>
                                        <dd class="text-sm">{{ $customer->phone }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</dt>
                                        <dd class="text-sm">{{ $customer->email ?? '–' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Kota') }}</dt>
                                        <dd class="text-sm">{{ $customer->city ?? '–' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Kontak Darurat') }}</dt>
                                        <dd class="text-sm">
                                            {{ $customer->emergency_contact_name ?? '–' }}
                                            @if ($customer->emergency_contact_phone)
                                                <span class="text-zinc-500 dark:text-zinc-400">({{ $customer->emergency_contact_phone }})</span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </flux:card>

                        <flux:card>
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <flux:heading size="lg">{{ __('Pekerjaan') }}</flux:heading>
                                    @can('employment.manage')
                                        <flux:button size="sm" variant="primary" :href="route('customers.employments.create', $customer)" wire:navigate>
                                            {{ __('Tambah') }}
                                        </flux:button>
                                    @endcan
                                </div>

                                @forelse ($customer->employments as $employment)
                                    <div class="flex flex-col gap-1 border-l-2 border-zinc-200 pl-3 dark:border-zinc-700">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium">{{ $employment->company_name }}</span>
                                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $employment->position }}</span>
                                            </div>
                                            <flux:badge :color="$employment->employment_status === 'ACTIVE' ? 'green' : 'amber'" size="sm">
                                                {{ App\Enums\EmploymentStatus::tryFrom($employment->employment_status)?->label() ?? $employment->employment_status }}
                                            </flux:badge>
                                        </div>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Penghasilan: :amount', ['amount' => App\Support\Money::formatRupiah((int) ($employment->estimated_monthly_income ?? 0))]) }}
                                        </p>
                                        <div class="mt-1 flex items-center gap-2">
                                            @can('employment.manage')
                                                <flux:link :href="route('customers.employments.edit', [$customer, $employment])" size="sm" wire:navigate>
                                                    {{ __('Edit') }}
                                                </flux:link>

                                                <form method="POST" action="{{ route('customers.employments.destroy', [$customer, $employment]) }}"
                                                      onsubmit="return confirm('{{ __('Hapus pekerjaan ini?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <flux:button type="submit" size="sm" variant="danger" variant-text>
                                                        {{ __('Hapus') }}
                                                    </flux:button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Belum ada data pekerjaan.') }}</p>
                                @endforelse
                            </div>
                        </flux:card>
                    </div>
                </section>

                <section x-show="tab === 'loans'" x-cloak>
                    @can('loan.view')
                        <flux:card>
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <flux:heading size="lg">{{ __('Pinjaman') }}</flux:heading>
                                    @can('loan.create')
                                        <flux:button size="sm" variant="primary" :href="route('loans.create')" wire:navigate>
                                            {{ __('Buat Pinjaman') }}
                                        </flux:button>
                                    @endcan
                                </div>

                                @forelse ($customer->loans ?? collect() as $loan)
                                    <div class="flex items-center justify-between gap-2 border-l-2 border-zinc-200 pl-3 dark:border-zinc-700">
                                        <div class="flex flex-col">
                                            <flux:link :href="route('loans.show', $loan)" wire:navigate class="font-mono text-xs">
                                                {{ $loan->loan_number }}
                                            </flux:link>
                                            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ App\Support\Money::formatRupiah($loan->principal_amount) }} ·
                                                {{ App\Enums\InterestMethod::tryFrom($loan->interest_method)?->label() }}
                                            </span>
                                        </div>
                                        @include('partials.loan-status-badge', ['status' => $loan->status])
                                    </div>
                                @empty
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Belum ada pinjaman.') }}</p>
                                @endforelse
                            </div>
                        </flux:card>
                    @else
                        <flux:card>
                            <flux:text>{{ __('Anda tidak memiliki izin untuk melihat data pinjaman.') }}</flux:text>
                        </flux:card>
                    @endcan
                </section>

                @foreach (['payments', 'collateral', 'collection', 'verification', 'audit'] as $futureTab)
                    <section x-show="tab === @js($futureTab)" x-cloak>
                        <flux:card>
                            <flux:text>
                                {{ __('Data :tab belum tersedia pada fase ini.', ['tab' => $futureTab]) }}
                            </flux:text>
                        </flux:card>
                    </section>
                @endforeach
            @else
                <p>{{ __('Anda tidak memiliki izin untuk melihat data nasabah.') }}</p>
            @endif
        </div>
    </div>
</x-layouts::app>