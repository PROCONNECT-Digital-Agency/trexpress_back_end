<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImportVideo;
use App\Http\Requests\UpdateImportVideoRequest;

class UpdateVideoController extends Controller
{
    public function allVideo()
    {
      return ImportVideo::all();
    }
    
    public function updateVideo(UpdateImportVideoRequest $request, ImportVideo $importVideo)
    {
        
        $request->validate();
        $importVideo['status'] = $request->status;
        
        return $importVideo->update();
        
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
}
