<?php

namespace App\Http\Requests;

use App\Models\Coach;
use App\Models\Sport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
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
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->where(fn ($query) => $query->where('gym_id', $gymId)),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'registration_type' => [
                'required',
                'string',
                Rule::in(Sport::PRICE_TIERS),
                function ($attribute, $value, $fail) use ($gymId) {
                    $sportIds = $this->input('sports', []);

                    if (! is_array($sportIds) || empty($sportIds) || ! in_array($value, Sport::PRICE_TIERS, true)) {
                        return;
                    }

                    $unsupported = Sport::whereIn('id', $sportIds)
                        ->where('gym_id', $gymId)
                        ->whereNull("{$value}_price")
                        ->exists();

                    if ($unsupported) {
                        $fail('One or more selected sports do not offer this registration type.');
                    }
                },
            ],
            'sports' => ['required', 'array', 'min:1'],
            'sports.*' => [
                'integer',
                Rule::exists('sports', 'id')
                    ->where(fn ($query) => $query->where('gym_id', $gymId)->whereNull('deleted_at')),
            ],
            'coach_id' => [
                'required',
                'integer',
                Rule::exists('coaches', 'id')
                    ->where(fn ($query) => $query->where('gym_id', $gymId)->whereNull('deleted_at')),
                function ($attribute, $value, $fail) {
                    $sports = $this->input('sports', []);

                    if (is_array($sports) && ! empty($sports)
                        && ! Coach::whereKey($value)->whereIn('sport_id', $sports)->exists()
                    ) {
                        $fail('The selected coach does not belong to any of the selected sports.');
                    }
                },
            ],
        ];
    }
}
