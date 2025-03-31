<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $products = [
                [
                    'name' => 'Red Widget',
                    'code' => 'R01',
                    'price' => '1000',
                ],
                [
                    'name' => 'Blue Widget',
                    'code' => 'B01',
                    'price' => '2000',
                ],
                [
                    'name' => 'Green Widget',
                    'code' => 'G01',
                    'price' => '3000',
                ],
                [
                    'name' => 'White Widget',
                    'code' => 'W01',
                    'price' => '6000',
                ],
            ];

            foreach ($products as $product) {
                DB::table('products')->updateOrInsert(
                    [
                        'name' => $product['name'],
                        'code' => $product['code'],
                    ],
                    $product
                );
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
