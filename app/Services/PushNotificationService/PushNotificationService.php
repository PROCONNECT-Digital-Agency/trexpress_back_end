<?php

namespace App\Services\PushNotificationService;

ini_set('memory_limit', '4G');
set_time_limit(0);

use App\Models\PushNotification;
use App\Repositories\CoreRepository;
use App\Traits\Notification;
use DB;
use Throwable;

class PushNotificationService extends CoreRepository
{
    use Notification;

    protected function getModelClass(): string
    {
        return PushNotification::class;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function store(array $data): mixed
    {
        return $this->model()->create($data);
    }


    /**
     * @param array $data
     * @param array $userIds
     * @return bool
     */
    public function storeMany(array $data, array $userIds): bool
    {
        $chunks = array_chunk($userIds, 2);

        foreach ($chunks as $chunk) {

            foreach ($chunk as $userId) {

                $newData = is_array(data_get($data, 'data')) ? $data['data'] : [data_get($data, 'data')];

                $data['user_id'] = $userId;
                $data['data']    = $newData;

                try {
                    $this->model()->create($data);
                } catch (Throwable $e) {
                }

            }

        }

        return true;
    }

    /**
     * @param int $id
     * @param int $userId
     * @return PushNotification|null
     */
    public function readAt(int $id, int $userId): ?PushNotification
    {
        $model = $this->model()
            ->with('user')
            ->where('user_id', $userId)
            ->find($id);

        $model?->update([
            'read_at' => now()
        ]);

        return $model;
    }

    /**
     * @param int $userId
     * @return void
     */
    public function readAll(int $userId): void
    {
        dispatch(function () use ($userId) {
            DB::table('push_notifications')
                ->orderBy('id')
                ->where('user_id', $userId)
                ->update([
                    'read_at' => now()
                ]);
        })->afterResponse();
    }

}
