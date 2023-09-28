<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Repositories\TicketRepository\TicketRepository;
use App\Services\TicketService\TicketService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends AdminBaseController
{
    private TicketRepository $ticketRepository;
    private TicketService $ticketService;

    /**
     * @param TicketRepository $ticketRepository
     * @param TicketService $ticketService
     */
    public function __construct(TicketRepository $ticketRepository, TicketService $ticketService)
    {
        parent::__construct();
        $this->ticketRepository = $ticketRepository;
        $this->ticketService = $ticketService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $categories = $this->ticketRepository->paginate($request->perPage ?? 15, $request->all());
        return TicketResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $result = $this->ticketService->create($request);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), TicketResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $ticket = $this->ticketRepository->ticketDetails($id);
        if ($ticket){
            return $this->successResponse(ResponseError::NO_ERROR, TicketResource::make($ticket));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $result = $this->ticketService->update($id, $request);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), TicketResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStatus(int $id, Request $request)
    {
        $ticket = $this->ticketRepository->ticketDetails($id);
        $status = $request->status ?? $ticket->status;

        if ($ticket) {
            $ticket->update(['status' => $status]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), TicketResource::make($ticket));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function getStatuses()
    {
        return $this->successResponse(ResponseError::NO_ERROR, Ticket::STATUS);
    }
}
