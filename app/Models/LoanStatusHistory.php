<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $loan_id
 * @property string|null $from_status
 * @property string $to_status
 * @property int|null $changed_by
 * @property Carbon $changed_at
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Loan $loan
 * @property-read User|null $changedBy
 */
class LoanStatusHistory extends Model
{
    protected $fillable = [
        'loan_id',
        'from_status',
        'to_status',
        'changed_by',
        'changed_at',
        'notes',
    ];

    /**
     * @return array{changed_at: 'datetime'}
     */
    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
