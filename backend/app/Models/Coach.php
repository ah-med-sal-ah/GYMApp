<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coach extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'gym_id',
        'sport_id',
        'photo',
        'first_name',
        'last_name',
        'age',
        'email',
        'phone',
        'salary',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'salary' => 'decimal:2',
        ];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
