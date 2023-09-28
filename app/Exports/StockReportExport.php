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

class StockReportExport implements FromCollection, WithMapping, ShouldAutoSize, WithBatchInserts, WithChunkReading,
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
            $row->product_translation_title,
            $row->product_bar_code,
            $row->status,
            $row->quantity,
            $row->deleted_at,
        ];
    }

    public function headings(): array
    {
        return [
            'Product title',
            'Sku',
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
