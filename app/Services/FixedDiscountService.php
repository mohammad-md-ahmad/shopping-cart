<?php

namespace App\Services;

use App\Contracts\DiscountStrategyInterface;
use App\Models\AppliedDiscount;
use App\Models\CartItem;
use App\Models\Discount;
use Exception;

class FixedDiscountService implements DiscountStrategyInterface
{
    public function __construct(
        protected CartItem $cartItem,
        protected Discount $discount,
    ) {}

    public function applyDiscount(): void
    {
        try {
            $appliedDiscountData = [
                'model_type' => $this->cartItem::class,
                'model_id' => $this->cartItem->id,
                'discount_id' => $this->discount->id,
            ];

            AppliedDiscount::create($appliedDiscountData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function calculateDiscount(): array
    {
        try {
            $discount = (int) $this->discount->value;

            return [
                'discount' => $discount,
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
