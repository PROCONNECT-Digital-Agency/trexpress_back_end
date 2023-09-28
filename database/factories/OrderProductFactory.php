<?php

namespace Database\Factories;

use App\Models\OrderDetail;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'order_detail_id' => OrderDetail::inRandomOrder()->first(),
            'stock_id' => Stock::inRandomOrder()->first(),
            'origin_price' => rand(100, 300),
            'total_price' => rand(300, 500),
            'tax' => rand(1, 10),
            'discount' => 0,
            'quantity' => rand(1, 3),
            'created_at' => now(),
            'updated_at' => now()->addMinutes(rand(0,10)),
        ];
    }
}
