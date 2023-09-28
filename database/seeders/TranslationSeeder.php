<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = Lang::get('errors');

        foreach ($data as $index => $item){
            Translation::updateOrInsert(['key' => $index], [
                'status' => true,
                'locale' => 'en',
                'group' => 'errors',
                'value' => $item,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
