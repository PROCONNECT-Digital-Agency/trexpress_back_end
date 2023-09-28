<?php

namespace App\Repositories\BrandRepository;

use App\Models\Brand;
use App\Repositories\CoreRepository;

class BrandRepository extends CoreRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return Brand::class;
    }

    public function brandsList(array $array = [])
    {
        return $this->model()->updatedDate($this->updatedDate)
            ->filter($array)->orderByDesc('id')->get();
    }

    /**
     * Get brands with pagination
     */
    public function brandsPaginate($perPage, $active = null, $array = [])
    {
        return $this->model()->withCount([
            'products' => fn($q) => $q->whereHas('shop', function ($item) {
                $item->whereNull('deleted_at');
            })->whereHas('stocks', function ($item){
                $item->where('quantity', '>', 0)->where('price', '>', 0);
            })
        ])
            ->filter($array)->updatedDate($this->updatedDate)
            ->when(isset($array['search']), function ($q) use ($array) {
                $q->where('title', 'LIKE',  "%". $array['search'] ."%");
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Get one brands by Identification number
     */
    public function brandDetails(int $id)
    {
        return $this->model()->find($id);
    }

    public function brandsSearch(string $search, $active = null){

        return $this->model()
            ->withCount('products')
            ->where(function ($query) use($search) {
                $query->where('title', 'LIKE', '%'. $search . '%');
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active);
            })
            ->orderByDesc('id')
            ->latest()->take(50)->get();
    }
}
