<?php

namespace App\Repositories\FindexRepository;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FindexRepository
{
    const FINDEX_URL = 'https://api2.findex.az/api/partners';
    const SECRET_KEY = 'pasMallSi';
    const TTL = 604800; // 7 days


    public function declarationById($id)
    {
//        $declaration = $this->declarationList()->where('id',$id)->first();
//        dd($declaration);
        $test = collect(Http::withHeaders(['application/json'])->get(self::FINDEX_URL . '/declaration_list?secret='.
            self::SECRET_KEY)->json('data'));
    }

    protected function declarationList()
    {
        return Cache::remember('declaration_list', self::TTL, function (){
            return collect(Http::withHeaders(['application/json'])->get(self::FINDEX_URL . '/declaration_list?secret='.self::SECRET_KEY)->json('data'));
        });
    }
}

