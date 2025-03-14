<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            'reservation_id' => 'required|exists:reservations,id',
            'amount' => 'required|numeric|min:0',
            'order_id' => 'required|string|max:100|unique:payments,order_id,' . ($this->payment->id ?? ''),
            'payment_status' => 'required|in:pending,paid,failed,refunded,expire',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'payment_data' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'reservation_id.required' => 'Reservation is required',
            'reservation_id.exists' => 'Selected reservation does not exist',
            'amount.required' => 'Payment amount is required',
            'amount.numeric' => 'Payment amount must be a number',
            'amount.min' => 'Payment amount must be at least 0',
            'order_id.required' => 'Order ID is required',
            'order_id.unique' => 'Order ID must be unique',
            'payment_status.required' => 'Payment status is required',
            'payment_status.in' => 'Invalid payment status selected',
        ];
    }
}
