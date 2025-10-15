<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('sales.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'sale_type' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'receipt_note' => ['nullable', 'string'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'tax_total' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'change_due' => ['nullable', 'numeric', 'min:0'],
            'created_from' => ['nullable', 'string', 'max:50'],
            'device_id' => ['nullable', 'string', 'max:100'],
            'payload' => ['nullable', 'array'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.inventory_batch_id' => ['nullable', 'exists:inventory_batches,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.metadata' => ['nullable', 'array'],
            'payments' => ['nullable', 'array'],
            'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],
            'payments.*.method' => ['required_with:payments', 'string', 'max:50'],
            'payments.*.reference' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'discount_total' => $this->input('discount_total', 0),
            'tax_total' => $this->input('tax_total', 0),
            'change_due' => $this->input('change_due', max(0, ($this->input('amount_paid', 0) - $this->input('total_amount', 0)))),
        ]);
    }
}
