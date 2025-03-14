<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaystationRequest extends FormRequest
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
        $rules = [
            'ps_number' => 'required|string|max:20',
            'ps_type' => 'required|string|max:50',
            'status' => 'required|in:available,in_use,maintenance',
            'hourly_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'game_ids' => 'sometimes|array',
            'game_ids.*' => 'exists:games,id',
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'ps_number.required' => 'Nomor PlayStation harus diisi',
            'ps_number.max' => 'Nomor PlayStation maksimal 20 karakter',
            'ps_type.required' => 'Tipe PlayStation harus diisi',
            'status.required' => 'Status PlayStation harus diisi',
            'status.in' => 'Status PlayStation tidak valid',
            'hourly_rate.required' => 'Tarif per jam harus diisi',
            'hourly_rate.numeric' => 'Tarif per jam harus berupa angka',
            'hourly_rate.min' => 'Tarif per jam tidak boleh negatif',
            'game_ids.*.exists' => 'Game yang dipilih tidak valid',
        ];
    }
}
