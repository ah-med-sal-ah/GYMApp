<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoachBySportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $gymId = $this->user()->id;

        return [
            'sport_id' => [
                'required',
                'integer',
                Rule::exists('sports', 'id')
                    ->where(fn ($query) => $query->where('gym_id', $gymId)->whereNull('deleted_at')),
            ],
        ];
    }
}
