<?php

namespace App\Http\Requests;

use App\Enums\GenderTarget;
use App\Services\CatalogService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::enum(GenderTarget::class)],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'available' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(array_keys(CatalogService::SORTS))],
        ];
    }

    /**
     * Filter tidak valid diabaikan (bukan error) supaya URL yang dibagikan tetap terbuka.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $validator = validator($this->query(), $this->rules());

        return collect($this->query())
            ->only(array_keys($this->rules()))
            ->reject(fn ($value, $key) => $validator->errors()->has($key) || $value === '' || $value === null)
            ->all();
    }

    /**
     * Validasi dilakukan lunak di filters().
     */
    protected function prepareForValidation(): void {}

    public function validateResolved(): void {}
}
