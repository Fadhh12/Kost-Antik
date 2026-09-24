<?php

namespace App\Http\Requests\Admin;

use App\Enums\InstanceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isOwner();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('instances', 'name')->ignore($this->route('instance'))],
            'type' => ['required', Rule::enum(InstanceType::class)],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
