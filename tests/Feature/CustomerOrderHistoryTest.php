<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CustomerOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_customers_orders_and_line_items(): void
    {
        Bus::fake();

        $customer = Customer::factory()->create([
            'email' => 'history.customer@example.com',
        ]);

        $other = Customer::factory()->create();

        $product = Product::factory()->create([
            'name' => 'Stapler',
            'code' => 'STN-STP-003',
            'price' => '185.00',
            'tax_percentage' => '18.00',
            'stock_on_hand' => 10,
        ]);

        $this->postJson('/api/orders', [
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated();

        $this->postJson('/api/orders', [
            'customer' => [
                'name' => $other->name,
                'email' => $other->email,
            ],
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated();

        $response = $this->getJson('/api/customers/'.rawurlencode($customer->email).'/orders');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.customer.email', $customer->email)
            ->assertJsonPath('data.0.items.0.product_code', 'STN-STP-003')
            ->assertJsonPath('data.0.items.0.quantity', 1)
            ->assertJsonPath('data.0.grand_total', '218.30');
    }

    public function test_unknown_customer_email_returns_not_found(): void
    {
        $this->getJson('/api/customers/'.rawurlencode('missing@example.com').'/orders')
            ->assertNotFound();
    }
}
