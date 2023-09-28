<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoryExport implements FromCollection, WithHeadings
{
    public function __construct()
    {
        $this->lang = request('lang') ?? null;
    }


    public function collection()
    {
        $lang = $this->lang;
        $categories = Category::query()->with([
            'translation' => function ($query) use ($lang) {
                $query->where('locale', $lang);
            },
            'children.translation' => function ($query) use ($lang) {
                $query->where('locale', $lang);
            },
            'children.children.translation' => function ($query) use ($lang) {
                $query->where('locale', $lang);
            }
        ])
            ->where('parent_id',0)
            ->orderBy('id', 'desc')
            ->get();
        $collection = collect();
        $i = null;
        $categories->map(function ($model) use ($collection, $i) {
            foreach ($model->children as $child) {
                $collection->push([
                    'title' => $model->translation ? $model->translation->title : '',
                    'childName' => $child->translation ? $child->translation->title : '',
                    'grandChildName' =>
                        $child->children->where('parent_id', $child->id)->first() ?
                            ($child->children->where('parent_id', $child->id)->first()->translation ?
                                $child->children->where('parent_id', $child->id)->first()->translation->title : '') : ''
                ]);

                $i++;
                $j = null;
                foreach ($child->children as $grandChild) {
                    if ($j !== null) {
                        $collection->push([
                            'title' => $model->translation ? $model->translation->title : '',
                            'childName' => $child->translation ? $child->translation->title : '',
                            'grandChildName' => $grandChild->translation ? $grandChild->translation->title : ''
                        ]);
                    }
                    $j++;
                }
            }
        });
//        dd($collection);
        return $collection->map(function ($query) {
            return $this->tableBody($query);
        });
    }

    public function headings(): array
    {
        return [
            'Category name',
            'SubCategory name',
            'Section name',
        ];
    }

    private function tableBody($item): array
    {
        return [
            $item['title'],
            $item['childName'],
            $item['grandChildName'],
        ];
    }


}
