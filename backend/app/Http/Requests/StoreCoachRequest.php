<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCoachRequest extends FormRequest
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
            'photo' => ['nullable', 'image', 'max:2048'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:18', 'max:80'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('coaches', 'email')->where(fn ($query) => $query->where('gym_id', $gymId)),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'salary' => ['required', 'numeric', 'min:0'],
            'sport_id' => [
                'required',
                'integer',
                Rule::exists('sports', 'id')
                    ->where(fn ($query) => $query->where('gym_id', $gymId)->whereNull('deleted_at')),
            ],
        ];
    }
}
