<?php

namespace App\Http\Requests\Admin;

use App\Models\Registration;
use App\Models\Seminar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Seminar $seminar */
        $seminar = $this->route('seminar');
        /** @var Registration $registration */
        $registration = $this->route('registration');

        return [
            'nama' => ['bail', 'required', 'string', 'max:100'],
            'email' => [
                'bail',
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('registrations', 'email')
                    ->where('seminar_id', $seminar->getKey())
                    ->ignore($registration->getKey()),
            ],
            'telepon' => [
                'bail',
                'required',
                'string',
                'regex:/\A[0-9]{10,13}\z/',
                Rule::unique('registrations', 'telepon')
                    ->where('seminar_id', $seminar->getKey())
                    ->ignore($registration->getKey()),
            ],
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
            'email.unique' => 'Email sudah terdaftar pada seminar ini.',
            'telepon.required' => 'Nomor telepon wajib diisi.',
            'telepon.string' => 'Nomor telepon tidak valid.',
            'telepon.regex' => 'Nomor telepon harus terdiri dari 10 sampai 13 digit angka.',
            'telepon.unique' => 'Nomor telepon sudah terdaftar pada seminar ini.',
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
