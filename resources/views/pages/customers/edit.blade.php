<x-layouts::app :title="__('Edit Nasabah')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-1">
            <flux:heading size="xl">{{ __('Edit Nasabah') }}</flux:heading>
            <flux:text class="text-sm">{{ $customer->full_name }} ({{ $customer->customer_code }})</flux:text>
        </div>

        <x-auth-session-status :status="session('status')" />

        <flux:card>
            <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('pages.customers._form', ['submitLabel' => __('Simpan Perubahan')])
            </form>
        </flux:card>
    </div>
</x-layouts::app>