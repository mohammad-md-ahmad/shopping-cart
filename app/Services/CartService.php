<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CartServiceInterface;
use App\Factories\DiscountFactory;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\DeliveryFee;
use App\Models\Product;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartService implements CartServiceInterface
{
    public function get($cartId): array
    {
        try {
            $data = [];
            $cartData = (array) DB::table('carts')->find($cartId);
            $cart = new Cart($cartData);

            $cartItems = $cart->cartItems();

            foreach ($cartItems as $cartItem) {
                $item = $cartItem->toArray();
                $item['product'] = $cartItem->product()->toArray();
                $item['free_items'] = [];
                $item['discounts'] = [];
                $item['final_item_quantity'] = $item['quantity'];
                $item['final_item_price'] = $item['price_at_time'];

                $calculatedDiscounts = $this->calculateDiscounts($cartItem);

                foreach ($calculatedDiscounts as $calculatedDiscount) {

                    if (isset($calculatedDiscount['free_items'])) {
                        $item['free_items'][] = $calculatedDiscount['free_items'];
                    }

                    if (isset($calculatedDiscount['discount'])) {
                        $item['discounts'][] = $calculatedDiscount['discount'];
                    }
                }

                foreach ($item['free_items'] as $freeItem) {
                    $item['final_item_quantity'] += $freeItem;
                }

                foreach ($item['discounts'] as $discount) {
                    $item['final_item_price'] -= $discount;
                }

                $data['items'][] = $item;
            }

            $data['total_price'] = $this->calculateTotalCost($data['items']);
            $data['delivery_fees'] = $this->calculateDeliveryFees($data['total_price']);
            $data['total_cost'] = $data['total_price'] + $data['delivery_fees'];

            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function addItem(Request $request, ?string $cartId): array
    {
        try {
            /** @var Cart $cart */
            $cart = DB::table('carts')->find($cartId);

            // create a new cart
            if (! $cart) {
                $cart = $this->createCart();
            }

            $productCode = $request->input('productCode');
            $quantity = $request->input('quantity');

            // retrieve the related product by code
            $productData = (array) DB::table('products')->where('code', $productCode)->firstOrFail();
            $product = new Product($productData);

            // add the new cart item
            $cartItem = $this->createCartItem([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => (int) $quantity,
                'price_at_time' => $product->price,
            ]);

            $this->applyDiscounts($cartItem);

            return $this->get($cart->id);
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function applyDiscounts(CartItem $cartItem): void
    {
        $discounts = $cartItem->discounts();

        foreach ($discounts as $discount) {
            $discountService = DiscountFactory::create($cartItem, $discount);
            $discountService->applyDiscount();
        }
    }

    protected function calculateDiscounts(CartItem $cartItem): array
    {
        try {
            $data = [];
            $appliedDiscounts = $cartItem->appliedDiscounts();

            foreach ($appliedDiscounts as $appliedDiscount) {
                $discountService = DiscountFactory::create($appliedDiscount->cartItem(), $appliedDiscount->discount());
                $data[] = $discountService->calculateDiscount();
            }

            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function calculateTotalCost(array $cartItemsData): int
    {
        try {
            $total = 0;
            foreach ($cartItemsData as $cartItemData) {
                $total += $cartItemData['final_item_price'];
            }

            return $total;
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function calculateDeliveryFees(int $totalCost): int
    {
        try {
            $deliveryFeeData = (array) DB::table('delivery_fees')
                ->where('min_amount', '<=', $totalCost)
                ->where(function (Builder $query) use ($totalCost) {
                    $query->where('max_amount', '>=', $totalCost)
                        ->orWhereNull('max_amount');
                })
                ->firstOrFail();

            $deliveryFee = new DeliveryFee($deliveryFeeData);

            return $deliveryFee->fee;
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function createCart(array $data = []): Cart
    {
        try {
            return DB::transaction(function () use ($data) {
                return Cart::create($data);
            });
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function createCartItem(array $data): CartItem
    {
        try {
            return DB::transaction(function () use ($data) {
                return CartItem::create($data);
            });
        } catch (Exception $e) {
            throw $e;
        }
    }
}
