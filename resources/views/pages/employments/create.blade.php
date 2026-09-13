<x-layouts::app :title="__('Tambah Pekerjaan')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-1">
            <flux:heading size="xl">{{ __('Tambah Pekerjaan') }}</flux:heading>
            <flux:text class="text-sm">
                {{ $customer->full_name }} ({{ $customer->customer_code }})
            </flux:text>
        </div>

        <x-auth-session-status :status="session('status')" />

        <flux:card>
            <form method="POST" action="{{ route('customers.employments.store', $customer) }}" class="space-y-6">
                @csrf

                @include('pages.employments._form', ['employment' => null, 'submitLabel' => __('Simpan Pekerjaan')])
            </form>
        </flux:card>
    </div>
</x-layouts::app>