<x-layouts::app :title="__('Nasabah')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
                <flux:heading size="xl">{{ __('Nasabah') }}</flux:heading>
                <flux:text class="text-sm">{{ __('Kelola data nasabah.') }}</flux:text>
            </div>

            @can('customer.create')
                <flux:button :href="route('customers.create')" variant="primary" wire:navigate>
                    {{ __('Tambah Nasabah') }}
                </flux:button>
            @endcan
        </div>

        <x-auth-session-status :status="session('status')" />

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <form method="GET" action="{{ route('customers.index') }}" class="flex flex-wrap items-center gap-3">
                <flux:input
                    name="q"
                    :value="request('q')"
                    :placeholder="__('Cari nama, kode, NIK, atau telepon…')"
                    class="w-full sm:max-w-xs"
                />
                <flux:button type="submit" variant="primary">{{ __('Cari') }}</flux:button>
                <flux:button :href="route('customers.index')" wire:navigate>{{ __('Reset') }}</flux:button>
            </form>
        </div>

        <flux:table :paginate="$customers">
            <flux:table.columns>
                <flux:table.column>{{ __('Kode') }}</flux:table.column>
                <flux:table.column>{{ __('Nama Lengkap') }}</flux:table.column>
                <flux:table.column>{{ __('Telepon') }}</flux:table.column>
                <flux:table.column>{{ __('Kota') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column class="w-32">{{ __('Aksi') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($customers as $customer)
                    <flux:table.row :key="$customer->id">
                        <flux:table.cell class="font-mono text-xs">{{ $customer->customer_code }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:link :href="route('customers.show', $customer)" wire:navigate>
                                {{ $customer->full_name }}
                            </flux:link>
                        </flux:table.cell>
                        <flux:table.cell>{{ $customer->phone }}</flux:table.cell>
                        <flux:table.cell>{{ $customer->city ?? '–' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="match ($customer->status) {
                                'ACTIVE' => 'green',
                                'INACTIVE' => 'amber',
                                'BLOCKED' => 'red',
                            }">
                                {{ App\Enums\CustomerStatus::tryFrom($customer->status)?->label() ?? $customer->status }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-1">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    :href="route('customers.show', $customer)"
                                    wire:navigate
                                    iconTrailing="arrow-right"
                                    class="!rounded-full"
                                >
                                    {{ __('Detail') }}
                                </flux:button>

                                @can('customer.update')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        :href="route('customers.edit', $customer)"
                                        wire:navigate
                                        iconTrailing="pencil-square"
                                        class="!rounded-full"
                                    >
                                        {{ __('Edit') }}
                                    </flux:button>
                                @endcan

                                @can('customer.delete')
                                    <flux:modal.trigger name="delete-{{ $customer->id }}">
                                        <flux:button size="sm" variant="danger" icon="trash" class="!rounded-full">
                                            {{ __('Hapus') }}
                                        </flux:button>
                                    </flux:modal.trigger>

                                    <flux:modal name="delete-{{ $customer->id }}" variant="danger">
                                        <div class="space-y-6">
                                            <div>
                                                <flux:heading size="lg">{{ __('Hapus nasabah ini?') }}</flux:heading>
                                                <flux:text>
                                                    {{ __('Nasabah :name (:code) akan dihapus. Tindakan ini tidak dapat dibatalkan.', [
                                                        'name' => $customer->full_name,
                                                        'code' => $customer->customer_code,
                                                    ]) }}
                                                </flux:text>
                                            </div>

                                            <div class="flex gap-3">
                                                <flux:button variant="primary" data-flux-modal-target="delete-{{ $customer->id }}">
                                                    {{ __('Batal') }}
                                                </flux:button>

                                                <form method="POST" action="{{ route('customers.destroy', $customer) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <flux:button type="submit" variant="danger">
                                                        {{ __('Ya, hapus nasabah') }}
                                                    </flux:button>
                                                </form>
                                            </div>
                                        </div>
                                    </flux:modal>
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('Belum ada data nasabah.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</x-layouts::app>