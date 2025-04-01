<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 */
class Cart extends BaseModel
{
    public function cartItems()
    {
        return DB::table('cart_items')
            ->where('cart_id', $this->id)
            ->get()
            ->map(function ($cartItem) {
                return new CartItem((array) $cartItem);
            });
    }

    public function appliedDiscounts()
    {
        try {
            return DB::table('applied_discounts')
                ->where('model_type', $this::class)
                ->where(function (Builder $query) {
                    $query->where('model_id', '=', $this->id)
                        ->orWhereNull('model_id');
                })
                ->get()
                ->map(function ($data) {
                    return new AppliedDiscount((array) $data);
                });
        } catch (Exception $e) {
            throw $e;
        }
    }
}
