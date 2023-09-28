<?php

namespace App\Exports;

use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection,
    ShouldAutoSize,
    WithBatchInserts,
    WithChunkReading,
    WithHeadings,
    WithMapping,
};

class CategoriesReportExport implements FromCollection, WithMapping, ShouldAutoSize, WithBatchInserts, WithChunkReading, WithHeadings
{
    private $rows;

    /**
     * BookingExport constructor.
     *
     * @param Collection $rows
     */
    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->rows;
    }

    public function map($row): array
    {
        /** @var Category $row */
        return [
            $row->parent->translation->title.'>'.$row->translation->title,
            $row->items_sold,
            $row->net_sales,
            $row->products_count,
            $row->orders_count,
        ];
    }

    public function headings(): array
    {
        return [
            'Category',
            'Items sold',
            'Net sales',
            'Products',
            'Orders',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
