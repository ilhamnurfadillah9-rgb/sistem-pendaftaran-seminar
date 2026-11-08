<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['bail', 'required', 'string', 'max:100'],
            'email' => ['bail', 'required', 'string', 'email', 'max:100'],
            'telepon' => ['bail', 'required', 'string', 'regex:/\A[0-9]{10,13}\z/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email tidak valid.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'telepon.required' => 'Nomor telepon wajib diisi.',
            'telepon.string' => 'Nomor telepon tidak valid.',
            'telepon.regex' => 'Nomor telepon harus terdiri dari 10 sampai 13 digit angka.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');

        if (is_string($email)) {
            $this->merge(['email' => Str::lower(trim($email))]);
        }
    }
}
