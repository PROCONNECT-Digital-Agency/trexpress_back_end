<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data =  [
            [
                'id' => 97,
                'type' => 'orders',
                'price' => 100.00,
                'month' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 98,
                'type' => 'orders',
                'price' => 250.00,
                'month' => 3,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 99,
                'type' => 'orders',
                'price' => 450.00,
                'month' => 6,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 100,
                'type' => 'orders',
                'price' => 800.00,
                'month' => 12,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $item){
            Subscription::updateOrInsert(['id' => $item['id']], $item);
        }
    }
}
