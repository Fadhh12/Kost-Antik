<?php

namespace App\Http\Requests\Auth;

use App\Enums\Gender;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'lowercase', self::emailRule(), 'max:255', Rule::unique(User::class)->withoutTrashed()],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/', Rule::unique(User::class)],
            'gender' => ['required', Rule::enum(Gender::class)],
            'instance_id' => ['nullable', 'integer', 'exists:instances,id'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->email)),
            'phone' => preg_replace('/[\s-]/', '', (string) $this->phone),
        ]);
    }

    /**
     * email:rfc,dns sesuai SRS; cek DNS dimatikan saat test agar tidak butuh internet.
     */
    public static function emailRule(): string
    {
        return app()->environment('testing') ? 'email:rfc' : 'email:rfc,dns';
    }
}
