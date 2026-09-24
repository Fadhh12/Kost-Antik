<?php

namespace App\Http\Requests\Tenant;

use App\Models\BookingRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', BookingRequest::class);
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.today()->addDays(60)->toDateString()],
            'duration_months' => ['required', 'integer', Rule::in(config('kost.durations'))],
            'note' => ['nullable', 'string', 'max:500'],
            'agree_rules' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.before_or_equal' => 'Tanggal mulai paling lambat 60 hari dari hari ini.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'agree_rules.accepted' => 'Kamu perlu menyetujui aturan kost.',
        ];
    }
}
