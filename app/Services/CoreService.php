<?php

namespace App\Services;


use App\Helpers\ResponseError;
use App\Models\Language;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

abstract class CoreService
{
    use ApiResponse;
    private $model;
    protected Application|Request|string|array|null $language;

    public function __construct()
    {
        $this->model = app($this->getModelClass());
        $this->language = $this->setLanguage();

    }

    abstract protected function getModelClass();

    protected function model() {
        return clone $this->model;
    }


    /**
     * Set Default status of Model
     * @param int|null $id
     * @param int|null $default
     * @return array
     */
    public function setDefault(int $id = null, int $default = null, $user = null): array
    {
        $model = $this->model()->orderByDesc('id')
            ->when(isset($user), function ($q) use($user) {
            $q->where('user_id', $user);
        })->get();

        // Check Languages list, if first records set it default.
        if (count($model) <= 1) {
            $this->model()->first()->update(['default' => 1, 'active' => 1]);
        }

        // Check and update default language if another language came with DEFAULT
        if ($default) {
            $defaultItem = $this->model()->orderByDesc('id')
                ->when(isset($user), function ($q) use($user) {
                    $q->where('user_id', $user);
                })->whereDefault(1)->first();

            $defaultItem->update(['default' => 0]);

            if ($id) {
                $item = $this->model()->orderByDesc('id')
                    ->when(isset($user), function ($q) use($user) {
                        $q->where('user_id', $user);
                    })->find($id);
                $item->update(['default' => 1, 'active' => 1]);
            }
        }
        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }

    /**
     * Set default Language
     */
    protected function setLanguage()
    {
        return request(
            'lang',
            data_get(Language::where('default', 1)->first(['locale', 'default']), 'locale')
        );
    }

//    public function delete(array $ids): array
//    {
//        $items = $this->model->find($ids);
//
//        if ($items->isNotEmpty()) {
//
//            foreach ($items as $item)
//                $item->delete();
//
//            return ['status' => true, 'code' => ResponseError::NO_ERROR];
//        }
//
//        return ['status' => false, 'code' => ResponseError::ERROR_404];
//    }

}
