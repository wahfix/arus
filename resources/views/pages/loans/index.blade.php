<x-layouts::app :title="__('Pinjaman')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <flux:heading size="xl">{{ __('Pinjaman') }}</flux:heading>
                <flux:text class="text-sm">{{ __('Kelola pengajuan dan kontrak pinjaman.') }}</flux:text>
            </div>

            @can('loan.create')
                <flux:button :href="route('loans.create')" variant="primary" wire:navigate>
                    {{ __('Buat Pinjaman') }}
                </flux:button>
            @endcan
        </div>

        <x-auth-session-status :status="session('status')" />

        @if (session('error'))
            <flux:callout color="red" icon="exclamation-triangle">
                {{ session('error') }}
            </flux:callout>
        @endif

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <form method="GET" action="{{ route('loans.index') }}" class="flex flex-wrap items-center gap-3">
                <flux:input
                    name="q"
                    :value="$statusFilter !== '' ? request('q') : request('q')"
                    :placeholder="__('Cari nomor pinjaman atau nasabah…')"
                    class="w-full sm:max-w-xs"
                />
                <flux:select name="status" class="w-full sm:max-w-xs">
                    <flux:select.option value="">{{ __('Semua Status') }}</flux:select.option>
                    @foreach ($statuses as $status)
                        <flux:select.option :value="$status->value" :selected="$statusFilter === $status->value">
                            {{ $status->label() }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:button type="submit" variant="primary">{{ __('Cari') }}</flux:button>
                <flux:button :href="route('loans.index')" wire:navigate>{{ __('Reset') }}</flux:button>
            </form>
        </div>

        <flux:table :paginate="$loans">
            <flux:table.columns>
                <flux:table.column>{{ __('Nomor') }}</flux:table.column>
                <flux:table.column>{{ __('Nasabah') }}</flux:table.column>
                <flux:table.column>{{ __('Pokok') }}</flux:table.column>
                <flux:table.column>{{ __('Total Bayar') }}</flux:table.column>
                <flux:table.column>{{ __('Tenor') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column class="w-32">{{ __('Aksi') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($loans as $loan)
                    <flux:table.row :key="$loan->id">
                        <flux:table.cell>
                            <flux:link :href="route('loans.show', $loan)" wire:navigate class="font-mono text-xs">
                                {{ $loan->loan_number }}
                            </flux:link>
                        </flux:table.cell>
                        <flux:table.cell>{{ $loan->customer->full_name }}</flux:table.cell>
                        <flux:table.cell>{{ App\Support\Money::formatRupiah($loan->principal_amount) }}</flux:table.cell>
                        <flux:table.cell>{{ App\Support\Money::formatRupiah($loan->total_payable) }}</flux:table.cell>
                        <flux:table.cell>{{ $loan->tenor.' '.($loan->installment_frequency === 'WEEKLY' ? __('minggu') : __('bulan')) }}</flux:table.cell>
                        <flux:table.cell>
                            @include('partials.loan-status-badge', ['status' => $loan->status])
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button
                                size="sm"
                                variant="ghost"
                                :href="route('loans.show', $loan)"
                                wire:navigate
                                iconTrailing="arrow-right"
                                class="!rounded-full"
                            >
                                {{ __('Detail') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('Belum ada data pinjaman.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</x-layouts::app>