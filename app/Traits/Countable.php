<?php

namespace App\Traits;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait Countable
{
    public function addInStock($request, $id = null)
    {
        $this->stocks()->delete();
        $extras = collect($request->extras)->unique('id');
        foreach ($extras as $item) {
            $stock = $this->stocks()->create([
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ]);
            $stock->stockExtras()->attach($item['id'] ?? []);
        }
    }

    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'countable');
    }

    public function stock(): MorphTo
    {
        return $this->morphTo(Stock::class, 'countable');
    }

    public function stocksWithTrashed(): MorphMany
    {
        return $this->morphMany(Stock::class, 'countable')->withTrashed();
    }
}
