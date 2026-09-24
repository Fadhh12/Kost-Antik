<?php

namespace App\Http\Requests\Admin;

use App\Enums\Gender;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'lowercase', RegisterRequest::emailRule(), 'max:255', Rule::unique(User::class)],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/', Rule::unique(User::class)],
            'gender' => ['required', Rule::enum(Gender::class)],
            'password' => ['required', Password::defaults()],
            'properties' => ['nullable', 'array'],
            'properties.*' => ['integer', 'exists:properties,id'],
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
