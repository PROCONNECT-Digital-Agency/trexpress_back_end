<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'order_id' => Order::inRandomOrder()->first(),
            'shop_id' => Shop::inRandomOrder()->first(),
            'price' => rand(100, 300),
            'tax' => rand(1, 10),
            'status' => 'new',
            'delivery_fee' => rand(10, 50),
            'created_at' => now(),
            'updated_at' => now()->addMinutes(rand(1,10)),
        ];
    }
}
