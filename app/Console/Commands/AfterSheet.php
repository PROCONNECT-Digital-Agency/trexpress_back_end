<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AfterSheet extends Command
{

    protected $signature = 'update:products:galleries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Product galleries update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $result = DB::table('before_galleries')
                ->distinct('product_id')
                ->orderBy('parent', 'desc')
                ->get()
                ->chunk(500);

            foreach ($result as $images) {

                $this->downloadImages($images->toArray());

            }

        } catch (Throwable $e) {
            $this->error($e);
        }

    }

    public function downloadImages($images) {

        $galleries      = [];
        $deleteImages   = [];

        $mh = curl_multi_init();

        $handles = [];

        foreach ($images as $image) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $image->url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($mh, $ch);
            curl_multi_setopt($mh, CURLMOPT_PIPELINING, 0);

            $handles[] = [
                'id'         => $image->id,
                'url'        => $image->url,
                'product_id' => $image->product_id,
                'parent'     => $image->parent,
                'ch'         => $ch
            ];

        }

        $running = 0;

        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while ($running > 0);

        $handles = collect($handles)->groupBy('product_id');

        foreach ($handles as $handle) {

            foreach ($handle as $data) {

                $handle = $data['ch'];

                $content = curl_multi_getcontent($handle);
                $info    = curl_getinfo($handle);

                if ($info['http_code'] == 200) {

                    $fileName = basename($info['url']);

                    $name = 'products/' . $data['product_id'] . time() . '.' . substr(strrchr($fileName, '.'), 1);

                    $url = "public/images/$name";

                    Storage::disk('do')->put($url, $content, 'public');

                    $galleries[] = [
                        'title'         => $url,
                        'path'          => $name,
                        'type'          => 'products',
                        'loadable_type' => 'App\Models\Product',
                        'loadable_id'   => $data['product_id'],
                    ];

                    $any = DB::table('before_galleries')
                        ->where('product_id', $data['product_id'])
                        ->where('parent', 1)
                        ->exists();

                    if (data_get($data, 'parent') || !$any) {
                        DB::table('products')
                            ->where('id', $data['product_id'])
                            ->update([
                                'img' => $name,
                            ]);
                    }

                    $deleteImages[] = $data['id'];
                }

                curl_multi_remove_handle($mh, $handle);
            }

        }

        curl_multi_close($mh);

        DB::table('galleries')->insert($galleries);

        DB::table('before_galleries')
            ->whereIn('id', $deleteImages)
            ->delete();
//        $this->info("mb:" . memory_get_usage(true) / (1024 * 1024));
    }


}
