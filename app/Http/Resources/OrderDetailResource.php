<?php

namespace App\Http\Resources;

use App\Models\ProductTranslation;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
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
            'shop_id' => (int) $this->shop_id,
            'delivery_fee' => (int) $this->delivery_fee,
            'price' => (double) $this->price,
            'tax' => (double) $this->tax,
            'status' => (string) $this->status,
            'delivery_date' => (string) $this->delivery_date,
            'delivery_time' => (string) $this->delivery_time,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),

            // Relations
            'order_stocks' => OrderProductResource::collection($this->whenLoaded('orderStocks')),
            'delivery_address' => UserAddressResource::make($this->whenLoaded('deliveryAddress')),
            'delivery_type' => DeliveryResource::make($this->whenLoaded('deliveryType')),
            'transaction' => TransactionResource::make($this->whenLoaded('transaction')),
            'shop' => ShopResource::make($this->whenLoaded('shop')),
        ];
    }
}
