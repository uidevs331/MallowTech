<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $productId,
        public readonly int $requestedQuantity,
        public readonly int $availableStock,
    ) {
        parent::__construct(
            "Insufficient stock for product {$productId}: requested {$requestedQuantity}, available {$availableStock}."
        );
    }

    public static function forProduct(Product $product, int $requestedQuantity): self
    {
        return new self($product->id, $requestedQuantity, (int) $product->stock_on_hand);
    }
}
