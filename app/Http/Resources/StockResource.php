<?php

namespace App\Http\Resources;

use App\Models\ExtraValue;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'countable_id' => (int) $this->countable_id,
            'price' => $this->when($this->price, (double) $this->price),
            'quantity' => $this->when($this->quantity, (int) $this->quantity),
            'discount' => $this->when($this->discount, (double) $this->actualDiscount),
            'tax' => $this->when($this->taxPrice, round($this->taxPrice, 2)),
            'discount_expired' => $this->when(isset($this->discount_expired) && $this->discount_expired < '2040-08-01', $this->discount_expired),
            'total_price' => $this->when($this->price, (double) $this->price - $this->actualDiscount),

            // Relation
            'extras' => ExtraValueResource::collection($this->whenLoaded('stockExtras')),
            'product' => ProductResource::make($this->whenLoaded('countable'))
        ];
    }
}
