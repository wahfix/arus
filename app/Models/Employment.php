<?php

namespace App\Models;

use Database\Factories\EmploymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property string $company_name
 * @property string|null $department
 * @property string $position
 * @property string|null $employment_type
 * @property Carbon|null $employment_start_date
 * @property int|null $estimated_monthly_income
 * @property string $employment_status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer $customer
 */
class Employment extends Model
{
    /** @use HasFactory<EmploymentFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'company_name',
        'department',
        'position',
        'employment_type',
        'employment_start_date',
        'estimated_monthly_income',
        'employment_status',
        'notes',
    ];

    /**
     * @return array{employment_start_date: 'date', estimated_monthly_income: 'integer'}
     */
    protected function casts(): array
    {
        return [
            'employment_start_date' => 'date',
            'estimated_monthly_income' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
