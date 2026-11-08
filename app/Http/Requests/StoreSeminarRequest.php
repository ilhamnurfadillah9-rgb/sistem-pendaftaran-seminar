<?php

namespace App\Http\Requests;

class StoreSeminarRequest extends SeminarRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $rules = $this->commonRules();
        $rules['flyer'] = ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'];
        $rules['tanggal'][] = 'after_or_equal:today';

        return $rules;
    }
}
