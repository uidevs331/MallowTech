<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerOrderController extends Controller
{
    public function index(string $email): JsonResponse
    {
        $customer = Customer::query()
            ->where('email', strtolower($email))
            ->firstOrFail();

        $orders = $customer->orders()
            ->with(['customer', 'items.product'])
            ->latest()
            ->get();

        return OrderResource::collection($orders)->response();
    }
}
