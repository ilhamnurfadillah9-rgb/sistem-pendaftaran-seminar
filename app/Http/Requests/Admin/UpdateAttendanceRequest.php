<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAttendanceRequest extends FormRequest
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
        return [
            'status' => ['bail', 'required', 'string', Rule::in(['terdaftar', 'hadir', 'tidak_hadir'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status kehadiran tidak valid.',
            'status.string' => 'Status kehadiran tidak valid.',
            'status.in' => 'Status kehadiran tidak valid.',
        ];
    }

    /**
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('status') === 'terdaftar') {
                    $validator->errors()->add(
                        'status',
                        'Status tidak dapat dikembalikan menjadi terdaftar.',
                    );
                }
            },
        ];
    }
}
