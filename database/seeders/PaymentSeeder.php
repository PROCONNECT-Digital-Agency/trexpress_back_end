<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\PaymentTranslation;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payments = [
            [
                'id' => 1,
                'tag' => Payment::CASH,
                'input' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'tag' => Payment::WALLET,
                'input' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'tag' => Payment::PAYPAL,
                'input' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'tag' => Payment::STRIPE,
                'input' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'tag' => Payment::PAYSTACK,
                'input' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'tag' => Payment::RAZORPAY,
                'input' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($payments as $item) {
            Payment::updateOrInsert(['id' => $item['id']], $item);
        }

        $paymentLang = [
            [
                'id' => 1,
                'payment_id' => 1,
                'locale' => 'en',
                'title' => 'Cash',
            ],
            [
                'id' => 2,
                'payment_id' => 2,
                'locale' => 'en',
                'title' => 'Wallet',
            ],
            [
                'id' => 3,
                'payment_id' => 3,
                'locale' => 'en',
                'title' => 'Paypal',
                'client_title' => 'Client ID',
                'secret_title' => 'Secret ID',
            ],
            [
                'id' => 4,
                'payment_id' => 4,
                'locale' => 'en',
                'title' => 'Stripe',
                'client_title' => 'Key ID',
                'secret_title' => 'Public ID',
            ],
            [
                'id' => 5,
                'payment_id' => 5,
                'locale' => 'en',
                'title' => 'Paystack',
                'client_title' => 'Public Key',
                'secret_title' => 'Secret Key',
            ],
            [
                'id' => 6,
                'payment_id' => 6,
                'locale' => 'en',
                'title' => 'Razorpay',
                'client_title' => 'Key ID',
                'secret_title' => 'Secret ID',
            ],
        ];

        foreach ($paymentLang as $lang) {
            PaymentTranslation::updateOrInsert(['payment_id' => $lang['payment_id']], $lang);
        }
    }
}
