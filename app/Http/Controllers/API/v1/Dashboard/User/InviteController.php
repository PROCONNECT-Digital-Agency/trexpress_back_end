<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\InviteResource;
use App\Models\Invitation;
use App\Models\Shop;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InviteController extends UserBaseController
{
    private Invitation $model;

    public function __construct(Invitation $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    public function paginate(Request $request)
    {
        $invites = $this->model->filter($request->all())->with([
            'user.roles',
            'user' => function($q) {
                $q->select('id', 'firstname', 'lastname');
            },
            'shop.translation' => fn($q) => $q->actualTranslation($request->lang ?? 'en')
        ])
            ->where('user_id', auth('sanctum')->id())
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15, );
        return InviteResource::collection($invites);
    }


    public function create($shop)
    {
        $shop = Shop::firstWhere('uuid', $shop);
        $invite = $this->model->updateOrCreate(['user_id' => auth('sanctum')->id()],[
            'shop_id' => $shop->id,
        ]);
        return $this->successResponse(__('web.invite_send'), InviteResource::make($invite));
    }
}
