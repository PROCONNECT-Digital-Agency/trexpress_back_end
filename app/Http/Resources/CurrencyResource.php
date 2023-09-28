<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
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
            'id'     => (int) $this->id,
            'symbol' => (string) $this->symbol,
            'title'  => (string) $this->title,
            "rate"   => $this->when($this->rate, (double) $this->rate),
            "default" => (bool) $this->default,
            "active"  => (bool) $this->active,
            "created_at" => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            "updated_at" => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
        ];
    }
}
