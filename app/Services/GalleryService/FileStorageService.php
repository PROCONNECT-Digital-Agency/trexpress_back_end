<?php

namespace App\Services\GalleryService;

use App\Helpers\ResponseError;
use App\Models\Gallery;
use App\Services\CoreService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService extends CoreService
{

    protected function getModelClass()
    {
        return Gallery::class;
    }

    /**
     * @param $type
     * @param int $perPage
     * @return mixed
     */
    public function getStorageFiles($type, int $perPage): mixed
    {
        return Gallery::where('type', $type)->paginate($perPage);
    }

    public function deleteFileFromStorage($file): array
    {
        $item = $this->model()->where('path',$file)->first();
        if ($item) {
            $item->delete();
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
