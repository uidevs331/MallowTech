<?php

namespace Tests\Feature;

use App\Jobs\SendOrderConfirmationJob;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SendOrderConfirmationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_job_writes_a_simulated_email_log_entry(): void
    {
        Event::fake([MessageLogged::class]);

        $customer = Customer::factory()->create([
            'email' => 'confirm@example.com',
        ]);

        $order = Order::query()->create([
            'customer_id' => $customer->id,
            'subtotal' => '100.00',
            'tax' => '18.00',
            'grand_total' => '118.00',
        ]);

        (new SendOrderConfirmationJob($order->id))->handle();

        Event::assertDispatched(MessageLogged::class, function (MessageLogged $event) use ($order) {
            return $event->level === 'info'
                && str_contains($event->message, 'Simulated order confirmation email')
                && ($event->context['order_id'] ?? null) === $order->id
                && ($event->context['customer_email'] ?? null) === 'confirm@example.com';
        });
    }
}
