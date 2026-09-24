<?php

namespace App\Http\Requests\Admin;

use App\Models\Lease;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Flow 3 / FR-LEASE-01: kontrak walk-in. Tanggal mulai boleh s.d. 30 hari ke belakang.
 */
class StoreLeaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Lease::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'start_date' => ['required', 'date', 'after_or_equal:'.today()->subDays(30)->toDateString(), 'before_or_equal:'.today()->addDays(60)->toDateString()],
            'duration_months' => ['required', 'integer', Rule::in(config('kost.durations'))],
            'pay_first_cash' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'Tanggal mulai paling jauh 30 hari ke belakang.',
            'start_date.before_or_equal' => 'Tanggal mulai paling lambat 60 hari dari hari ini.',
        ];
    }
}
