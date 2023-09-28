<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

abstract class SellerBaseController extends Controller
{
    use ApiResponse;
    protected $shop;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('check.shop')
            ->except('shopCreate', 'shopShow', 'shopUpdate');
        if (isset(auth('sanctum')->user()->shop)) {
            $this->shop = auth('sanctum')->user()->shop;
        } elseif (isset(auth('sanctum')->user()->moderatorShop) && ((auth('sanctum')->user())->role == 'moderator' || (auth('sanctum')->user())->role == 'deliveryman')) {
            $this->shop = auth('sanctum')->user()->moderatorShop;
        } else {
            $this->shop = false;
        }
    }

}
