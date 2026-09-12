<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LowStockProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'threshold' => ['sometimes', 'integer', 'min:0'],
        ]);

        $threshold = $validated['threshold'] ?? config('inventory.low_stock_threshold');

        $products = Product::query()
            ->where('stock_on_hand', '<', $threshold)
            ->orderBy('stock_on_hand')
            ->orderBy('id')
            ->get();

        return ProductResource::collection($products)
            ->additional([
                'meta' => [
                    'threshold' => (int) $threshold,
                ],
            ])
            ->response();
    }
}
