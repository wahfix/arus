<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Database\Factories\LoanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $loan_number
 * @property int $customer_id
 * @property int $principal_amount
 * @property int $interest_rate_basis_points
 * @property string $interest_method
 * @property int $tenor
 * @property string $installment_frequency
 * @property Carbon|null $disbursement_date
 * @property Carbon|null $first_due_date
 * @property Carbon|null $maturity_date
 * @property int $total_interest
 * @property int $total_payable
 * @property int $installment_amount
 * @property int $outstanding_principal
 * @property int $outstanding_interest
 * @property int $outstanding_penalty
 * @property int $outstanding_total
 * @property string $status
 * @property int|null $created_by
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 * @property Carbon|null $disbursed_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer $customer
 * @property-read Collection<int, Installment> $installments
 * @property-read Collection<int, LoanStatusHistory> $statusHistories
 * @property-read User|null $createdBy
 * @property-read User|null $approvedBy
 */
class Loan extends Model
{
    /** @use HasFactory<LoanFactory> */
    use HasFactory;

    protected $fillable = [
        'loan_number',
        'customer_id',
        'principal_amount',
        'interest_rate_basis_points',
        'interest_method',
        'tenor',
        'installment_frequency',
        'disbursement_date',
        'first_due_date',
        'maturity_date',
        'total_interest',
        'total_payable',
        'installment_amount',
        'outstanding_principal',
        'outstanding_interest',
        'outstanding_penalty',
        'outstanding_total',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'disbursed_at',
        'completed_at',
    ];

    /**
     * @return array{
     *     principal_amount: 'integer',
     *     interest_rate_basis_points: 'integer',
     *     tenor: 'integer',
     *     disbursement_date: 'date',
     *     first_due_date: 'date',
     *     maturity_date: 'date',
     *     total_interest: 'integer',
     *     total_payable: 'integer',
     *     installment_amount: 'integer',
     *     outstanding_principal: 'integer',
     *     outstanding_interest: 'integer',
     *     outstanding_penalty: 'integer',
     *     outstanding_total: 'integer',
     *     approved_at: 'datetime',
     *     disbursed_at: 'datetime',
     *     completed_at: 'datetime',
     * }
     */
    protected function casts(): array
    {
        return [
            'principal_amount' => 'integer',
            'interest_rate_basis_points' => 'integer',
            'tenor' => 'integer',
            'disbursement_date' => 'date',
            'first_due_date' => 'date',
            'maturity_date' => 'date',
            'total_interest' => 'integer',
            'total_payable' => 'integer',
            'installment_amount' => 'integer',
            'outstanding_principal' => 'integer',
            'outstanding_interest' => 'integer',
            'outstanding_penalty' => 'integer',
            'outstanding_total' => 'integer',
            'approved_at' => 'datetime',
            'disbursed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<Installment, $this>
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class)->orderBy('installment_number');
    }

    /**
     * @return HasMany<LoanStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(LoanStatusHistory::class)->orderByDesc('changed_at');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @param  Builder<Loan>  $query
     * @return Builder<Loan>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term !== null && trim($term) !== '') {
            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('loan_number', 'like', "%{$term}%")
                    ->orWhereHas('customer', function (Builder $customer) use ($term): void {
                        $customer->where('full_name', 'like', "%{$term}%")
                            ->orWhere('customer_code', 'like', "%{$term}%");
                    });
            });
        }

        return $query;
    }

    /**
     * @param  Builder<Loan>  $query
     * @return Builder<Loan>
     */
    public function scopeWhereStatus(Builder $query, ?string $status): Builder
    {
        $status = trim((string) $status);

        if ($status !== '' && LoanStatus::tryFrom($status) !== null) {
            $query->where('status', $status);
        }

        return $query;
    }
}
