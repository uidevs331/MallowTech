<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class CounterController extends Controller
{
    public function __invoke(): View
    {
        return view('counter', [
            'products' => Product::query()->orderBy('name')->get(),
            'lowStockThreshold' => (int) config('inventory.low_stock_threshold'),
        ]);
    }
}
