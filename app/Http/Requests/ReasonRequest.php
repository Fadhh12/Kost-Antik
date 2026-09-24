<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * SRS 2.2: tolak booking/akun/pembayaran wajib alasan 10-500 karakter.
 * Otorisasi dicek di controller lewat Policy.
 */
class ReasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }
}
