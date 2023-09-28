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

class ShopsReportExport implements FromCollection, WithMapping, ShouldAutoSize, WithBatchInserts, WithChunkReading,
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
            $row->shop_translation_title,
            $row->seller_firstname . ' ' . $row->seller_lastname,
            $row->completed_orders_count ?? 0,
            $row->completed_orders_price_sum ?? 0,
            $row->canceled_orders_price_sum ?? 0,
            $row->canceled_orders_count ?? 0,
            $row->items_sold ?? 0,
            $row->net_sales ?? 0,
            $row->total_earned ?? 0,
            $row->tax_earned ?? 0,
            $row->products_count ?? 0,
            $row->deleted_at ?? ''];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Seller',
            'Completed orders count',
            'Completed orders price sum',
            'Canceled orders count',
            'Canceled orders price sum',
            'Items sold',
            'Net Sales',
            'total_earned',
            'tax_earned',
            'products_count',
            'Deleted at'];
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
