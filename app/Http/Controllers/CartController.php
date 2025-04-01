<?php

namespace App\Http\Controllers;

use App\Contracts\CartServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CartController
{
    public function __construct(
        protected CartServiceInterface $cartService,
    ) {}

    public function get(Request $request): JsonResponse
    {
        try {
            $cartId = $request->route()->parameter('cartId');
            $data = $this->cartService->get($cartId);

            return response()->json([
                'message' => 'Cart has been retrieved successfully',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());

            return response()->json([
                'message' => 'Something went wrong!',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function addItem(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'productCode' => 'required|string',
                'quantity' => 'required|integer|min:1',
            ]);

            $cartId = $request->route()->parameter('cartId');
            $data = $this->cartService->addItem($request, $cartId);

            return response()->json([
                'message' => 'Item has been added to the cart successfully',
                'data' => $data,
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            Log::error($e->getMessage(), $e->getTrace());

            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());

            return response()->json([
                'message' => 'Something went wrong!',
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
