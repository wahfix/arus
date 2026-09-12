<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
            {{ __('Selamat datang, :name', ['name' => $user->name]) }}
        </h1>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Dashboard overview will be displayed per role.') }}
            </p>
        </div>
    </div>
</x-layouts::app>