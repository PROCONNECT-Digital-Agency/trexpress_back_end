<?php

namespace App\Services\ProductService;

use App\Models\Stock;

class StockService extends \App\Services\CoreService
{

    protected function getModelClass()
    {
        return Stock::class;
    }


    public function decrementStocksQuantity(array $shops) {

        foreach ($shops as $shop) {
            foreach ($shop['products'] as $product) {
                $stock = Stock::find(data_get($product, 'id'));

                if (!$stock) {
                    continue;
                }

                Stock::find($stock['id'])->decrement('quantity', $product['qty']);
            }

        }
    }

    public function incrementStocksQuantity($shops) {
        foreach ($shops as $shop) {
            foreach ($shop->orderStocks as $stock) {
                Stock::find($stock->stock_id)->increment('quantity', $stock->quantity);
            }
        }
    }
}
