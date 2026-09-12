<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmationJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * @param  array{customer: array{name: string, email: string}, items: list<array{product_id: int, quantity: int}>}  $payload
     */
    public function create(array $payload): Order
    {
        $order = DB::transaction(function () use ($payload) {
            $customer = $this->resolveCustomer($payload['customer']);
            $items = $payload['items'];

            // Lock products in ascending ID order so concurrent orders cannot deadlock.
            $productIds = collect($items)->pluck('product_id')->sort()->values()->all();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lineAttributes = [];
            $subtotal = '0.00';
            $tax = '0.00';

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];

                if ($product === null) {
                    abort(422, 'One or more products could not be found.');
                }

                if ($product->stock_on_hand < $quantity) {
                    throw InsufficientStockException::forProduct($product, $quantity);
                }

                $unitPrice = Money::unitPrice((string) $product->price);
                $taxPercentage = Money::percentage((string) $product->tax_percentage);
                $lineSubtotal = Money::lineSubtotal($unitPrice, $quantity);
                $taxAmount = Money::taxAmount($lineSubtotal, $taxPercentage);
                $lineTotal = Money::add($lineSubtotal, $taxAmount);

                $lineAttributes[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $taxAmount,
                    'line_subtotal' => $lineSubtotal,
                    'line_total' => $lineTotal,
                ];

                $subtotal = Money::add($subtotal, $lineSubtotal);
                $tax = Money::add($tax, $taxAmount);
            }

            $order = $customer->orders()->create([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'grand_total' => Money::add($subtotal, $tax),
            ]);

            $order->items()->createMany($lineAttributes);

            foreach ($items as $item) {
                $quantity = (int) $item['quantity'];

                // Atomic guard in addition to lockForUpdate so stock cannot go negative.
                $affected = Product::query()
                    ->whereKey($item['product_id'])
                    ->where('stock_on_hand', '>=', $quantity)
                    ->decrement('stock_on_hand', $quantity);

                if ($affected === 0) {
                    $product = $products->get($item['product_id']);
                    throw InsufficientStockException::forProduct($product, $quantity);
                }
            }

            return $order;
        });

        SendOrderConfirmationJob::dispatch($order->id);

        return $order->load(['customer', 'items.product']);
    }

    /**
     * Email is the customer identity. An existing customer is reused;
     * the stored name is not overwritten by later orders.
     *
     * @param  array{name: string, email: string}  $customerData
     */
    private function resolveCustomer(array $customerData): Customer
    {
        try {
            return Customer::query()->firstOrCreate(
                ['email' => $customerData['email']],
                ['name' => $customerData['name']],
            );
        } catch (UniqueConstraintViolationException) {
            return Customer::query()
                ->where('email', $customerData['email'])
                ->firstOrFail();
        }
    }
}
