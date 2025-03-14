<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
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
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string',
            'refund_percentage' => 'required|in:0,25,50,75,100',
            'request_date' => 'nullable|date',
            'status' => 'required|in:pending,approved,rejected,processed',
            'admin_id' => 'nullable|exists:users,id',
            'processed_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'refund_id' => 'nullable|string|max:100',
            'refund_response' => 'nullable|array',
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
            'payment_id.required' => 'Referensi pembayaran diperlukan',
            'payment_id.exists' => 'Pembayaran yang dipilih tidak valid',
            'reservation_id.required' => 'Referensi reservasi diperlukan',
            'amount.required' => 'Jumlah pengembalian dana diperlukan',
            'amount.numeric' => 'Jumlah pengembalian dana harus berupa angka',
            'refund_percentage.required' => 'Persentase pengembalian dana diperlukan',
            'refund_percentage.in' => 'Persentase pengembalian dana harus 0, 25, 50, 75 atau 100',
            'status.required' => 'Status refund diperlukan',
            'status.in' => 'Status refund tidak valid',
        ];
    }
}
