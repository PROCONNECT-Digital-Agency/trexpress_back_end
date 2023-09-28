<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
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
            'type' => (string) $this->type,
            'price' => (double) $this->price,
            'start' => (string) $this->start,
            'end' => $this->end ?? null,
            'active' => (boolean) $this->active,
            'img' => (string) $this->img,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),

            // Relations
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'galleries' => GalleryResource::collection($this->whenLoaded('galleries'))

        ];
    }
}
