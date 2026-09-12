<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $customer = $this->input('customer', []);

        if (is_array($customer) && isset($customer['email']) && is_string($customer['email'])) {
            $customer['email'] = strtolower(trim($customer['email']));
            $this->merge(['customer' => $customer]);
        }
    }

    public function rules(): array
    {
        return [
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = $this->input('items');

            if (! is_array($items)) {
                return;
            }

            $productIds = [];

            foreach ($items as $index => $item) {
                if (! is_array($item) || ! array_key_exists('product_id', $item)) {
                    continue;
                }

                $productId = $item['product_id'];

                if (in_array($productId, $productIds, true)) {
                    $validator->errors()->add(
                        "items.{$index}.product_id",
                        'Duplicate product lines are not allowed. Send each product_id once.'
                    );
                }

                $productIds[] = $productId;
            }
        });
    }
}
