<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounterPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_counter_page_renders(): void
    {
        Product::factory()->create(['name' => 'Wireless Mouse']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Retail Counter')
            ->assertSee('Wireless Mouse');
    }
}
