<?php

namespace App\Services;

use App\Enums\InstallmentFrequency;
use App\Enums\InterestMethod;
use Carbon\CarbonImmutable;
use RuntimeException;

/**
 * Financial calculations for loans.
 *
 * Money is always handled as whole rupiah integers and arithmetic is performed
 * with the BC Math extension to avoid binary floating point rounding errors.
 */
class LoanCalculationService
{
    private const SCALE = 12;

    private const BASIS_POINTS_PER_UNIT = 10_000;

    /**
     * Compute the financial plan for a proposed loan.
     *
     * @param  array{
     *     principal_amount: int,
     *     interest_rate_basis_points: int,
     *     interest_method: string,
     *     tenor: int,
     *     installment_frequency: string,
     *     first_due_date: string,
     * }  $params
     * @return array{
     *     total_interest: int,
     *     total_payable: int,
     *     installment_amount: int,
     *     maturity_date: string,
     *     schedule: list<array{installment_number: int, due_date: string, principal_due: int, interest_due: int, total_due: int}>,
     * }
     */
    public function compute(array $params): array
    {
        $this->assertBcmathAvailable();

        $principal = (int) $params['principal_amount'];
        $rate = (int) $params['interest_rate_basis_points'];
        $method = InterestMethod::tryFrom($params['interest_method']) ?? throw new RuntimeException('Unknown interest method.');
        $tenor = (int) $params['tenor'];
        $frequency = InstallmentFrequency::tryFrom($params['installment_frequency']) ?? throw new RuntimeException('Unknown installment frequency.');

        if ($principal <= 0) {
            throw new RuntimeException('Principal must be greater than zero.');
        }

        if ($rate < 0) {
            throw new RuntimeException('Interest rate cannot be negative.');
        }

        if ($tenor <= 0) {
            throw new RuntimeException('Tenor must be greater than zero.');
        }

        $schedule = match ($method) {
            InterestMethod::Flat => $this->flatSchedule($principal, $rate, $tenor),
            InterestMethod::ReducingBalance => $this->reducingBalanceSchedule($principal, $rate, $tenor),
        };

        $dueDates = $this->installmentDates($params['first_due_date'], $frequency, $tenor);

        foreach ($schedule as $index => $row) {
            $schedule[$index]['due_date'] = $dueDates[$index];
        }

        $totalInterest = array_sum(array_column($schedule, 'interest_due'));
        $totalPayable = $principal + $totalInterest;

        return [
            'total_interest' => $totalInterest,
            'total_payable' => $totalPayable,
            'installment_amount' => $this->roundBc($this->bcDiv((string) $totalPayable, (string) $tenor)),
            'maturity_date' => $this->maturityDate($params['first_due_date'], $frequency, $tenor)->toDateString(),
            'schedule' => $schedule,
        ];
    }

    /**
     * Return the number of installments kept next to the given date for a frequency.
     */
    private function maturityDate(string $firstDueDate, InstallmentFrequency $frequency, int $tenor): CarbonImmutable
    {
        $date = $this->asCarbon($firstDueDate);

        return match ($frequency) {
            InstallmentFrequency::Monthly => $date->addMonths($tenor - 1),
            InstallmentFrequency::Weekly => $date->addWeeks($tenor - 1),
        };
    }

    /**
     * Schedule installment due dates for a disbursed loan.
     *
     * @return list<string>
     */
    public function installmentDates(string $firstDueDate, InstallmentFrequency $frequency, int $tenor): array
    {
        $dates = [];
        $date = $this->asCarbon($firstDueDate);

        for ($i = 0; $i < $tenor; $i++) {
            $dates[] = $date->toDateString();

            $date = match ($frequency) {
                InstallmentFrequency::Monthly => $date->addMonths(1),
                InstallmentFrequency::Weekly => $date->addWeeks(1),
            };
        }

        return $dates;
    }

    /**
     * @return list<array{installment_number: int, due_date: string, principal_due: int, interest_due: int, total_due: int}>
     */
    private function flatSchedule(int $principal, int $rateBasisPoints, int $tenor): array
    {
        $totalInterest = $this->roundBc(
            $this->bcDiv(
                $this->bcmul((string) $principal, (string) ($rateBasisPoints * $tenor)),
                (string) self::BASIS_POINTS_PER_UNIT,
            ),
        );

        $principalBase = intdiv($principal, $tenor);
        $principalRemainder = $principal - ($principalBase * $tenor);
        $interestBase = $this->roundBc($this->bcDiv((string) $totalInterest, (string) $tenor));
        $interestRemainder = $totalInterest - ($interestBase * $tenor);

        $schedule = [];

        for ($number = 1; $number <= $tenor; $number++) {
            $principalDue = $principalBase + ($number === $tenor ? $principalRemainder : 0);
            $interestDue = $interestBase + ($number === $tenor ? $interestRemainder : 0);

            $schedule[] = [
                'installment_number' => $number,
                'due_date' => '',
                'principal_due' => $principalDue,
                'interest_due' => $interestDue,
                'total_due' => $principalDue + $interestDue,
            ];
        }

        return $schedule;
    }

