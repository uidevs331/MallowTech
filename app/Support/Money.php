<?php

namespace App\Support;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

final class Money
{
    public static function lineSubtotal(string $unitPrice, int $quantity): string
    {
        return BigDecimal::of($unitPrice)
            ->multipliedBy($quantity)
            ->toScale(2, RoundingMode::HalfUp)
            ->__toString();
    }

    public static function taxAmount(string $lineSubtotal, string $taxPercentage): string
    {
        return BigDecimal::of($lineSubtotal)
            ->multipliedBy($taxPercentage)
            ->dividedBy(100, 2, RoundingMode::HalfUp)
            ->__toString();
    }

    public static function add(string $left, string $right): string
    {
        return BigDecimal::of($left)
            ->plus($right)
            ->toScale(2, RoundingMode::HalfUp)
            ->__toString();
    }

    public static function unitPrice(string $price): string
    {
        return BigDecimal::of($price)
            ->toScale(2, RoundingMode::HalfUp)
            ->__toString();
    }

    public static function percentage(string $percentage): string
    {
        return BigDecimal::of($percentage)
            ->toScale(2, RoundingMode::HalfUp)
            ->__toString();
    }
}
