<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FR-PAY-01/02: upload bukti transfer. Nominal harus sama dengan tagihan.
 */
class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pay', $this->route('invoice'));
    }

    public function rules(): array
    {
        $invoice = $this->route('invoice');

        return [
            'invoice_id' => ['required', 'integer', Rule::in([$invoice->id])],
            'amount' => ['required', 'integer', Rule::in([$invoice->amount])],
            'method' => ['required', Rule::in(['transfer'])],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:3072'],
        ];
    }

    public function messages(): array
    {
        return [
            'paid_at.before_or_equal' => 'Tanggal transfer tidak boleh setelah hari ini.',
            'proof.mimes' => 'Bukti harus berupa gambar (JPG, PNG, WebP) atau PDF.',
        ];
    }
}
