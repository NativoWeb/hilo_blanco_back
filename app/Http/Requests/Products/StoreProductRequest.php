<?php

namespace App\Http\Requests\Products;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:60', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'details' => ['nullable', 'array'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['boolean'],
            'is_customizable' => ['boolean'],
            'status' => ['integer', 'in:0,1'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Error de validación.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
