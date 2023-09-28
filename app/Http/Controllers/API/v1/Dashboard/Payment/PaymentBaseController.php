<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

abstract class PaymentBaseController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware(['sanctum.check']);
    }
}
