<x-layouts::app :title="__('Angsuran')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-1">
            <flux:heading size="xl">{{ __('Angsuran') }}</flux:heading>
            <flux:text class="text-sm">{{ __('Pantau jadwal angsuran semua pinjaman aktif.') }}</flux:text>
        </div>

        <x-auth-session-status :status="session('status')" />

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <form method="GET" action="{{ route('installments.index') }}" class="flex flex-wrap items-center gap-3">
                <flux:input
                    name="q"
                    :value="request('q')"
                    :placeholder="__('Cari nomor angsuran, pinjaman, atau nasabah…')"
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
                <flux:button :href="route('installments.index')" wire:navigate>{{ __('Reset') }}</flux:button>
            </form>
        </div>

        <flux:table :paginate="$installments">
            <flux:table.columns>
                <flux:table.column>{{ __('Angsuran') }}</flux:table.column>
                <flux:table.column>{{ __('Pinjaman') }}</flux:table.column>
                <flux:table.column>{{ __('Nasabah') }}</flux:table.column>
                <flux:table.column>{{ __('Jatuh Tempo') }}</flux:table.column>
                <flux:table.column>{{ __('Total') }}</flux:table.column>
                <flux:table.column>{{ __('Sisa') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($installments as $installment)
                    <flux:table.row :key="$installment->id">
                        <flux:table.cell>{{ __('Angsuran ke-:no', ['no' => $installment->installment_number]) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:link :href="route('loans.show', $installment->loan)" wire:navigate class="font-mono text-xs">
                                {{ $installment->loan->loan_number }}
                            </flux:link>
                        </flux:table.cell>
                        <flux:table.cell>{{ $installment->loan->customer->full_name }}</flux:table.cell>
                        <flux:table.cell>{{ $installment->due_date?->isoFormat('D MMM YYYY') }}</flux:table.cell>
                        <flux:table.cell>{{ App\Support\Money::formatRupiah($installment->total_due) }}</flux:table.cell>
                        <flux:table.cell>{{ App\Support\Money::formatRupiah($installment->remaining_amount) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="match ($installment->status) {
                                'PENDING' => 'blue',
                                'PARTIALLY_PAID' => 'amber',
                                'PAID' => 'green',
                                'OVERDUE' => 'red',
                                'WAIVED' => 'zinc',
                                default => 'zinc',
                            }">
                                {{ App\Enums\InstallmentStatus::tryFrom($installment->status)?->label() ?? $installment->status }}
                            </flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('Belum ada data angsuran.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</x-layouts::app>