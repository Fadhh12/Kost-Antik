<?php

namespace App\Http\Requests\Admin;

use App\Enums\FacilityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isOwner();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('facilities', 'name')->ignore($this->route('facility'))],
            'icon' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/', function ($attr, $value, $fail) {
                if ($value && ! file_exists(base_path("vendor/mallardduck/blade-lucide-icons/resources/svg/icons/{$value}.svg"))) {
                    $fail('Ikon Lucide "'.$value.'" tidak ditemukan.');
                }
            }],
            'type' => ['required', Rule::enum(FacilityType::class)],
        ];
    }
}
