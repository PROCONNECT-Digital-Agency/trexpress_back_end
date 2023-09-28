<?php

namespace App\Http\Resources;

use App\Models\ShopTranslation;
use Illuminate\Http\Resources\Json\JsonResource;
use function Symfony\Component\String\s;

class ShopResource extends JsonResource
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
            'id'  => (int) $this->id,
            'uuid' => $this->uuid,
            'user_id' => (int) $this->user_id,
            'tax' => (int) $this->tax,
            'delivery_range' => (int) $this->delivery_range,
            'percentage' => (double) $this->percentage,
            'location' => $this->when($this->location,[
                'latitude' => $this->when($this->location, (float) $this->location['latitude'] ?? null),
                'longitude' => $this->when($this->location,(float) $this->location['longitude'] ?? null),
            ]),
            'phone' =>  $this->phone,
            'show_type' => (bool) $this->show_type,
            'open' => (bool) $this->open,
            'visibility' => (bool) $this->visibility,
            'open_time' => $this->open_time?->format('H:i'),
            'close_time' => $this->close_time?->format('H:i'),
            'background_img' => $this->background_img,
            'logo_img' => $this->logo_img,
            'min_amount' => (double) $this->min_amount,
            'mark' => (string) $this->mark,
            'status' => (string) $this->status,
            'status_note' => (string) $this->status_note,
            'invite_link' => $this->when(auth('sanctum')->check() && auth('sanctum')->user()->hasRole('seller'), '/shop/invitation/' .$this->uuid . '/link'),
            'rating_avg' => $this->when($this->reviews_avg_rating, number_format($this->reviews_avg_rating, 2)),
            'reviews_count' => $this->when($this->reviews_count, (int) $this->reviews_count),
            'orders_count' => $this->when($this->orders_count, (int) $this->orders_count),
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'deleted_at' => $this->when($this->deleted_at, optional($this->deleted_at)->format('Y-m-d H:i:s')),

            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'seller' => UserResource::make($this->whenLoaded('seller')),
            'deliveries' => DeliveryResource::collection($this->whenLoaded('deliveries')),
            'subscription' => $this->whenLoaded('subscription'),
            'point_deliveries' => $this->whenLoaded('pointDeliveries'),
        ];
    }
}
