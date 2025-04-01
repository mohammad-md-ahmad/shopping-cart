<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Models\Product;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $discounts = [
                [
                    'type' => DiscountType::BOGO->name,
                    'value' => 1,
                    'min_quantity' => 1,
                    'applicable_model_type' => Product::class,
                    'applicable_model_id' => DB::table('products')->first()?->id,
                    'valid_from' => Carbon::now()->toDateString(),
                    'valid_until' => null,
                ],
                [
                    'type' => DiscountType::PERCENTAGE->name,
                    'value' => 20,
                    'min_quantity' => 1,
                    'applicable_model_type' => Product::class,
                    'applicable_model_id' => DB::table('products')->where('id', 2)->first()?->id,
                    'valid_from' => Carbon::now()->toDateString(),
                    'valid_until' => null,
                ],
            ];

            foreach ($discounts as $discount) {
                DB::table('discounts')->updateOrInsert(
                    [
                        'type' => $discount['type'],
                        'value' => $discount['value'],
                        'min_quantity' => $discount['min_quantity'],
                        'applicable_model_type' => $discount['applicable_model_type'],
                        'applicable_model_id' => $discount['applicable_model_id'],
                        'valid_from' => $discount['valid_from'],
                        'valid_until' => $discount['valid_until'],
                    ],
                    $discount
                );
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
