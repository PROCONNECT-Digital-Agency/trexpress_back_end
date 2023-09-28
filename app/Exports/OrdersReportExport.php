<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderProduct;
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

class OrdersReportExport implements FromCollection, WithMapping, ShouldAutoSize, WithBatchInserts, WithChunkReading, WithHeadings
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
        /** @var Order $row */
        return [
            $row->created_at,
            $row->id,
            $row->status,
            $row->user_firstname.' '.$row->user_lastname,
            $row->user_active,
            orderProductsTitle($row),
            $row->item_sold,
            '',//?
            $row->net_sales
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            '#',
            'Status',
            'Customer',
            'Customer type',
            'Products',
            'Item sold',
            'coupons',
            'Net sales',
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
