<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SportListResource extends JsonResource
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
            'name' => $this->name,
            'day_price' => $this->day_price !== null ? (float) $this->day_price : null,
            'week_price' => $this->week_price !== null ? (float) $this->week_price : null,
            'month_price' => $this->month_price !== null ? (float) $this->month_price : null,
            'year_price' => $this->year_price !== null ? (float) $this->year_price : null,
        ];
    }
}
