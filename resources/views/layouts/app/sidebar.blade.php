<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Kelola')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    @if(auth()->check() && (auth()->user()->hasAnyPermission(['customer.view'])))
                        <flux:sidebar.item icon="users" :href="\Illuminate\Support\Facades\Route::has('customers.index') ? route('customers.index') : '#'" :current="request()->routeIs('customers.*')" wire:navigate>
                            {{ __('Nasabah') }}
                        </flux:sidebar.item>
                    @endif

                    @if(auth()->check() && auth()->user()->hasAnyPermission(['loan.view']))
                        <flux:sidebar.item icon="document-text" :href="\Illuminate\Support\Facades\Route::has('loans.index') ? route('loans.index') : '#'" :current="request()->routeIs('loans.*')" wire:navigate>
                            {{ __('Pinjaman') }}
                        </flux:sidebar.item>
                    @endif

                    @if(auth()->check() && auth()->user()->hasAnyPermission(['installment.view']))
                        <flux:sidebar.item icon="calendar-days" :href="\Illuminate\Support\Facades\Route::has('installments.index') ? route('installments.index') : '#'" :current="request()->routeIs('installments.*')" wire:navigate>
                            {{ __('Angsuran') }}
                        </flux:sidebar.item>
                    @endif

                    @if(auth()->check() && auth()->user()->hasAnyPermission(['payment.view']))
                        <flux:sidebar.item icon="banknotes" :href="\Illuminate\Support\Facades\Route::has('payments.index') ? route('payments.index') : '#'" :current="request()->routeIs('payments.*')" wire:navigate>
                            {{ __('Pembayaran') }}
                        </flux:sidebar.item>
                    @endif

                    @if(auth()->check() && auth()->user()->hasAnyPermission(['collection.view']))
                        <flux:sidebar.item icon="phone" :href="\Illuminate\Support\Facades\Route::has('collections.index') ? route('collections.index') : '#'" :current="request()->routeIs('collections.*')" wire:navigate>
                            {{ __('Penagihan') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Aset & Verifikasi')" class="grid">
                    @if(auth()->check() && auth()->user()->hasAnyPermission(['collateral.view']))
                        <flux:sidebar.item icon="shield-check" :href="\Illuminate\Support\Facades\Route::has('collaterals.index') ? route('collaterals.index') : '#'" :current="request()->routeIs('collaterals.*')" wire:navigate>
                            {{ __('Jaminan') }}
                        </flux:sidebar.item>
                    @endif

                    @if(auth()->check() && auth()->user()->hasAnyPermission(['identity.view']))
                        <flux:sidebar.item icon="identification" :href="\Illuminate\Support\Facades\Route::has('identity-verifications.index') ? route('identity-verifications.index') : '#'" :current="request()->routeIs('identity-verifications.*')" wire:navigate>
                            {{ __('Verifikasi Identitas') }}
                        </flux:sidebar.item>
                    @endif

                    @if(auth()->check() && auth()->user()->hasAnyPermission(['collateral.prepare_release']))
                        <flux:sidebar.item icon="arrow-path" :href="\Illuminate\Support\Facades\Route::has('collateral-releases.index') ? route('collateral-releases.index') : '#'" :current="request()->routeIs('collateral-releases.*')" wire:navigate>
                            {{ __('Pengambilan Jaminan') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Sistem')" class="grid">
                    @if(auth()->check() && auth()->user()->hasAnyPermission(['user.view']))
                        <flux:sidebar.item icon="user-circle" :href="\Illuminate\Support\Facades\Route::has('users.index') ? route('users.index') : '#'" :current="request()->routeIs('users.*')" wire:navigate>
                            {{ __('Pengguna') }}
                        </flux:sidebar.item>
                    @endif

                    @if(auth()->check() && auth()->user()->hasAnyPermission(['report.view']))
                        <flux:sidebar.item icon="chart-bar" :href="\Illuminate\Support\Facades\Route::has('reports.index') ? route('reports.index') : '#'" :current="request()->routeIs('reports.*')" wire:navigate>
                            {{ __('Laporan') }}
                        </flux:sidebar.item>
                    @endif

                    @if(auth()->check() && auth()->user()->hasAnyPermission(['audit.view']))
                        <flux:sidebar.item icon="clipboard-document-list" :href="\Illuminate\Support\Facades\Route::has('audit-logs.index') ? route('audit-logs.index') : '#'" :current="request()->routeIs('audit-logs.*')" wire:navigate>
                            {{ __('Audit Log') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog" :href="route('profile.edit')" :current="request()->routeIs('profile.edit')" wire:navigate>
                    {{ __('Pengaturan') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Pengaturan') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>