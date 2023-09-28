<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first(),
            'price' => rand(100, 500),
            'currency_id' => Currency::inRandomOrder()->first(),
            'rate' => Currency::inRandomOrder()->first()->rate,
            'created_at' => now(),
            'updated_at' => now()->addMinutes(rand(0,10)),
        ];
    }
}
