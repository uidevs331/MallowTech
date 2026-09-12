<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        $order = Order::query()->with('customer')->find($this->orderId);

        if ($order === null) {
            Log::warning('Order confirmation skipped because the order was not found.', [
                'order_id' => $this->orderId,
            ]);

            return;
        }

        Log::info('Simulated order confirmation email.', [
            'order_id' => $order->id,
            'customer_email' => $order->customer->email,
            'grand_total' => $order->grand_total,
        ]);
    }
}
