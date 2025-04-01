<?php

namespace App\Contracts;

interface DiscountStrategyInterface
{
    public function applyDiscount();

    public function calculateDiscount();
}
