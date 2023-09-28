<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class CategoryCustomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Category|JsonResource $this */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'keywords' => $this->when($this->keywords, (string) $this->keywords),
            'parent_id' => (int) $this->parent_id,
            'img' => $this->when(isset($this->img), (string) $this->img),
            'products' => ProductResource::collection($this->whenLoaded('product')),

            // Relation
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
        ];
    }
}
