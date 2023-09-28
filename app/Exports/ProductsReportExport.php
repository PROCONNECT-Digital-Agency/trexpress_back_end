<?php

namespace App\Exports;

use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection,
    ShouldAutoSize,
    WithBatchInserts,
    WithChunkReading,
    WithHeadings,
    WithMapping,
    WithStyles
};
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsReportExport implements FromCollection, WithMapping, ShouldAutoSize, WithBatchInserts, WithChunkReading,
    WithHeadings
{
    private Collection $rows;

    /**
     * BookingExport constructor.
     *
     * @param Collection $rows
     */
    public function __construct(Collection $rows)
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
        /** @var Product $row */
        return [
            $row->translation_title,
            $row->bar_code,
            $row->items_sold,
            moneyFormatter($row->net_sales),
            $row->orders_count,
            $row->category?->self_and_parent_title ?: null,
            $row->variations,
            $row->status,
            $row->stocks_total,
            $row->deleted_at ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Product title',
            'Sku',
            'Items sold',
            'Net sales',
            'Order',
            'Category',
            'Variations',
            'Status',
            'Stock',
            'Deleted at',
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
