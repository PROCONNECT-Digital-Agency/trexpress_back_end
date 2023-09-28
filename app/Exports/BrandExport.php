<?php

namespace App\Exports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BrandExport implements FromCollection, WithHeadings
{
    public function __construct()
    {
        $this->lang = request('lang') ?? null;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $model = Brand::all();
        return $model->map(function ($model){
            return $this->tableBody($model);
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Brand name',
            'Status',
        ];
    }

    private function tableBody($item): array
    {
        return [
            'id' => $item->id,
            'Brand name' => $item->title ?? null,
            'Status' => $item->active ? 'active' : 'inactive',
        ];
    }
}
