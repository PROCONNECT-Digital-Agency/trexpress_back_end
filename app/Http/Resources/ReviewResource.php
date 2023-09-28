<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'reviewable_id' => $this->when($this->reviewable_id, (int) $this->reviewable_id),
            'rating' => (string) $this->rating,
            'comment' => (string) $this->comment,
            'img' => $this->when($this->img, $this->img),
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),


            'galleries' => GalleryResource::collection($this->whenLoaded('galleries')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'order' => $this->when(
                $this->reviewable_type == OrderDetail::class,
                OrderResource::make($this->whenLoaded('reviewable'))
            ),
            'product' => $this->when(
                $this->reviewable_type == Product::class,
                ProductResource::make($this->whenLoaded('reviewable'))
            ),
           ];
    }
}
