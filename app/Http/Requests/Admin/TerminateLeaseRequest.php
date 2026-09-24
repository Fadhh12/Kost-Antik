<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TerminateLeaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('terminate', $this->route('lease'));
    }

    public function rules(): array
    {
        $lease = $this->route('lease');

        return [
            'terminated_at' => [
                'required', 'date',
                'after_or_equal:'.$lease->start_date->toDateString(),
                'before_or_equal:'.$lease->end_date->toDateString(),
            ],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'terminated_at.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum kontrak dimulai.',
            'terminated_at.before_or_equal' => 'Tanggal berakhir tidak boleh setelah akhir kontrak.',
        ];
    }
}
