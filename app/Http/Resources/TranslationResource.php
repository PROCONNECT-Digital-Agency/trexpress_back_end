<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TranslationResource extends JsonResource
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
            "id" => (int)  $this->id,
            "locale" => (string) $this->locale,
            "title" => $this->when($this->title, (string)  $this->title),
            'short_desc' => $this->when($this->short_desc, (string) $this->short_desc),
            'description' => $this->when($this->description, (string) $this->description),
            'address' => $this->when($this->address, (string) $this->address),
            'question' => $this->when($this->question, (string) $this->question),
            'answer' => $this->when($this->answer, (string) $this->answer),
            'client_title' => $this->when($this->client_title, (string) $this->client_title),
            'secret_title' => $this->when($this->secret_title, (string) $this->secret_title),
        ];
    }
}
