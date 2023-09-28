<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExtraValueResource extends JsonResource
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
            'extra_group_id' => (int) $this->extra_group_id,
            'value' => (string) $this->value,
            'active' => (boolean) $this->active,

            // Relations
            'group' => ExtraGroupResource::make($this->whenLoaded('group')),
            'galleries' => GalleryResource::collection($this->whenLoaded('galleries')),
        ];
    }
}
