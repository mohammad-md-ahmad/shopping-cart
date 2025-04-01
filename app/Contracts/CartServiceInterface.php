<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface CartServiceInterface
{
    public function get(string $cartId): array;

    public function addItem(Request $request, ?string $cartId): array;
}
