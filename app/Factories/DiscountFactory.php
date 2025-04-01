<?php

namespace App\Factories;

use App\Contracts\DiscountStrategyInterface;
use App\Enums\DiscountType;
use App\Models\CartItem;
use App\Models\Discount;
use App\Services\BOGODiscountService;
use App\Services\FixedDiscountService;
use App\Services\PercentageDiscountService;
use Exception;

class DiscountFactory
{
    public static function create(CartItem $cartItem, Discount $discount): DiscountStrategyInterface
    {
        return match ($discount->type) {
            DiscountType::BOGO->name => new BOGODiscountService($cartItem, $discount),
            DiscountType::PERCENTAGE->name => new PercentageDiscountService($cartItem, $discount),
            DiscountType::FIXED->name => new FixedDiscountService($cartItem, $discount),
            default => throw new Exception('Invalid Discount Type')
        };
    }
}
