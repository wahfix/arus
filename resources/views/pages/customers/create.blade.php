<x-layouts::app :title="__('Tambah Nasabah')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <flux:heading size="xl">{{ __('Tambah Nasabah') }}</flux:heading>

        <x-auth-session-status :status="session('status')" />

        <flux:card>
            <form method="POST" action="{{ route('customers.store') }}" class="space-y-6">
                @csrf

                @include('pages.customers._form', ['submitLabel' => __('Simpan Nasabah')])
            </form>
        </flux:card>
    </div>
</x-layouts::app>