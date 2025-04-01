<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $fees = [
                [
                    'fee' => 4950,
                    'min_amount' => 0,
                    'max_amount' => 49999,
                ],
                [
                    'fee' => 2950,
                    'min_amount' => 50000,
                    'max_amount' => 89999,
                ],
                [
                    'fee' => 0,
                    'min_amount' => 90000,
                    'max_amount' => null,
                ],
            ];

            foreach ($fees as $fee) {
                DB::table('delivery_fees')->updateOrInsert(
                    [
                        'fee' => $fee['fee'],
                        'min_amount' => $fee['min_amount'],
                        'max_amount' => $fee['max_amount'],
                    ],
                    $fee
                );
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
