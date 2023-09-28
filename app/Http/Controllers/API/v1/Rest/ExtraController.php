<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExtraGroupResource;
use App\Repositories\ExtraRepository\ExtraGroupRepository;
use Illuminate\Http\Request;

class ExtraController extends RestBaseController
{

    private ExtraGroupRepository $extraGroupRepository;

    public function __construct(ExtraGroupRepository $extraGroupRepository)
    {
        $this->extraGroupRepository = $extraGroupRepository;
    }

    public function extrasGroupList()
    {
        $extraGroups = $this->extraGroupRepository->extraGroupList();

        return ExtraGroupResource::collection($extraGroups);
    }

    public function extrasValueByGroupId()
    {
        return true;
    }
}
