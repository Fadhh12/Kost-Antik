<?php

namespace App\Http\Requests\Admin;

use App\Enums\FacilityType;
use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $room = $this->route('room');

        return $room instanceof Room
            ? $this->user()->can('update', $room)
            : $this->user()->can('create', [Room::class, $this->route('property')]);
    }

    public function rules(): array
    {
        $property = $this->route('property');
        $room = $this->route('room');

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('rooms', 'code')->where('property_id', $property->id)->ignore($room)],
            'floor' => ['nullable', 'integer', 'between:0,50'],
            'size_m2' => ['nullable', 'numeric', 'between:2,100'],
            'monthly_price' => ['required', 'integer', 'min:100000', 'max:50000000'],
            'capacity' => ['required', 'integer', 'between:1,4'],
            // FR-PROP-03: occupied hanya oleh sistem.
            'status' => ['required', Rule::in([RoomStatus::Available->value, RoomStatus::Maintenance->value])],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer', Rule::exists('facilities', 'id')->where('type', FacilityType::Room->value)],
        ];
    }

    protected function prepareForValidation(): void
    {
        // "1.250.000" -> 1250000
        if (is_string($this->monthly_price)) {
            $this->merge(['monthly_price' => preg_replace('/\D/', '', $this->monthly_price)]);
        }
    }
}
