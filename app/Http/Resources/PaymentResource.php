<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'tag' => (string) $this->tag,
            'input' =>  $this->when($this->input, (int) $this->input),
            'client_id' => $this->when($this->client_id,(string) $this->client_id),
            'secret_id' => $this->when($this->secret_id, (string) $this->secret_id),
            'sandbox' => $this->when($this->sandbox, (boolean) $this->sandbox),
            'active' => (boolean) $this->active,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),

            // Relations
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
        ];
    }
}
