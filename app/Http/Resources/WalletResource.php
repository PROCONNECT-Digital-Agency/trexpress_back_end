<?php

namespace App\Http\Resources;

use App\Models\WalletHistory;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
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
            'uuid' => (string) $this->uuid,
            'user_id' => (int) $this->user_id,
            'price' => (double) $this->price,
            'symbol' => (string) $this->symbol,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'deleted_at' => $this->when($this->deleted_at, optional($this->deleted_at)->format('Y-m-d H:i:s')),

            // Relations
            'histories' => WalletHistoryResource::collection($this->whenLoaded('histories')),
            'currency' => CurrencyResource::make($this->whenLoaded('currency')),
        ];
    }
}
