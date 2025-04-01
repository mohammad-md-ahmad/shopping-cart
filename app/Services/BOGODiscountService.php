<?php

namespace App\Services;

use App\Contracts\DiscountStrategyInterface;
use App\Models\AppliedDiscount;
use App\Models\CartItem;
use App\Models\Discount;
use Exception;

class BOGODiscountService implements DiscountStrategyInterface
{
    public function __construct(
        protected CartItem $cartItem,
        protected Discount $discount,
    ) {}

    public function applyDiscount()
    {
        try {
            if ($this->cartItem->quantity >= $this->discount->min_quantity) {
                $appliedDiscountData = [
                    'model_type' => $this->cartItem::class,
                    'model_id' => $this->cartItem->id,
                    'discount_id' => $this->discount->id,
                ];

                AppliedDiscount::create($appliedDiscountData);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function calculateDiscount()
    {
        try {
            $freeItems = (int) floor($this->cartItem->quantity / $this->discount->min_quantity);

            return [
                'free_items' => $freeItems,
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
