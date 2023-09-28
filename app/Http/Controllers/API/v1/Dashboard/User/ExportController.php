<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\Translation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportController extends UserBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function orderExportPDF(int $id)
    {
        $order = Order::with('orderDetails.orderStocks', 'orderDetails.shop')->find($id);
        if ($order) {
            $pdf = PDF::loadView('order-invoice', compact('order'));
            $filename = Str::slug(Carbon::now()->format('Y-m-d h:i:s')). '.order_invoice.pdf';
            $pdf->save(Storage::disk('public')->path('export/invoices/').$filename);

            return $this->successResponse(
                trans('web.file_path', [], request()->lang),
                ['filepath' => 'export/invoices/'.$filename]
            );
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? config('app.locale')));
    }

}
