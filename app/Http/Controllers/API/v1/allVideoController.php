<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ImportResource;
use App\Models\ImportVideo;
class allVideoController extends Controller
{
    public function allVideo()
    {
      $importVideo = ImportVideo::where('status', 1)->get();
      return ImportResource::collection($importVideo);
    }

}
