<?php

namespace App\Features\Lookups\Infrastructure\Persistence\Models;

use App\Features\Users\Infrastructure\Persistence\Models\User;
use Database\Factories\StatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'value',
])]
class Status extends Model
{
    /** @use HasFactory<StatusFactory> */
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): StatusFactory
    {
        return StatusFactory::new();
    }

    /**
     * Get the users that have this status.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'boolean',
        ];
    }
}
