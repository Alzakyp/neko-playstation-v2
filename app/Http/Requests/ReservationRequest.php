<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ReservationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_walkin' => 'sometimes|boolean',
            'user_id' => 'required_if:is_walkin,0,|nullable|exists:users,id',
            'walkin_name' => 'required_if:is_walkin,1|nullable|string|max:255',
            'walkin_phone' => 'required_if:is_walkin,1|nullable|string|max:20',
            'playstation_id' => 'required|exists:playstations,id',
            'start_time' => 'required|date',
            'end_time' => [
                'required',
                'date',
                'after:start_time',
            ],
            'status' => 'nullable|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check that end time is at least 1 hour after start time
            $startTime = Carbon::parse($this->start_time);
            $endTime = Carbon::parse($this->end_time);

            if ($endTime->diffInMinutes($startTime) < 60) {
                $validator->errors()->add('end_time', 'The end time must be at least 1 hour after the start time.');
            }

            // Validate that either user_id is present or walk-in info is provided
            if (!$this->is_walkin && empty($this->user_id)) {
                $validator->errors()->add('user_id', 'Please select a customer or mark as walk-in.');
            }

            if ($this->is_walkin && (empty($this->walkin_name) || empty($this->walkin_phone))) {
                if (empty($this->walkin_name)) {
                    $validator->errors()->add('walkin_name', 'Walk-in customer name is required.');
                }
                if (empty($this->walkin_phone)) {
                    $validator->errors()->add('walkin_phone', 'Walk-in customer phone number is required.');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'user_id.required_if' => 'Please select a customer when not using walk-in option',
            'user_id.exists' => 'Selected customer not found',
            'walkin_name.required_if' => 'Walk-in customer name is required',
            'walkin_phone.required_if' => 'Walk-in customer phone number is required',
            'playstation_id.required' => 'Please select a PlayStation unit',
            'playstation_id.exists' => 'PlayStation unit not found',
            'start_time.required' => 'Start time is required',
            'start_time.date' => 'Invalid start time format',
            'end_time.required' => 'End time is required',
            'end_time.date' => 'Invalid end time format',
            'end_time.after' => 'End time must be after start time',
            'status.in' => 'Invalid reservation status',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Convert checkbox value to boolean
        if ($this->has('is_walkin')) {
            $this->merge([
                'is_walkin' => filter_var($this->is_walkin, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? true : false,
            ]);
        }
    }
}
