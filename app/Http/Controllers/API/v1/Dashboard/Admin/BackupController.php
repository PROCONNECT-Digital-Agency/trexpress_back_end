<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

/**
 * @author  Githubit
 * @email   support@githubit.com
 * @phone   +1 202 340 10-32
 * @site    https://githubit.com/
 */

use App\Http\Controllers\Controller;
use App\Http\Resources\BackupResource;
use App\Models\BackupHistory;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupController extends AdminBaseController
{
    use ApiResponse;
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function download()
    {
        $artisan = Process::fromShellCommandline("cd ". base_path() . " && php artisan backup:run");
        $artisan->run();
        if ($artisan->isSuccessful()) {
            $path = Storage::disk('public')->path('laravel-backup/');
            $files = File::allFiles($path);

            foreach ($files as $item) {
                $title = Str::of($item)->after('laravel-backup/');

                $result = BackupHistory::updateOrCreate([
                    'title' => $title
                ], [
                    'status' => true,
                    'path' => '/storage/laravel-backup/' . $title,
                    'created_by' => auth('sanctum')->id(),
                    'created_at' => now(),
                ]);
            }

            return $this->successResponse('Backup was successfully', [
                'title' => $result->title,
                'path' => '/storage/laravel-backup/' . $result->title,
            ]);
        }

        throw new ProcessFailedException($artisan);
    }

    public function histories(Request $request) {
        $backups = BackupHistory::with('user')->orderBy($request->column ?? 'id', $request->sort ?? 'desc')->paginate($request->perPage ?? 15);
        return BackupResource::collection($backups);
    }
}
