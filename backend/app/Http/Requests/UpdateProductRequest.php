<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('products.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product');

        if ($productId instanceof Product) {
            $productId = $productId->getKey();
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.($productId ?? 'NULL').',id'],
            'barcode' => ['nullable', 'string', 'max:150', 'unique:products,barcode,'.($productId ?? 'NULL').',id'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'unit_of_measure' => ['required', 'string', 'max:50'],
            'pack_size' => ['nullable', 'integer', 'min:1'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'reorder_quantity' => ['nullable', 'integer', 'min:0'],
            'track_batches' => ['boolean'],
            'track_serial_numbers' => ['boolean'],
            'expiry_required' => ['boolean'],
            'is_prescription_only' => ['boolean'],
            'is_controlled_substance' => ['boolean'],
            'storage_instructions' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'track_batches' => $this->boolean('track_batches', true),
            'track_serial_numbers' => $this->boolean('track_serial_numbers'),
            'expiry_required' => $this->boolean('expiry_required', true),
            'is_prescription_only' => $this->boolean('is_prescription_only'),
            'is_controlled_substance' => $this->boolean('is_controlled_substance'),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
