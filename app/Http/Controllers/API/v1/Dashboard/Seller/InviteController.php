<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\InviteResource;
use App\Models\Invitation;
use App\Models\Shop;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InviteController extends SellerBaseController
{
    private Invitation $model;

    public function __construct(Invitation $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    public function paginate(Request $request)
    {
        if ($this->shop) {
            $invites = $this->model->filter($request->all())->with([
                'user.roles',
                'user' => function($q) {
                    $q->select('id', 'firstname', 'lastname');
                },
                'shop.translation' => fn($q) => $q->actualTranslation($request->lang ?? 'en')
            ])
                ->where('shop_id', $this->shop->id)->orderBy($request->column ?? 'id', $request->sort ?? 'desc')->paginate($request->perPage ?? 15, );
            return InviteResource::collection($invites);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function changeStatus(int $id)
    {
        if ($this->shop) {
            $invite = $this->model->firstWhere(['id' => $id, 'shop_id' => $this->shop->id]);
            if ($invite){
                if (isset(request()->role) && (request()->role == 'moderator' || request()->role == 'deliveryman')) {
                    $invite->update(['status' => Invitation::STATUS['excepted'], 'role' => request()->role]);
                    $invite->user->syncRoles(request()->role);
                } else {
                    $invite->update(['status' => Invitation::STATUS['rejected'], 'role' => 'user']);
                    $invite->user->syncRoles('user');
                }
                return InviteResource::make($invite);
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], \request()->lang),
                Response::HTTP_NOT_FOUND
            );
        }  else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], \request()->lang),
                Response::HTTP_FORBIDDEN
            );
        }
    }

}
