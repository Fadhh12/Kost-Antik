<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'lowercase', RegisterRequest::emailRule(), 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/', Rule::unique(User::class)->ignore($user->id)],
            // Jenis kelamin menentukan kelayakan sewa (BR-02); dikunci setelah akun diverifikasi.
            'gender' => [Rule::requiredIf($user->isPending()), Rule::prohibitedIf(! $user->isPending()), Rule::enum(Gender::class)],
            'instance_id' => ['nullable', 'integer', 'exists:instances,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->email)),
            'phone' => preg_replace('/[\s-]/', '', (string) $this->phone),
        ]);
    }
}
