<x-layouts::app :title="$loan->loan_number">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('loans.index')" wire:navigate>{{ __('Pinjaman') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item :href="route('customers.show', $loan->customer)" wire:navigate>{{ $loan->customer->full_name }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>{{ $loan->loan_number }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>

                <div class="flex items-center gap-3">
                    <flux:heading size="xl">{{ $loan->loan_number }}</flux:heading>
                    @include('partials.loan-status-badge', ['status' => $loan->status])
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($loan->status === 'DRAFT' && auth()->user()->can('loan.update'))
                    <flux:button :href="route('loans.edit', $loan)" variant="primary" wire:navigate iconTrailing="pencil-square">
                        {{ __('Edit') }}
                    </flux:button>
                @endif

                @if ($loan->status === 'DRAFT' && auth()->user()->can('loan.submit'))
                    <form method="POST" action="{{ route('loans.submit', $loan) }}">
                        @csrf
                        <flux:button type="submit" variant="primary">{{ __('Ajukan') }}</flux:button>
                    </form>
                @endif

                @if ($loan->status === 'SUBMITTED' && auth()->user()->can('loan.approve'))
                    <form method="POST" action="{{ route('loans.review', $loan) }}">
                        @csrf
                        <flux:button type="submit" variant="primary">{{ __('Mulai Review') }}</flux:button>
                    </form>
                @endif

                @if ($loan->status === 'UNDER_REVIEW' && auth()->user()->can('loan.approve'))
                    <form method="POST" action="{{ route('loans.approve', $loan) }}">
                        @csrf
                        <flux:button type="submit" variant="success">{{ __('Setujui') }}</flux:button>
                    </form>
                @endif

                @if ($loan->status === 'UNDER_REVIEW' && auth()->user()->can('loan.reject'))
                    <form method="POST" action="{{ route('loans.reject', $loan) }}"
                          onsubmit="return confirm('{{ __('Tolak pinjaman ini?') }}')">
                        @csrf
                        <flux:button type="submit" variant="danger">{{ __('Tolak') }}</flux:button>
                    </form>
                @endif

                @if ($loan->status === 'APPROVED' && auth()->user()->can('loan.approve'))
                    <form method="POST" action="{{ route('loans.prepare', $loan) }}">
                        @csrf
                        <flux:button type="submit" variant="primary">{{ __('Siapkan Pencairan') }}</flux:button>
                    </form>
                @endif

                @if ($loan->status === 'READY_FOR_DISBURSEMENT' && auth()->user()->can('loan.disburse'))
                    <form method="POST" action="{{ route('loans.disburse', $loan) }}"
                          onsubmit="return confirm('{{ __('Cairkan pinjaman ini dan buat jadwal angsuran?') }}')">
                        @csrf
                        <flux:button type="submit" variant="success">{{ __('Cairkan') }}</flux:button>
                    </form>
                @endif

                @if (in_array($loan->status, ['DRAFT', 'SUBMITTED'], true) && auth()->user()->can('loan.status'))
                    <form method="POST" action="{{ route('loans.cancel', $loan) }}"
                          onsubmit="return confirm('{{ __('Batalkan pinjaman ini?') }}')">
                        @csrf
                        <flux:button type="submit" variant="danger">{{ __('Batalkan') }}</flux:button>
                    </form>
                @endif

                @if ($loan->status === 'ACTIVE' && auth()->user()->can('loan.status'))
                    <form method="POST" action="{{ route('loans.mark-overdue', $loan) }}">
                        @csrf
                        <flux:button type="submit" variant="warning">{{ __('Tandai Menunggak') }}</flux:button>
                    </form>
                @endif

                @if ($loan->status === 'OVERDUE' && auth()->user()->can('loan.status'))
                    <form method="POST" action="{{ route('loans.clear-overdue', $loan) }}">
                        @csrf
                        <flux:button type="submit">{{ __('Lepas Menunggak') }}</flux:button>
                    </form>

                    <form method="POST" action="{{ route('loans.default', $loan) }}"
                          onsubmit="return confirm('{{ __('Tandai pinjaman macet?') }}')">
                        @csrf
                        <flux:button type="submit" variant="danger">{{ __('Tandai Macet') }}</flux:button>
                    </form>
                @endif

                @if (in_array($loan->status, ['ACTIVE', 'OVERDUE'], true) && auth()->user()->can('loan.status'))
                    <form method="POST" action="{{ route('loans.complete', $loan) }}"
                          onsubmit="return confirm('{{ __('Tandai pinjaman lunas?') }}')">
                        @csrf
                        <flux:button type="submit" variant="primary">{{ __('Tandai Lunas') }}</flux:button>
                    </form>
                @endif
            </div>
        </div>

        <x-auth-session-status :status="session('status')" />

        @if (session('error'))
            <flux:callout color="red" icon="exclamation-triangle">
                {{ session('error') }}
            </flux:callout>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <flux:card class="lg:col-span-2">
                <div class="flex flex-col gap-4">
                    <flux:heading size="lg">{{ __('Kontrak') }}</flux:heading>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Nasabah') }}</dt>
                            <dd class="text-sm">{{ $loan->customer->full_name }} ({{ $loan->customer->customer_code }})</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Metode Bunga') }}</dt>
                            <dd class="text-sm">
                                {{ App\Enums\InterestMethod::tryFrom($loan->interest_method)?->label() }}
                                ({{ $loan->interest_rate_basis_points / 100 }}%)
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tenor') }}</dt>
                            <dd class="text-sm">
                                {{ $loan->tenor.' '.($loan->installment_frequency === 'WEEKLY' ? __('minggu') : __('bulan')) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Angsuran per periode') }}</dt>
                            <dd class="text-sm">{{ App\Support\Money::formatRupiah($loan->installment_amount) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Tanggal Pencairan') }}</dt>
                            <dd class="text-sm">{{ $loan->disbursement_date?->isoFormat('D MMMM YYYY') ?? '–' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jatuh Tempo Pertama') }}</dt>
                            <dd class="text-sm">{{ $loan->first_due_date?->isoFormat('D MMMM YYYY') ?? '–' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jatuh Tempo Akhir') }}</dt>
                            <dd class="text-sm">{{ $loan->maturity_date?->isoFormat('D MMMM YYYY') ?? '–' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Dibuat oleh') }}</dt>
                            <dd class="text-sm">{{ $loan->createdBy?->name ?? '–' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Disetujui oleh') }}</dt>
                            <dd class="text-sm">{{ $loan->approvedBy?->name ?? '–' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Diajukan pada') }}</dt>
                            <dd class="text-sm">{{ $loan->created_at?->isoFormat('D MMMM YYYY HH:mm') }}</dd>
                        </div>
                    </dl>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex flex-col gap-4">
                    <flux:heading size="lg">{{ __('Ringkasan Keuangan') }}</flux:heading>

                    <dl class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Pokok') }}</dt>
                            <dd class="text-sm">{{ App\Support\Money::formatRupiah($loan->principal_amount) }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Bunga total') }}</dt>
                            <dd class="text-sm">{{ App\Support\Money::formatRupiah($loan->total_interest) }}</dd>
                        </div>
                        <flux:separator />
                        <div class="flex items-center justify-between">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total yang harus dibayar') }}</dt>
                            <dd class="text-sm font-medium">{{ App\Support\Money::formatRupiah($loan->total_payable) }}</dd>
                        </div>
                        @if (in_array($loan->status, ['ACTIVE', 'OVERDUE'], true))
                            <flux:separator />
                            <div class="flex items-center justify-between">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Sisa pokok') }}</dt>
                                <dd class="text-sm">{{ App\Support\Money::formatRupiah($loan->outstanding_principal) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Sisa bunga') }}</dt>
                                <dd class="text-sm">{{ App\Support\Money::formatRupiah($loan->outstanding_interest) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Sisa total') }}</dt>
                                <dd class="text-sm font-medium text-amber-600 dark:text-amber-400">
                                    {{ App\Support\Money::formatRupiah($loan->outstanding_total) }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </flux:card>
        </div>

        @if ($loan->installments->isNotEmpty())
            <flux:card>
                <div class="flex flex-col gap-4">
                    <flux:heading size="lg">{{ __('Jadwal Angsuran') }}</flux:heading>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('No.') }}</flux:table.column>
                            <flux:table.column>{{ __('Jatuh Tempo') }}</flux:table.column>
                            <flux:table.column>{{ __('Pokok') }}</flux:table.column>
                            <flux:table.column>{{ __('Bunga') }}</flux:table.column>
                            <flux:table.column>{{ __('Total') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($loan->installments as $installment)
                                <flux:table.row :key="$installment->id">
                                    <flux:table.cell>{{ $installment->installment_number }}</flux:table.cell>
                                    <flux:table.cell>{{ $installment->due_date?->isoFormat('D MMM YYYY') }}</flux:table.cell>
                                    <flux:table.cell>{{ App\Support\Money::formatRupiah($installment->principal_due) }}</flux:table.cell>
                                    <flux:table.cell>{{ App\Support\Money::formatRupiah($installment->interest_due) }}</flux:table.cell>
                                    <flux:table.cell>{{ App\Support\Money::formatRupiah($installment->total_due) }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge :color="match ($installment->status) {
                                            'PENDING' => 'blue',
                                            'PARTIALLY_PAID' => 'amber',
                                            'PAID' => 'green',
                                            'OVERDUE' => 'red',
                                            'WAIVED' => 'zinc',
                                            default => 'zinc',
                                        }" size="sm">
                                            {{ App\Enums\InstallmentStatus::tryFrom($installment->status)?->label() ?? $installment->status }}
                                        </flux:badge>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        @endif

        <flux:card>
            <div class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Riwayat Status') }}</flux:heading>

                @forelse ($loan->statusHistories as $history)
                    <div class="flex gap-3">
                        <div class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-zinc-300 dark:bg-zinc-600"></div>
                        <div class="flex flex-col gap-0.5">
                            <div class="text-sm">
                                <span class="font-medium">{{ $history->to_status }}</span>
                                @if ($history->from_status)
                                    <span class="text-zinc-400 dark:text-zinc-500">(dari {{ $history->from_status }})</span>
                                @endif
                            </div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $history->changed_at?->isoFormat('D MMMM YYYY HH:mm') }}
                                · {{ $history->changedBy?->name ?? __('Sistem') }}
                            </div>
                            @if ($history->notes)
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $history->notes }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <flux:text>{{ __('Belum ada riwayat status.') }}</flux:text>
                @endforelse
            </div>
        </flux:card>
    </div>
</x-layouts::app>