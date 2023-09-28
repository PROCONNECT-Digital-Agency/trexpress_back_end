<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImportVideoRequest;
use App\Http\Requests\UpdateImportVideoRequest;
use App\Models\ImportVideo;
use App\Http\Resources\ImportResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class ImportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $importVideo = ImportVideo::where('admin_id', 1)->get();
        return ImportResource::collection($importVideo);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreImportVideoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreImportVideoRequest $request)
    {
        $requestData = $request->all();
        if ($request->file('name')) {
            
            $destination_path = 'public/images/videos';
            $image = $request->file('name');
            $image_name = time().'.'.$image->getClientOriginalExtension();
            $path = $request->file('name')->storeAs($destination_path, $image_name);
            $requestData['name'] = $image_name;
            $requestData['description'] = $request->description;

        }
        if ($request->file('banner')) {
            
            $destination_path = 'public/images/videos';
            $image = $request->file('banner');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $path = $request->file('banner')->storeAs($destination_path, $imageName);
            $requestData['banner'] = $imageName;

        }
        ImportVideo::create($requestData);
        $responce = [
        "status"=>true,
        "message"=>"successfully"
        ];
        return $responce;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImportVideo  $importVideo
     * @return \Illuminate\Http\Response
     */
    public function show(ImportVideo $importVideo)
    {
        return $importVideo;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateImportVideoRequest  $request
     * @param  \App\Models\ImportVideo  $importVideo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateImportVideoRequest $request, ImportVideo $importVideo)
    {
        $request->validate();
         if ($request->file('name')) {
         
            if(file_exists('app/public/images/videos/'.$importVideo->name)){

            unlink(storage_path('app/public/images/videos/'.$importVideo->name));

        }
            $destination_path = 'public/images/videos';
            $image = $request->file('name');
            $image_name = time().'.'.$image->getClientOriginalExtension();
            $path = $request->file('name')->storeAs($destination_path, $image_name);
            $requestData['name'] = $image_name;
            

        }
        $requestData = $request->all();
        $requestData['description'] = $request->description;
        $importVideo->update($requestData);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImportVideo  $importVideo
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImportVideo $importVideo)
    {
        if(file_exists('app/public/images/videos/'.$importVideo->name)){

            unlink(storage_path('app/public/images/videos/'.$importVideo->name));

        }

            $importVideo->delete();
            return "deleted";
    }
}
