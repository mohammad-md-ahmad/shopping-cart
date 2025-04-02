<?php

namespace Tests\Unit;

use App\Enums\DiscountType;
use App\Models\Cart;
use App\Models\DeliveryFee;
use App\Models\Discount;
use App\Models\Product;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    public function test_add_item_success(): void
    {
        // Set up real data for the test
        $price = 1000;
        $quantity = 2;
        $cart = Cart::create();
        $product = Product::create([
            'name' => 'Red Widget',
            'code' => 'R01',
            'price' => $price,
        ]);

        $request = Request::create('/api/carts', 'POST', [
            'productCode' => $product->code,
            'quantity' => $quantity,
        ]);

        $cartService = app(CartService::class);
        $result = $cartService->addItem($request, $cart->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertEquals($price, $result['total_cost']);
        $this->assertEquals($result['total_price'], $result['total_cost']);
    }

    public function test_add_item_with_no_cart_initialized(): void
    {
        // Set up real data for the test
        $price = 1000;
        $quantity = 2;
        $product = Product::create([
            'name' => 'Red Widget',
            'code' => 'R01',
            'price' => $price,
        ]);

        $request = Request::create('/api/carts', 'POST', [
            'productCode' => $product->code,
            'quantity' => $quantity,
        ]);

        $cartService = app(CartService::class);
        $result = $cartService->addItem($request, null);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertEquals($price, $result['total_cost']);
        $this->assertEquals($result['total_price'], $result['total_cost']);
    }

    public function test_add_item_with_delivery_fees(): void
    {
        // Set up real data for the test
        $p1Price = 5000;
        $p2Price = 10000;
        $p3Price = 15000;
        $totalPrice = $p1Price + $p2Price + $p3Price;
        $quantity = 1;
        $cart = Cart::create();
        $product1 = Product::create([
            'name' => 'Red Widget',
            'code' => 'R01',
            'price' => $p1Price,
        ]);
        $product2 = Product::create([
            'name' => 'Blue Widget',
            'code' => 'B01',
            'price' => $p2Price,
        ]);
        $product3 = Product::create([
            'name' => 'Green Widget',
            'code' => 'G01',
            'price' => $p3Price,
        ]);
        $delivery1Fee = 4950;
        $delivery2Fee = 2950;
        DeliveryFee::create([
            'fee' => $delivery1Fee,
            'min_amount' => 0,
            'max_amount' => 49999,
        ]);
        DeliveryFee::create([
            'fee' => $delivery2Fee,
            'min_amount' => 50000,
            'max_amount' => 89999,
        ]);

        $request1 = Request::create('/api/carts', 'POST', [
            'productCode' => $product1->code,
            'quantity' => $quantity,
        ]);
        $request2 = Request::create('/api/carts', 'POST', [
            'productCode' => $product2->code,
            'quantity' => $quantity,
        ]);
        $request3 = Request::create('/api/carts', 'POST', [
            'productCode' => $product3->code,
            'quantity' => $quantity,
        ]);

        $cartService = app(CartService::class);
        $result = $cartService->addItem($request1, $cart->id);
        $result = $cartService->addItem($request2, $cart->id);
        $result = $cartService->addItem($request3, $cart->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertEquals($totalPrice, $result['total_price']);
        $this->assertEquals($delivery1Fee, $result['delivery_fees']);
        $this->assertEquals($totalPrice + $delivery1Fee, $result['total_cost']);
    }

    public function test_add_item_with_bogo_discount(): void
    {
        // Set up real data for the test
        $p1Price = 5000;
        $p2Price = 10000;
        $totalPrice = $p1Price + $p2Price;
        $quantity = 1;
        $cart = Cart::create();
        $product1 = Product::create([
            'name' => 'Red Widget',
            'code' => 'R01',
            'price' => $p1Price,
        ]);
        $product2 = Product::create([
            'name' => 'Blue Widget',
            'code' => 'B01',
            'price' => $p2Price,
        ]);
        $freeItems = 1;
        $discount = Discount::create([
            'type' => DiscountType::BOGO->name,
            'value' => $freeItems,
            'min_quantity' => 1,
            'applicable_model_type' => Product::class,
            'applicable_model_id' => $product1->id,
            'valid_from' => Carbon::now()->subDay()->toDateString(),
            'valid_until' => null,
        ]);

        $request1 = Request::create('/api/carts', 'POST', [
            'productCode' => $product1->code,
            'quantity' => $quantity,
        ]);
        $request2 = Request::create('/api/carts', 'POST', [
            'productCode' => $product2->code,
            'quantity' => $quantity,
        ]);

        $cartService = app(CartService::class);
        $cartService->addItem($request1, $cart->id);
        $result = $cartService->addItem($request2, $cart->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertEquals($totalPrice, $result['total_price']);

        // Assert that the first product (product1) has 1 free item (BOGO)
        $this->assertCount(1, $result['items'][0]['free_items']);
        $this->assertEquals($freeItems, $result['items'][0]['free_items'][0]);

        // Assert the final item price
        $this->assertEquals($p1Price, $result['items'][0]['final_item_price']);
    }

    public function test_add_item_with_bogo_discount_not_applied(): void
    {
        // Set up real data for the test
        $p1Price = 5000;
        $p2Price = 10000;
        $totalPrice = $p1Price + $p2Price;
        $quantity = 1;
        $cart = Cart::create();
        $product1 = Product::create([
            'name' => 'Red Widget',
            'code' => 'R01',
            'price' => $p1Price,
        ]);
        $product2 = Product::create([
            'name' => 'Blue Widget',
            'code' => 'B01',
            'price' => $p2Price,
        ]);
        $freeItems = 1;
        $discount = Discount::create([
            'type' => DiscountType::BOGO->name,
            'value' => $freeItems,
            'min_quantity' => 2,
            'applicable_model_type' => Product::class,
            'applicable_model_id' => $product1->id,
            'valid_from' => Carbon::now()->subDay()->toDateString(),
            'valid_until' => null,
        ]);

        $request1 = Request::create('/api/carts', 'POST', [
            'productCode' => $product1->code,
            'quantity' => $quantity,
        ]);
        $request2 = Request::create('/api/carts', 'POST', [
            'productCode' => $product2->code,
            'quantity' => $quantity,
        ]);

        $cartService = app(CartService::class);
        $cartService->addItem($request1, $cart->id);
        $result = $cartService->addItem($request2, $cart->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertEquals($totalPrice, $result['total_price']);

        // Assert that the first product (product1) has NO free item (BOGO)
        $this->assertCount(0, $result['items'][0]['free_items']);

        // Assert the final item price
        $this->assertEquals($p1Price, $result['items'][0]['final_item_price']);
    }

    public function test_add_items_with_bogo_and_percentage_discount(): void
    {
        // Set up real data for the test
        $p1Price = 5000;
        $p2Price = 10000;
        $quantity = 1;
        $cart = Cart::create();
        $product1 = Product::create([
            'name' => 'Red Widget',
            'code' => 'R01',
            'price' => $p1Price,
        ]);
        $product2 = Product::create([
            'name' => 'Blue Widget',
            'code' => 'B01',
            'price' => $p2Price,
        ]);
        $freeItems = 1;
        $discount1 = Discount::create([
            'type' => DiscountType::BOGO->name,
            'value' => $freeItems,
            'min_quantity' => 1,
            'applicable_model_type' => Product::class,
            'applicable_model_id' => $product1->id,
            'valid_from' => Carbon::now()->subDay()->toDateString(),
            'valid_until' => null,
        ]);
        $discount2Percentage = 20;
        $discount2 = Discount::create([
            'type' => DiscountType::PERCENTAGE->name,
            'value' => $discount2Percentage,
            'min_quantity' => 1,
            'applicable_model_type' => Product::class,
            'applicable_model_id' => $product2->id,
            'valid_from' => Carbon::now()->subHour()->toDateString(),
            'valid_until' => null,
        ]);

        $totalPrice = $p1Price + ($p2Price * ((100 - $discount2Percentage) / 100));

        $request1 = Request::create('/api/carts', 'POST', [
            'productCode' => $product1->code,
            'quantity' => $quantity,
        ]);
        $request2 = Request::create('/api/carts', 'POST', [
            'productCode' => $product2->code,
            'quantity' => $quantity,
        ]);

        $cartService = app(CartService::class);
        $cartService->addItem($request1, $cart->id);
        $result = $cartService->addItem($request2, $cart->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertEquals($totalPrice, $result['total_price']);

        // Assert that the first product (product1) has 1 free item (BOGO)
        $this->assertCount(1, $result['items'][0]['free_items']);
        $this->assertEquals($freeItems, $result['items'][0]['free_items'][0]);

        // Assert the final item price
        $this->assertEquals($p1Price, $result['items'][0]['final_item_price']);

        // Assert that product 2 has the 20% discount applied
        $expectedDiscountedPrice = $p2Price * ((100 - 20) / 100);  // 20% off
        $this->assertEquals($expectedDiscountedPrice, $result['items'][1]['final_item_price']);
    }
}
