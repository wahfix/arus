<?php

use App\Enums\InstallmentFrequency;
use App\Services\LoanCalculationService;

function calc(array $overrides = []): array
{
    return (new LoanCalculationService)->compute(array_merge([
        'principal_amount' => 10_000_000,
        'interest_rate_basis_points' => 200,
        'interest_method' => 'FLAT',
        'tenor' => 10,
        'installment_frequency' => 'MONTHLY',
        'first_due_date' => '2026-01-05',
    ], $overrides));
}

test('FLAT method matches the spec example', function () {
    $plan = calc();

    expect($plan['total_interest'])->toBe(2_000_000)
        ->and($plan['total_payable'])->toBe(12_000_000)
        ->and($plan['installment_amount'])->toBe(1_200_000)
        ->and($plan['maturity_date'])->toBe('2026-10-05')
        ->and(count($plan['schedule']))->toBe(10);
});

test('FLAT schedule splits principal and interest evenly', function () {
    $plan = calc();

    expect(array_sum(array_column($plan['schedule'], 'principal_due')))->toBe(10_000_000)
        ->and(array_sum(array_column($plan['schedule'], 'interest_due')))->toBe(2_000_000)
        ->and(array_sum(array_column($plan['schedule'], 'total_due')))->toBe(12_000_000);
});

test('REDUCING_BALANCE schedule closes exactly', function () {
    $plan = calc(['interest_method' => 'REDUCING_BALANCE', 'tenor' => 12]);

    expect(count($plan['schedule']))->toBe(12)
        ->and(array_sum(array_column($plan['schedule'], 'principal_due')))->toBe(10_000_000)
        ->and(array_sum(array_column($plan['schedule'], 'interest_due')))->toBe($plan['total_interest'])
        ->and($plan['total_payable'])->toBe($plan['total_interest'] + 10_000_000);
});

test('REDUCING_BALANCE equal installment matches formula', function () {
    $plan = calc(['interest_method' => 'REDUCING_BALANCE', 'tenor' => 12]);

    expect($plan['installment_amount'])->toBeGreaterThan(900_000)
        ->and($plan['installment_amount'])->toBeLessThan(1_000_000);
});

test('zero interest reducing balance divides principal evenly', function () {
    $plan = calc(['interest_method' => 'REDUCING_BALANCE', 'interest_rate_basis_points' => 0, 'tenor' => 12]);

    expect($plan['total_interest'])->toBe(0)
        ->and($plan['installment_amount'])->toBe(833_333);
});

test('percentage conversion round trips correctly', function () {
    $service = new LoanCalculationService;

    expect($service->percentageToBasisPoints('2'))->toBe(200)
        ->and($service->percentageToBasisPoints('2.5'))->toBe(250)
        ->and($service->percentageToBasisPoints('0'))->toBe(0)
        ->and($service->percentageFromBasisPoints(250))->toBe('2.5')
        ->and($service->percentageFromBasisPoints(200))->toBe('2')
        ->and($service->percentageFromBasisPoints(205))->toBe('2.05');
});

test('installment dates step monthly and weekly', function () {
    $service = new LoanCalculationService;

    expect($service->installmentDates('2026-01-05', InstallmentFrequency::Monthly, 3))
        ->toBe(['2026-01-05', '2026-02-05', '2026-03-05']);

    expect($service->installmentDates('2026-01-05', InstallmentFrequency::Weekly, 3))
        ->toBe(['2026-01-05', '2026-01-12', '2026-01-19']);
});

test('invalid method or frequency throws', function () {
    calc(['interest_method' => 'BOGUS']);
})->throws(RuntimeException::class);

test('non-positive principal throws', function () {
    calc(['principal_amount' => 0]);
})->throws(RuntimeException::class);
