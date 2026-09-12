<?php

namespace Tests\Feature;

use App\Jobs\SendOrderConfirmationJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_order_calculates_totals_and_deducts_stock(): void
    {
        Bus::fake();

        $customer = Customer::factory()->create([
            'name' => 'Anita Sharma',
            'email' => 'anita.sharma@example.com',
        ]);

        $paper = Product::factory()->create([
            'name' => 'A4 Copy Paper Ream',
            'code' => 'STN-A4-001',
            'price' => '249.00',
            'tax_percentage' => '18.00',
            'stock_on_hand' => 10,
        ]);

        $pen = Product::factory()->create([
            'name' => 'Blue Ballpoint Pen',
            'code' => 'STN-PEN-002',
            'price' => '12.50',
            'tax_percentage' => '18.00',
            'stock_on_hand' => 20,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'items' => [
                ['product_id' => $paper->id, 'quantity' => 2],
                ['product_id' => $pen->id, 'quantity' => 4],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.subtotal', '548.00')
            ->assertJsonPath('data.tax', '98.64')
            ->assertJsonPath('data.grand_total', '646.64')
            ->assertJsonPath('data.customer.email', 'anita.sharma@example.com')
            ->assertJsonCount(2, 'data.items');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 2);
        $this->assertSame(8, $paper->fresh()->stock_on_hand);
        $this->assertSame(16, $pen->fresh()->stock_on_hand);

        $order = Order::query()->first();
        $this->assertSame('548.00', $order->subtotal);
        $this->assertSame('98.64', $order->tax);
        $this->assertSame('646.64', $order->grand_total);
    }

    public function test_it_creates_a_customer_when_the_email_is_new(): void
    {
        Bus::fake();

        $product = Product::factory()->create([
            'price' => '100.00',
            'tax_percentage' => '10.00',
            'stock_on_hand' => 5,
        ]);

        $this->postJson('/api/orders', [
            'customer' => [
                'name' => 'New Buyer',
                'email' => 'new.buyer@example.com',
            ],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertCreated();

        $this->assertDatabaseHas('customers', [
            'email' => 'new.buyer@example.com',
            'name' => 'New Buyer',
        ]);
    }

    public function test_it_reuses_an_existing_customer_without_changing_the_stored_name(): void
    {
        Bus::fake();

        $customer = Customer::factory()->create([
            'name' => 'Original Name',
            'email' => 'same.person@example.com',
        ]);

        $product = Product::factory()->create([
            'price' => '10.00',
            'tax_percentage' => '0.00',
            'stock_on_hand' => 5,
        ]);

        $this->postJson('/api/orders', [
            'customer' => [
                'name' => 'Different Name',
                'email' => 'Same.Person@example.com',
            ],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertCreated();

        $this->assertDatabaseCount('customers', 1);
        $this->assertSame('Original Name', $customer->fresh()->name);
        $this->assertSame($customer->id, Order::query()->first()->customer_id);
    }

    public function test_insufficient_stock_does_not_create_an_order_or_change_stock(): void
    {
        Bus::fake();

        $product = Product::factory()->create([
            'price' => '50.00',
            'tax_percentage' => '18.00',
            'stock_on_hand' => 1,
        ]);

        $this->postJson('/api/orders', [
            'customer' => [
                'name' => 'Rahul Menon',
                'email' => 'rahul.menon@example.com',
            ],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertUnprocessable()
            ->assertJsonPath('error', 'insufficient_stock')
            ->assertJsonPath('product_id', $product->id)
            ->assertJsonPath('requested_quantity', 2)
            ->assertJsonPath('available_stock', 1);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(1, $product->fresh()->stock_on_hand);
        Bus::assertNotDispatched(SendOrderConfirmationJob::class);
    }

    public function test_a_later_insufficient_line_rolls_back_the_whole_order(): void
    {
        Bus::fake();

        $available = Product::factory()->create([
            'price' => '10.00',
            'tax_percentage' => '0.00',
            'stock_on_hand' => 5,
        ]);

        $unavailable = Product::factory()->create([
            'price' => '20.00',
            'tax_percentage' => '0.00',
            'stock_on_hand' => 1,
        ]);

        $this->postJson('/api/orders', [
            'customer' => [
                'name' => 'Priya Nair',
                'email' => 'priya.nair@example.com',
            ],
            'items' => [
                ['product_id' => $available->id, 'quantity' => 2],
                ['product_id' => $unavailable->id, 'quantity' => 3],
            ],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(5, $available->fresh()->stock_on_hand);
        $this->assertSame(1, $unavailable->fresh()->stock_on_hand);
    }

    public function test_it_rejects_zero_and_negative_quantities(): void
    {
        $product = Product::factory()->create(['stock_on_hand' => 5]);

        $this->postJson('/api/orders', [
            'customer' => ['name' => 'Buyer', 'email' => 'buyer@example.com'],
            'items' => [['product_id' => $product->id, 'quantity' => 0]],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('items.0.quantity');

        $this->postJson('/api/orders', [
            'customer' => ['name' => 'Buyer', 'email' => 'buyer@example.com'],
            'items' => [['product_id' => $product->id, 'quantity' => -2]],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('items.0.quantity');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock_on_hand);
    }

    public function test_it_rejects_unknown_products_and_invalid_customer_input(): void
    {
        $this->postJson('/api/orders', [
            'customer' => ['name' => '', 'email' => 'not-an-email'],
            'items' => [['product_id' => 999, 'quantity' => 1]],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'customer.name',
                'customer.email',
                'items.0.product_id',
            ]);
    }

    public function test_it_rejects_duplicate_product_lines(): void
    {
        $product = Product::factory()->create(['stock_on_hand' => 10]);

        $this->postJson('/api/orders', [
            'customer' => ['name' => 'Buyer', 'email' => 'buyer@example.com'],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('items.1.product_id');
    }

    public function test_order_lines_keep_historical_price_and_tax_after_catalog_changes(): void
    {
        Bus::fake();

        $product = Product::factory()->create([
            'price' => '100.00',
            'tax_percentage' => '10.00',
            'stock_on_hand' => 5,
        ]);

        $this->postJson('/api/orders', [
            'customer' => ['name' => 'Buyer', 'email' => 'buyer@example.com'],
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated();

        $product->update([
            'price' => '500.00',
            'tax_percentage' => '18.00',
        ]);

        $item = OrderItem::query()->first();

        $this->assertSame('100.00', $item->unit_price);
        $this->assertSame('10.00', $item->tax_percentage);
        $this->assertSame('10.00', $item->tax_amount);
        $this->assertSame('100.00', $item->line_subtotal);
        $this->assertSame('110.00', $item->line_total);
        $this->assertSame('100.00', Order::query()->first()->subtotal);
    }

    public function test_successful_order_creation_dispatches_the_confirmation_job(): void
    {
        Bus::fake();

        $product = Product::factory()->create([
            'price' => '25.00',
            'tax_percentage' => '0.00',
            'stock_on_hand' => 3,
        ]);

        $this->postJson('/api/orders', [
            'customer' => ['name' => 'Buyer', 'email' => 'buyer@example.com'],
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated();

        Bus::assertDispatched(SendOrderConfirmationJob::class, function (SendOrderConfirmationJob $job) {
            return $job->orderId === Order::query()->value('id');
        });
    }
}
