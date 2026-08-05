<?php

namespace App\Http\Requests;

use App\Models\Sport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSportRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sports', 'name')->where(fn ($query) => $query->where('gym_id', $gymId)),
            ],
            'day_price' => ['nullable', 'numeric', 'min:0'],
            'week_price' => ['nullable', 'numeric', 'min:0'],
            'month_price' => ['nullable', 'numeric', 'min:0'],
            'year_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(
            collect(Sport::PRICE_TIERS)->mapWithKeys(fn ($tier) => [
                "{$tier}_price" => $this->filled("{$tier}_price") ? $this->input("{$tier}_price") : null,
            ])->all(),
        );
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasAnyPrice = collect(Sport::PRICE_TIERS)
                ->contains(fn ($tier) => $this->filled("{$tier}_price"));

            if (! $hasAnyPrice) {
                $validator->errors()->add('day_price', 'Select and price at least one tier (day, week, month, or year).');
            }
        });
    }
}
