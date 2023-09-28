<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ExtraGroup;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Stock;
use App\Models\Subscription;
use App\Models\User;
use Database\Factories\OrderFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call(LanguageSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(RoleSeeder::class);
//        $this->call(SubscriptionSeeder::class);
        $this->call(TranslationSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PaymentSeeder::class);
//        $this->call(SmsGatewaySeeder::class);
//        $this->call(UnitSeeder::class);


//        if (app()->environment() == 'local') {
//            Category::factory()->hasTranslations(1)->count(10)->create();
//            Brand::factory()->count(10)->create();
//            ExtraGroup::factory()->hasTranslation(1)->hasExtraValues(3)->count(5)->create();
//              DB::transaction(function () {
//                  $user = User::create([
//                      'uuid' => Str::uuid(),
//                      'firstname' => 'Seller',
//                      'lastname' => 'Seller',
//                      'email' => 'seller@gmail.com',
//                      'phone' => '9989119021961',
//                      'birthday' => '1990-12-31',
//                      'gender' => 'male',
//                      'email_verified_at' => now(),
//                      'password' => bcrypt('123456'),
//                      'created_at' => now(),
//                      'updated_at' => now(),
//                  ]);
//
//                  $user->syncRoles('seller');
//
//                  $user2 = User::create([
//                      'uuid' => Str::uuid(),
//                      'firstname' => 'Seller',
//                      'lastname' => 'Seller',
//                      'email' => 'seller2@gmail.com',
//                      'phone' => '99891111961',
//                      'birthday' => '1990-12-31',
//                      'gender' => 'male',
//                      'email_verified_at' => now(),
//                      'password' => bcrypt('123456'),
//                      'created_at' => now(),
//                      'updated_at' => now(),
//                  ]);
//
//                  $user2->syncRoles('seller');
//
//                  Shop::query()->create([
//                      'uuid'              => Str::uuid(),
//                      'user_id'           => $user->id,
//                      'tax'               => 1,
//                      'delivery_range'    => 1,
//                      'percentage'        => 1,
//                      'phone'             => '21345',
//                      'show_type'         => 1,
//                      'open'              => 1,
//                      'visibility'        => 1,
//                      'open_time'         => '09:00',
//                      'close_time'        => '21:00',
//                      'background_img'    => 'sad',
//                      'logo_img'          => 'asd',
//                      'min_amount'        => 1,
//                      'status'            => 'new',
//                      'status_note'       => 'newnew',
//                      'mark'              => 'sadsad',
//                  ]);
//
//                  $user2->syncRoles('seller');
//
//                  Shop::query()->create([
//                      'uuid'              => Str::uuid(),
//                      'user_id'           => $user2->id,
//                      'tax'               => 1,
//                      'delivery_range'    => 1,
//                      'percentage'        => 1,
//                      'phone'             => '21345',
//                      'show_type'         => 1,
//                      'open'              => 1,
//                      'visibility'        => 1,
//                      'open_time'         => '09:00',
//                      'close_time'        => '21:00',
//                      'background_img'    => 'sad',
//                      'logo_img'          => 'asd',
//                      'min_amount'        => 1,
//                      'status'            => 'new',
//                      'status_note'       => 'newnew',
//                      'mark'              => 'sadsad',
//                  ]);
//              });

//            Order::factory()->has(OrderDetail::factory()->hasProducts(2)->count(3))->count(10)->create();
//        }
    }
}
