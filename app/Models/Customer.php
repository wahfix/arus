<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $customer_code
 * @property string $full_name
 * @property string $national_id_number
 * @property Carbon|null $date_of_birth
 * @property string|null $gender
 * @property string $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $city
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Employment> $employments
 * @property-read Collection<int, Loan> $loans
 */
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'customer_code',
        'full_name',
        'national_id_number',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'address',
        'city',
        'emergency_contact_name',
        'emergency_contact_phone',
        'status',
    ];

    /**
     * @return array{date_of_birth: 'date'}
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    /**
     * @return HasMany<Employment, $this>
     */
    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class);
    }

    /**
     * @return HasMany<Loan, $this>
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * @param  Builder<Customer>  $query
     * @return Builder<Customer>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term !== null && trim($term) !== '') {
            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('full_name', 'like', "%{$term}%")
                    ->orWhere('customer_code', 'like', "%{$term}%")
                    ->orWhere('national_id_number', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        return $query;
    }
}
