<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @property string $id
 * @property string $cart_id
 * @property string $product_id
 * @property int $quantity
 * @property int $price_at_time
 */
class CartItem extends BaseModel
{
    public function product()
    {
        $productData = (array) DB::table('products')
            ->where('id', $this->product_id)
            ->first();

        return new Product($productData);
    }

    public function discounts()
    {
        try {
            $currentTimestamp = date('Y-m-d H:i:s');

            return DB::table('discounts')
                ->where('applicable_model_type', Product::class)
                ->where('applicable_model_id', $this->product_id)
                ->whereDate('valid_from', '<=', $currentTimestamp)
                ->where(function (Builder $query) use ($currentTimestamp) {
                    $query->whereDate('valid_until', '>=', $currentTimestamp)
                        ->orWhereNull('valid_until');
                })
                ->get()
                ->map(function ($data) {
                    return new Discount((array) $data);
                });
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function appliedDiscounts()
    {
        try {
            return DB::table('applied_discounts')
                ->where('model_type', $this::class)
                ->where('model_id', $this->id)
                ->get()
                ->map(function ($data) {
                    return new AppliedDiscount((array) $data);
                });
        } catch (Exception $e) {
            throw $e;
        }
    }
}
