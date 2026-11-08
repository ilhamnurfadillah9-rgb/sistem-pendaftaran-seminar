<?php

namespace App\Http\Requests;

use App\Models\Seminar;
use Illuminate\Validation\Validator;

class UpdateSeminarRequest extends SeminarRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            ...$this->commonRules(),
            'flyer' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('kuota')) {
                    return;
                }

                $seminar = $this->route('seminar');

                if (
                    $seminar instanceof Seminar
                    && (int) $this->input('kuota') < $seminar->registrations()->count()
                ) {
                    $validator->errors()->add(
                        'kuota',
                        'Kuota tidak boleh lebih kecil daripada jumlah pendaftar.',
                    );
                }
            },
        ];
    }
}
