<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'gym_id',
        'coach_id',
        'photo',
        'first_name',
        'last_name',
        'age',
        'email',
        'phone',
        'registration_type',
        'registration_start',
        'registration_end',
        'reminder_sent_at',
        'email_reminder_enabled',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'registration_start' => 'date',
            'registration_end' => 'date',
            'reminder_sent_at' => 'datetime',
            'email_reminder_enabled' => 'boolean',
        ];
    }

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class, 'client_sport');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    /**
     * Machine-readable reminder status: 'sent', 'pending' or 'disabled'.
     */
    public function reminderStatus(): string
    {
        if (! $this->email_reminder_enabled) {
            return 'disabled';
        }

        return $this->reminder_sent_at !== null ? 'sent' : 'pending';
    }
}
