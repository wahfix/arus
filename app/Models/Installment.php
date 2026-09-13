<?php

namespace App\Models;

use App\Enums\InstallmentStatus;
use Database\Factories\InstallmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $loan_id
 * @property int $installment_number
 * @property Carbon $due_date
 * @property int $principal_due
 * @property int $interest_due
 * @property int $penalty_due
 * @property int $total_due
 * @property int $principal_paid
 * @property int $interest_paid
 * @property int $penalty_paid
 * @property int $total_paid
 * @property int $remaining_amount
 * @property string $status
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Loan $loan
 */
class Installment extends Model
{
    /** @use HasFactory<InstallmentFactory> */
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'installment_number',
        'due_date',
        'principal_due',
        'interest_due',
        'penalty_due',
        'total_due',
        'principal_paid',
        'interest_paid',
        'penalty_paid',
        'total_paid',
        'remaining_amount',
        'status',
        'paid_at',
    ];

    /**
     * @return array{
     *     installment_number: 'integer',
     *     due_date: 'date',
     *     principal_due: 'integer',
     *     interest_due: 'integer',
     *     penalty_due: 'integer',
     *     total_due: 'integer',
     *     principal_paid: 'integer',
     *     interest_paid: 'integer',
     *     penalty_paid: 'integer',
     *     total_paid: 'integer',
     *     remaining_amount: 'integer',
     *     paid_at: 'date',
     * }
     */
    protected function casts(): array
    {
        return [
            'installment_number' => 'integer',
            'due_date' => 'date',
            'principal_due' => 'integer',
            'interest_due' => 'integer',
            'penalty_due' => 'integer',
            'total_due' => 'integer',
            'principal_paid' => 'integer',
            'interest_paid' => 'integer',
            'penalty_paid' => 'integer',
            'total_paid' => 'integer',
            'remaining_amount' => 'integer',
            'paid_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Loan, $this>
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * @param  Builder<Installment>  $query
     * @return Builder<Installment>
     */
    public function scopeWhereStatus(Builder $query, ?string $status): Builder
    {
        $status = trim((string) $status);

        if ($status !== '' && InstallmentStatus::tryFrom($status) !== null) {
            $query->where('status', $status);
        }

        return $query;
    }
}
