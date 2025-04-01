<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property int $discount_id
 */
class AppliedDiscount extends BaseModel
{
    public function cartItem(): CartItem
    {
        try {
            $data = DB::table('cart_items')
                ->where('id', $this->model_id)
                ->firstOrFail();

            return new CartItem((array) $data);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function discount(): Discount
    {
        try {
            $data = DB::table('discounts')
                ->where('id', $this->discount_id)
                ->firstOrFail();

            return new Discount((array) $data);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
