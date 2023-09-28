<?php

namespace App\Exports;

use App\Models\Currency;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection,WithHeadings
{
    private int $shop_id;

    public function __construct($shop_id,  protected array $filter)
    {
        $this->shop_id = $shop_id;
        $this->lang = request('lang') ?? null;
    }
    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $filter = $this->filter;
        $model = Product::with([
            'category.translation' => fn($q) => $q->where('locale', $this->lang),
            'translation' => fn($q) => $q->where('locale', $this->lang),
        ])
            ->when(isset($filter['brand_id']),function ($q) use ($filter){
                $q->where('brand_id',$filter['brand_id']);
            })
            ->when(isset($filter['category_id']),function ($q) use ($filter){
                $q->where('category_id',$filter['category_id']);
            })
            ->where('shop_id', $this->shop_id)->get();
        return $model->map(function ($model){
            return $this->productModel($model);
        });
    }

    public function headings(): array
    {
        return [
            'Picture',
            'Product name',
            'Description',
            'Category',
            'Barcode',
        ];
    }

    private function productModel($item): array
    {
        return [
            'Picture' => $item->img,
            'Product name' =>  $item->translation ? $item->translation->title : '',
            'Description' =>  $item->translation ? $item->translation->description : '',
            'Category' => $item?->category?->translation?->title,
            'Barcode' => $item->bar_code,
        ];
    }
}
