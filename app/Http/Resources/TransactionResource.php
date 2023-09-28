<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'payable_id' => (int) $this->payable_id,
            'price' => (double) $this->price,
            'payment_trx_id' => (string) $this->payment_trx_id,
            'note' => (string) $this->note,
            'perform_time' => $this->when($this->perform_time, optional($this->perform_time)->format('Y-m-d H:i:s')),
            'refund_time' => $this->when($this->refund_time, optional($this->refund_time)->format('Y-m-d H:i:s')),
            'status' => (string) $this->status,
            'status_description' => (string) $this->status_description,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'deleted_at' => $this->when($this->deleted_at, optional($this->deleted_at)->format('Y-m-d H:i:s')),

            // Relations
            'user' => UserResource::make($this->whenLoaded('user')),
            'payment_system' => PaymentResource::make($this->whenLoaded('paymentSystem')),
            'payable' => $this->whenLoaded('payable'),
        ];
    }
}
