<?php

namespace App\Support;

use App\Models\User;

final class Navigation
{
    /**
     * @return array<string, array{label: string, route: string, icon: string, permissions: list<string>}>
     */
    public static function items(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'home',
                'permissions' => ['dashboard.view'],
            ],
            'nasabah' => [
                'label' => 'Nasabah',
                'route' => 'customers.index',
                'icon' => 'users',
                'permissions' => ['customer.view'],
            ],
            'pinjaman' => [
                'label' => 'Pinjaman',
                'route' => 'loans.index',
                'icon' => 'document-text',
                'permissions' => ['loan.view'],
            ],
            'angsuran' => [
                'label' => 'Angsuran',
                'route' => 'installments.index',
                'icon' => 'calendar-days',
                'permissions' => ['installment.view'],
            ],
            'pembayaran' => [
                'label' => 'Pembayaran',
                'route' => 'payments.index',
                'icon' => 'banknotes',
                'permissions' => ['payment.view'],
            ],
            'penagihan' => [
                'label' => 'Penagihan',
                'route' => 'collections.index',
                'icon' => 'phone',
                'permissions' => ['collection.view'],
            ],
            'jaminan' => [
                'label' => 'Jaminan',
                'route' => 'collaterals.index',
                'icon' => 'shield-check',
                'permissions' => ['collateral.view'],
            ],
            'verifikasi' => [
                'label' => 'Verifikasi Identitas',
                'route' => 'identity-verifications.index',
                'icon' => 'identification',
                'permissions' => ['identity.view'],
            ],
            'pengambilan-jaminan' => [
                'label' => 'Pengambilan Jaminan',
                'route' => 'collateral-releases.index',
                'icon' => 'arrow-path',
                'permissions' => ['collateral.prepare_release'],
            ],
            'pengguna' => [
                'label' => 'Pengguna',
                'route' => 'users.index',
                'icon' => 'user-circle',
                'permissions' => ['user.view'],
            ],
            'laporan' => [
                'label' => 'Laporan',
                'route' => 'reports.index',
                'icon' => 'chart-bar',
                'permissions' => ['report.view'],
            ],
            'audit-log' => [
                'label' => 'Audit Log',
                'route' => 'audit-logs.index',
                'icon' => 'clipboard-document-list',
                'permissions' => ['audit.view'],
            ],
            'pengaturan' => [
                'label' => 'Pengaturan',
                'route' => 'profile.edit',
                'icon' => 'cog',
                'permissions' => [],
            ],
        ];
    }

    /**
     * Return the navigation items the given user is permitted to view.
     *
     * @return array<array-key, array{label: string, route: string, icon: string, permissions: list<string>}>
     */
    public static function forUser(User $user): array
    {
        return collect(self::items())
            ->reject(fn (array $item) => $item['route'] === 'dashboard')
            ->filter(fn (array $item) => empty($item['permissions']) || $user->hasAnyPermission($item['permissions']))
            ->values()
            ->all();
    }
}
