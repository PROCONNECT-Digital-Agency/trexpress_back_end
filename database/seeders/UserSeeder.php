<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'id' => 101,
                'uuid' => Str::uuid(),
                'firstname' => 'Mark',
                'lastname' => 'Ten',
                'email' => 'admin@gmail.com',
                'phone' => '998911902490',
                'birthday' => '1991-08-10',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => bcrypt('admin123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 102,
                'uuid' => Str::uuid(),
                'firstname' => 'Jonny',
                'lastname' => 'Cache',
                'email' => 'manager@gmail.com',
                'phone' => '998911902591',
                'birthday' => '1993-12-30',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => bcrypt('manager'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 103,
                'uuid' => Str::uuid(),
                'firstname' => 'Tom',
                'lastname' => 'Seed',
                'email' => 'seller@gmail.com',
                'phone' => '998911902692',
                'birthday' => '1990-12-31',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => bcrypt('seller'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 104,
                'uuid' => Str::uuid(),
                'firstname' => 'Mike',
                'lastname' => 'Taylor',
                'email' => 'moderator@gmail.com',
                'phone' => '998909035993',
                'birthday' => '1990-12-31',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => bcrypt('moderator'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 105,
                'uuid' => Str::uuid(),
                'firstname' => 'John',
                'lastname' => 'Deer',
                'email' => 'deliveryman@gmail.com',
                'phone' => '998909035994',
                'birthday' => '1990-12-31',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => bcrypt('deliveryman'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 106,
                'uuid' => Str::uuid(),
                'firstname' => 'User',
                'lastname' => 'User',
                'email' => 'user@gmail.com',
                'phone' => '998909035990',
                'birthday' => '1990-12-31',
                'gender' => 'male',
                'email_verified_at' => now(),
                'password' => bcrypt('deq789ag'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
        foreach ($users as $user) {
            User::updateOrInsert(['id' => data_get($user, 'id')], $user);
        }
        User::find(101)->syncRoles('admin');
        User::find(102)->syncRoles('manager');
        User::find(103)->syncRoles('seller');
        User::find(104)->syncRoles('moderator');
        User::find(105)->syncRoles('deliveryman');
        User::find(106)->syncRoles('user');
    }
}
