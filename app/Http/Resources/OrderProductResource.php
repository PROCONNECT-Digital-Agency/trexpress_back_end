<?php

namespace App\Http\Resources;

use App\Models\Stock;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
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
            'stock_id' => (int) $this->stock_id,
            'origin_price' => (double)  $this->origin_price,
            'tax' => (double)  $this->tax,
            'discount' => (double)  $this->discount,
            'quantity' => (int)  $this->quantity,
            'total_price' => (double)  $this->total_price,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),

            // Relation
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'stock' =>  StockResource::make($this->whenLoaded('stock')),
        ];
    }
}
