<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletHistoryResource extends JsonResource
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
            'uuid' => (string) $this->uuid,
            'wallet_uuid' => (string) $this->wallet_uuid,
            'transaction_id' => (int) $this->transaction_id,
            'type' => (string) $this->type,
            'price' => (double) $this->price,
            'note' => (string) $this->note,
            'status' => (string) $this->status,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),

            // Relations
            'author' => UserResource::make($this->whenLoaded('author')),
            'user' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}
