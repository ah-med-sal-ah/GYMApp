<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $remainingDays = $this->remainingDays();

        return [
            'id' => $this->id,
            'photo' => $this->photo ? Storage::disk('public')->url($this->photo) : null,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'age' => $this->age,
            'email' => $this->email,
            'phone' => $this->phone,
            'sports' => $this->sports->map(fn ($sport) => [
                'id' => $sport->id,
                'name' => $sport->name,
            ]),
            'sports_count' => $this->sports->count(),
            'coach' => $this->coach ? [
                'id' => $this->coach->id,
                'name' => "{$this->coach->first_name} {$this->coach->last_name}",
            ] : null,
            'registration_type' => $this->registration_type,
            'registration_start' => $this->registration_start->toDateString(),
            'registration_end' => $this->registration_end->toDateString(),
            'remaining_days' => $remainingDays,
            'status' => $remainingDays > 0 ? 'active' : 'expired',
            'email_reminder_enabled' => $this->email_reminder_enabled,
            'reminder_sent_at' => $this->reminder_sent_at?->toDateTimeString(),
            'reminder_status' => $this->reminderStatus(),
        ];
    }

    private function remainingDays(): int
    {
        $today = Carbon::today();
        $end = Carbon::parse($this->registration_end)->startOfDay();

        return max(0, $today->diffInDays($end, false));
    }
}
