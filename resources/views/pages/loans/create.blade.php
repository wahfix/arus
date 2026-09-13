<x-layouts::app :title="__('Buat Pinjaman')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-1">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item :href="route('loans.index')" wire:navigate>{{ __('Pinjaman') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('Buat Pinjaman') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl">{{ __('Buat Pinjaman') }}</flux:heading>
        </div>

        <x-auth-session-status :status="session('status')" />

        @if (session('error'))
            <flux:callout color="red" icon="exclamation-triangle">
                {{ session('error') }}
            </flux:callout>
        @endif

        <div
            x-data='loanCreation({
                csrf: @js(csrf_token()),
                previewUrl: @js(route("loans.preview")),
                form: @json($form)
            })'
        >
            <form id="loan-form" method="POST" action="{{ route('loans.store') }}" class="space-y-6">
                @csrf

                @include('pages.loans._form', ['showCustomerSelect' => true])

                <div class="flex items-center gap-3">
                    <flux:modal.trigger name="confirm-create">
                        <flux:button type="button" variant="primary">
                            {{ __('Tinjau & Konfirmasi') }}
                        </flux:button>
                    </flux:modal.trigger>

                    <flux:button :href="route('loans.index')" wire:navigate>
                        {{ __('Batal') }}
                    </flux:button>
                </div>

                <flux:modal name="confirm-create" variant="primary" :dismissible="false">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">{{ __('Konfirmasi Pengajuan Pinjaman') }}</flux:heading>
                            <flux:text>{{ __('Periksa kembali ringkasan sebelum mengajukan.') }}</flux:text>
                        </div>

                        <template x-if="loading">
                            <flux:text>{{ __('Menghitung…') }}</flux:text>
                        </template>

                        <template x-if="!loading && error">
                            <flux:callout color="red" icon="exclamation-triangle">
                                <span x-text="error"></span>
                            </flux:callout>
                        </template>

                        <template x-if="!loading && !error && preview">
                            <flux:card>
                                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Pokok') }}</dt>
                                        <dd class="text-sm" x-text="money(preview.principal_amount)"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Bunga total') }}</dt>
                                        <dd class="text-sm" x-text="money(preview.total_interest)"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total yang harus dibayar') }}</dt>
                                        <dd class="text-sm font-medium" x-text="money(preview.total_payable)"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Angsuran per periode') }}</dt>
                                        <dd class="text-sm font-medium" x-text="money(preview.installment_amount)"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jatuh tempo pertama') }}</dt>
                                        <dd class="text-sm" x-text="preview.first_due_date ?? form.first_due_date"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jatuh tempo akhir') }}</dt>
                                        <dd class="text-sm" x-text="preview.maturity_date"></dd>
                                    </div>
                                </dl>
                            </flux:card>
                        </template>

                        <div class="flex gap-3">
                            <flux:button variant="primary" data-flux-modal-target="confirm-create">
                                {{ __('Kembali') }}
                            </flux:button>

                            <flux:button type="button" variant="danger" x-on:click="document.getElementById('loan-form').submit()">
                                {{ __('Ya, buat pinjaman') }}
                            </flux:button>
                        </div>
                    </div>
                </flux:modal>
            </form>
        </div>
    </div>
</x-layouts::app>