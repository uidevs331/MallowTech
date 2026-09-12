<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_products_below_the_configured_threshold(): void
    {
        config(['inventory.low_stock_threshold' => 5]);

        $low = Product::factory()->create(['code' => 'LOW-1', 'stock_on_hand' => 4]);
        $equal = Product::factory()->create(['code' => 'EQ-1', 'stock_on_hand' => 5]);
        $high = Product::factory()->create(['code' => 'HI-1', 'stock_on_hand' => 12]);

        $response = $this->getJson('/api/products/low-stock');

        $response->assertOk()
            ->assertJsonPath('meta.threshold', 5)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $low->id);

        $this->assertNotContains($equal->id, collect($response->json('data'))->pluck('id'));
        $this->assertNotContains($high->id, collect($response->json('data'))->pluck('id'));
    }

    public function test_the_threshold_query_parameter_overrides_configuration(): void
    {
        config(['inventory.low_stock_threshold' => 5]);

        Product::factory()->create(['stock_on_hand' => 2]);
        $mid = Product::factory()->create(['stock_on_hand' => 8]);
        Product::factory()->create(['stock_on_hand' => 20]);

        $response = $this->getJson('/api/products/low-stock?threshold=10');

        $response->assertOk()
            ->assertJsonPath('meta.threshold', 10)
            ->assertJsonCount(2, 'data');

        $this->assertContains($mid->id, collect($response->json('data'))->pluck('id'));
    }

    public function test_it_rejects_an_invalid_threshold(): void
    {
        $this->getJson('/api/products/low-stock?threshold=-1')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('threshold');
    }
}
