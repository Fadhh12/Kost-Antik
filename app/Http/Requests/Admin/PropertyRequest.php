<?php

namespace App\Http\Requests\Admin;

use App\Enums\FacilityType;
use App\Enums\GenderTarget;
use App\Enums\PropertyStatus;
use App\Enums\Role;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $property = $this->route('property');

        return $property instanceof Property
            ? $this->user()->can('update', $property)
            : $this->user()->can('create', Property::class);
    }

    public function rules(): array
    {
        $property = $this->route('property');

        return [
            'name' => ['required', 'string', 'min:3', 'max:100', Rule::unique('properties', 'name')->ignore($property)],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'gender_target' => ['required', Rule::enum(GenderTarget::class)],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'description' => ['nullable', 'string', 'max:2000'],
            'rules' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(PropertyStatus::class)],
            'manager_id' => ['nullable', Rule::exists('users', 'id')->whereNull('deleted_at'), function ($attr, $value, $fail) {
                if ($value && ! User::find($value)?->hasRole(Role::Manager->value)) {
                    $fail('Pengelola yang dipilih bukan akun pengelola.');
                }
            }],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer', Rule::exists('facilities', 'id')->where('type', FacilityType::Shared->value)],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];
    }

    /**
     * Pengelola hanya boleh mengubah info dasar; field lain diambil dari data lama
     * agar validasi tetap lolos tanpa membuka akses.
     */
    protected function prepareForValidation(): void
    {
        $property = $this->route('property');

        if ($property instanceof Property && ! $this->user()->can('manage', $property)) {
            $this->merge([
                'name' => $property->name,
                'gender_target' => $property->gender_target->value,
                'status' => $property->status->value,
                'manager_id' => $property->manager_id,
                'facilities' => null,
                'images' => null,
            ]);
        }
    }
}
