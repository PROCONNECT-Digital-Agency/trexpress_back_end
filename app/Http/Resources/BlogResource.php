<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
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
            'user_id' => $this->when($this->user_id, (string) $this->user_id),
            'type' => $this->type,
            'published_at' => $this->when(isset($this->published_at), (string) $this->published_at),
            'active' => $this->when(isset($this->active), (boolean) $this->active),
            'img' => $this->when($this->img, (string) $this->img),
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'deleted_at' => $this->when($this->deleted_at, optional($this->deleted_at)->format('Y-m-d H:i:s')),

            // Relations
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'author' => UserResource::make($this->whenLoaded('author')),
        ];
    }
}
