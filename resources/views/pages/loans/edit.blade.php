<x-layouts::app :title="__('Edit Pinjaman')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-1">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item :href="route('loans.index')" wire:navigate>{{ __('Pinjaman') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item :href="route('loans.show', $loan)">{{ $loan->loan_number }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('Edit') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl">{{ __('Edit Pinjaman') }}</flux:heading>
        </div>

        <flux:callout color="amber" icon="exclamation-triangle">
            {{ __('Pinjaman hanya dapat diubah selama berstatus Draf.') }}
        </flux:callout>

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
            <form id="loan-form" method="POST" action="{{ route('loans.update', $loan) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('pages.loans._form', ['showCustomerSelect' => false])

                <div class="flex items-center gap-3">
                    <flux:button type="submit" variant="primary">
                        {{ __('Simpan Perubahan') }}
                    </flux:button>

                    <flux:button :href="route('loans.show', $loan)" wire:navigate>
                        {{ __('Batal') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>