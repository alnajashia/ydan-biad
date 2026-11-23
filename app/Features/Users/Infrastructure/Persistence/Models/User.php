<?php

namespace App\Features\Users\Infrastructure\Persistence\Models;

use App\Features\Customers\Infrastructure\Persistence\Models\Customer;
use App\Features\Lookups\Infrastructure\Persistence\Models\Role;
use App\Features\Lookups\Infrastructure\Persistence\Models\Status;
use App\Features\Users\Domain\Enums\Gender;
use App\Features\Users\Domain\Enums\IdentityType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'full_name',
    'phone',
    'password',
    'gender',
    'status_id',
    'identity_type',
    'identity_number',
    'identity_front_image',
    'identity_back_image',
    'identity_expiry_date',
    'account_verified_at',
    'role_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Get the customer profile for the user.
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * Get the status that belongs to the user.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Get the role that belongs to the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'identity_type' => IdentityType::class,
            'identity_expiry_date' => 'date',
            'account_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
