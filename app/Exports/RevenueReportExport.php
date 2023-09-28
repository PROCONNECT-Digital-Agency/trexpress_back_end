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

class RevenueReportExport implements FromCollection, WithMapping, ShouldAutoSize, WithBatchInserts, WithChunkReading,
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
            $row['date'],
            $row['orders'],
            $row['returns'],
            $row['net_sales'],
            $row['taxes'],
            $row['shipping'],
            $row['total_sales'],
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Orders',
            'Returns',
            'Net Sales',
            'Taxes',
            'Shipping',
            'Total sales',
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
