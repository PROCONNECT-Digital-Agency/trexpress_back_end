<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
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
            'url' => (string) $this->url,
            'products' => (array) $this->products,
            'img' => (string) $this->img,
            'active' => (boolean) $this->active,
            'likes' => (int) $this->likes_count,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' =>  $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),

            // Relations
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'shop' => ShopResource::make($this->whenLoaded('shop')),
            'galleries' => GalleryResource::collection($this->whenLoaded('galleries'))
        ];
    }
}