    /**
     * @return list<array{installment_number: int, due_date: string, principal_due: int, interest_due: int, total_due: int}>
     */
    private function reducingBalanceSchedule(int $principal, int $rateBasisPoints, int $tenor): array
    {
        $schedule = [];
        $rate = $this->bcDiv((string) $rateBasisPoints, (string) self::BASIS_POINTS_PER_UNIT);
        $outstanding = (string) $principal;

        for ($number = 1; $number <= $tenor; $number++) {
            $interestDue = $this->roundBc($this->bcmul($outstanding, $rate));

            if ($number === $tenor) {
                $principalDue = (int) $outstanding;
            } else {
                $installment = $this->monthlyInstallment((string) $principal, $rate, $tenor);
                $principalDue = max(0, $installment - $interestDue);
            }

            $outstanding = (string) ($principalDue > 0 ? $this->bcSub($outstanding, (string) $principalDue) : $outstanding);

            $schedule[] = [
                'installment_number' => $number,
                'due_date' => '',
                'principal_due' => $principalDue,
                'interest_due' => $interestDue,
                'total_due' => $principalDue + $interestDue,
            ];
        }

        return $schedule;
    }

    /**
     * Equal monthly installment A = P × r × (1+r)^n / ((1+r)^n − 1).
     *
     * @param  numeric-string  $principal
     * @param  numeric-string  $rate
     */
    private function monthlyInstallment(string $principal, string $rate, int $tenor): int
    {
        if ($this->bcComp($rate, '0') === 0) {
            return $this->roundBc($this->bcDiv($principal, (string) $tenor));
        }

        $factor = $this->bcPow(bcadd('1', $rate, self::SCALE), (string) $tenor);

        $numerator = $this->bcmul($this->bcmul($principal, $rate), $factor);
        $denominator = bcsub($factor, '1', self::SCALE);

        return $this->roundBc($this->bcDiv($numerator, $denominator));
    }

    private function asCarbon(string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date);
    }

    /**
     * Convert a percentage (e.g. "2.5") to basis points (250) using exact arithmetic.
     */
    public function percentageToBasisPoints(string $percentage): int
    {
        $this->assertBcmathAvailable();

        $percentage = trim($percentage);

        if (! is_numeric($percentage)) {
            throw new RuntimeException('Interest rate must be a numeric value.');
        }

        return $this->roundBc($this->bcmul($percentage, '100'));
    }

    /**
     * Convert basis points to a percentage string without trailing zeros (e.g. 250 → "2.5").
     */
    public function percentageFromBasisPoints(int $basisPoints): string
    {
        $value = $this->bcDiv((string) $basisPoints, '100');

        return rtrim(rtrim($value, '0'), '.');
    }

    private function assertBcmathAvailable(): void
    {
        if (! extension_loaded('bcmath')) {
            throw new RuntimeException('The bcmath extension is required for loan calculations.');
        }
    }

    /**
     * @param  numeric-string  $left
     * @param  numeric-string  $right
     * @return numeric-string
     */
    private function bcmul(string $left, string $right): string
    {
        return bcmul($left, $right, self::SCALE);
    }

    /**
     * @param  numeric-string  $left
     * @param  numeric-string  $right
     * @return numeric-string
     */
    private function bcDiv(string $left, string $right): string
    {
        return bcdiv($left, $right, self::SCALE);
    }

    /**
     * @param  numeric-string  $left
     * @param  numeric-string  $right
     * @return numeric-string
     */
    private function bcSub(string $left, string $right): string
    {
        return bcsub($left, $right, self::SCALE);
    }

    /**
     * @param  numeric-string  $left
     * @param  numeric-string  $right
     */
    private function bcComp(string $left, string $right): int
    {
        return bccomp($left, $right, self::SCALE);
    }

    /**
     * @param  numeric-string  $base
     * @param  numeric-string  $exponent
     * @return numeric-string
     */
    private function bcPow(string $base, string $exponent): string
    {
        return bcpow($base, $exponent, self::SCALE);
    }

    /**
     * Round a BC Math string to the nearest integer (half up).
     */
    private function roundBc(string $value): int
    {
        $negative = str_starts_with($value, '-');

        $sign = $negative ? '-' : '';
        $abs = ltrim($value, '-0 ');

        if ($abs === '' || $abs === '.') {
            return 0;
        }

        [$whole, $fraction] = array_pad(explode('.', $abs, 2), 2, '0');

        $whole = (int) $whole;
        $leadingFraction = (int) substr($fraction, 0, 1);

        if ($leadingFraction >= 5) {
            $whole++;
        }

        return (int) ($sign.$whole);
    }
}
