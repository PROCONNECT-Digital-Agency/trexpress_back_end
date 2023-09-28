<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Repositories\ProductTypeRepository\ProductTypeRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'uuid' => (string) $this->uuid,
            'keywords' => $this->when($this->keywords, (string) $this->keywords),
            'parent_id' => (int) $this->parent_id,
            'type' => $this->when($this->type, (string) $this->type),
            'position' => $this->when($this->position, (string) $this->position),
            'img' => $this->when(isset($this->img), (string) $this->img),
            'active' => (bool) $this->active,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' =>  $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'products_count' =>  $this->when($this->products_count, (int) $this->products_count),
            'has_children' => $this->children ? [] : null,

            // Relation
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'parent' => CategoryResource::make($this->whenLoaded('parent')),

        ];
    }

}
