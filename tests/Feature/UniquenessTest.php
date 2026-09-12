<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_email_must_be_unique(): void
    {
        Customer::factory()->create(['email' => 'unique.customer@example.com']);

        $this->expectException(QueryException::class);

        Customer::factory()->create(['email' => 'unique.customer@example.com']);
    }

    public function test_product_code_must_be_unique(): void
    {
        Product::factory()->create(['code' => 'SKU-UNIQUE']);

        $this->expectException(QueryException::class);

        Product::factory()->create(['code' => 'SKU-UNIQUE']);
    }
}
