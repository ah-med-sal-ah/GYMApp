<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ClientListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'photo' => $this->photo ? Storage::disk('public')->url($this->photo) : null,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'age' => $this->age,
            'email' => $this->email,
            'phone' => $this->phone,
            'reminder_status' => $this->reminderStatus(),
            'details_url' => URL::to("/api/clients/{$this->id}"),
        ];
    }
}
