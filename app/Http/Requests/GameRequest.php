<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GameRequest extends FormRequest
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
            'title' => 'required|string|max:100',
            'genre' => 'required|string|max:50',
            'ps_type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'playstation_ids' => 'sometimes|array',
            'playstation_ids.*' => 'exists:playstations,id',
        ];

        // For image_url, different validation based on create/update
        if ($this->isMethod('post')) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
        } else {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg|max:2048';
        }

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
            'title.required' => 'Judul game harus diisi',
            'title.max' => 'Judul game maksimal 100 karakter',
            'genre.required' => 'Genre game harus diisi',
            'ps_type.required' => 'Tipe PlayStation harus diisi',
            'image.required' => 'Gambar game harus diunggah',
            'image.image' => 'File harus berupa gambar',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB',
            'playstation_ids.*.exists' => 'PlayStation yang dipilih tidak valid',
        ];
    }
}
