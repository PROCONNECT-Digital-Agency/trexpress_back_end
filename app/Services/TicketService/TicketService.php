<?php

namespace App\Services\TicketService;

use App\Helpers\ResponseError;
use App\Models\Ticket;

class TicketService extends \App\Services\CoreService
{
    protected function getModelClass()
    {
        return Ticket::class;
    }

    public function create($collection): array
    {
        try {
            $ticket = $this->model()->create($this->setTicketParams($collection));
            if ($ticket) {
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $ticket];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501 ];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    public function update($id, $collection): array
    {
        $ticket = $this->model()->find($id);
        if ($ticket) {
            try {
                $result = $ticket->update($this->setTicketParams($collection));
                if ($result) {
                    return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $ticket];
                }
                return ['status' => false, 'code' => ResponseError::ERROR_502 ];
            } catch (\Exception $e) {
                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
            }
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function setTicketParams($collection): array
    {
        return [
            'created_by' => $collection->created_by,
            'user_id' => $collection->user_id ?? null,
            'order_id' => $collection->order_id ?? null,
            'parent_id' => $collection->parent_id ?? 0,
            'type' => $collection->type ?? 'question',
            'subject' => $collection->subject,
            'content' => $collection->content,
        ];
    }
}
