<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection,
    ShouldAutoSize,
    WithBatchInserts,
    WithChunkReading,
    WithHeadings,
    WithMapping,
};

class VariationsReportExport implements FromCollection, WithMapping, ShouldAutoSize, WithBatchInserts, WithChunkReading,
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
        return [
            $row->translation_title . "($row->extrasFormatted)",
            $row->bar_code,
            $row->items_sold,
            $row->net_sales,
            $row->orders_count,
            $row->status,
            $row->quantity,
            $row->deleted_at ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Product title(Variation)',
            'Sku',
            'Items sold',
            'Net Sales',
            'Orders',
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
