<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sport extends Model
{
    use HasFactory, SoftDeletes;

    public const PRICE_TIERS = ['day', 'week', 'month', 'year'];

    protected $fillable = [
        'gym_id',
        'photo',
        'name',
        'day_price',
        'week_price',
        'month_price',
        'year_price',
    ];

    protected function casts(): array
    {
        return [
            'day_price' => 'decimal:2',
            'week_price' => 'decimal:2',
            'month_price' => 'decimal:2',
            'year_price' => 'decimal:2',
        ];
    }

    /**
     * Get the price for a given registration tier ('day', 'week', 'month', 'year'), or null if not offered.
     */
    public function priceFor(string $tier): ?float
    {
        $column = "{$tier}_price";

        return $this->{$column} !== null ? (float) $this->{$column} : null;
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_sport');
    }

    public function coaches(): HasMany
    {
        return $this->hasMany(Coach::class);
    }
}
