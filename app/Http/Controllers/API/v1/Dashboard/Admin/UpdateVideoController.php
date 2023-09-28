<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImportVideo;
use App\Http\Requests\UpdateImportVideoRequest;
use App\Http\Resources\ImportResource;
use App\Repositories\VideoRepository\VideoRepository;
use App\Services\VideoService\VideoService;

class UpdateVideoController extends AdminBaseController
{


    private VideoRepository $VideoRepository;
    private VideoService $videoService;

    /**
     * @param BlogRepository $VideoRepository
     * @param videoService $videoService
     */
    public function __construct(VideoRepository $VideoRepository, VideoService $videoService)
    {
        parent::__construct();
        $this->VideoRepository = $VideoRepository;
        $this->videoService = $videoService;
    }


    public function allVideo(Request $request)
    {

      $importVideo = $this->VideoRepository->videosPaginate($request->perPage ?? 15, null, $request->all());
        return ImportResource::collection($importVideo);
    }
    
    public function updateVideo(string $uuid, Request $request)
    {
        
        $result = $this->videoService->update($uuid, $request);
        return $result;
        if ($result['status']) {

            return $this->successResponse(__('web.record_successfully_updated'), ImportResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
        /*if ($request->file('name')) 
         {
            if(file_exists('app/public/images/videos/'.$importVideo->name))
            {
              unlink(storage_path('app/public/images/videos/'.$importVideo->name));
            }
            $destination_path = 'public/images/videos';
            $image = $request->file('name');
            $image_name = time().'.'.$image->getClientOriginalExtension();
            $path = $request->file('name')->storeAs($destination_path, $image_name);
            $requestData['name'] = $image_name;
            $requestData['status'] = 1;
        }
        $requestData = $request->all();
        $requestData['status'] = 1;*/

    }

    public function delete_video($id)
    {
        $importVideo = ImportVideo::findOrFail($id);
        if ($importVideo) {
            $importVideo->delete();
            return "data deleted Successfully";
        }else {
            return "404 not found";
        }
    }
}
