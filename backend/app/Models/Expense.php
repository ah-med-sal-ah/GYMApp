<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    public const CATEGORIES = [
        'Equipment',
        'Maintenance',
        'Salary',
        'Rent',
        'Utilities',
        'Other',
    ];

    protected $fillable = [
        'gym_id',
        'title',
        'description',
        'amount',
        'expense_date',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
