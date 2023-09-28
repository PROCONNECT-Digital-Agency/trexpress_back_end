<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoryImport implements ToCollection, WithHeadingRow, WithBatchInserts
{
    use Importable;

    /**
     * @var array|Application|Request|string
     */
    protected $lang;

    public function __construct()
    {
        $this->lang = request('lang') ?? 'en';
    }

    /**
     * @param Collection $collection
     * @return mixed
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            if (isset($row['parent_category'])) {

                $category = Category::whereHas('translation', function ($q) use ($row) {
                    $q->where('locale', $this->lang)->where('title', $row['parent_category']);
                })->first();

                if (!$category) {
                    $category = Category::create([
                        'keywords' => $row['parent_category']
                    ]);
                    $category->translation()->create([
                        'locale' => $this->lang,
                        'title' => $row['parent_category'],
                    ]);
                }

                if (isset($row['category'])) {

                    $subCategory = Category::whereHas('translation', function ($q) use ($row) {
                        $q->where('locale', $this->lang)->where('title', $row['category']);
                    })->first();

                    if (!$subCategory) {
                        $subCategory = Category::create([
                            'parent_id' => $category->id,
                            'keywords' => $row['category']
                        ]);
                        $subCategory->translation()->create([
                            'locale' => $this->lang,
                            'title' => $row['category'],
                        ]);
                    }


                    if (isset($row['child_category'])) {

                        $section = Category::whereHas('translation', function ($q) use ($row) {
                            $q->where('locale', $this->lang)->where('title', $row['child_category']);
                        })->first();

                        if (!$section) {
                            $section = Category::create([
                                'parent_id' => $subCategory->id,
                                'keywords' => $row['child_category']
                            ]);
                            $section->translation()->create([
                                'locale' => $this->lang,
                                'title' => $row['child_category'],
                            ]);
                        }

                    }

                }
            }

        }
        return true;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
