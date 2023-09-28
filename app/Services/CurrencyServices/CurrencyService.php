<?php
namespace App\Services\CurrencyServices;


use App\Helpers\ResponseError;
use App\Models\Currency as Model;
use App\Services\CoreService;
use App\Services\Interfaces\CurrencyServiceInterface;

class CurrencyService extends CoreService implements CurrencyServiceInterface
{
    /**
     * CurrencyService Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return Model::class;
    }

    public function create($collection)
    {
        $first = $this->model()->first();
        try {
            $currency = $this->model()->create($this->setCurrencyParams($collection));

            if ($currency){
                // Set Default Currency if this first record on the table/
                $first ?? $this->setCurrencyDefault($currency);

                cache()->forget('currencies-list');
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $currency];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];

        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    public function update($id, $collection)
    {
        try {
            $currency = $this->model()->find($id);
            if ($currency) {
                $currency->update($this->setCurrencyParams($collection));

                cache()->forget('currencies-list');
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $currency];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];

        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    public function delete($id)
    {
        $item = $this->model()->find($id);
        if ($item) {
            if ($item->default) {
                return ['status' => false, 'code' => ResponseError::ERROR_505];
            }
            $item->delete();

            cache()->forget('currencies-list');
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }


    private function setCurrencyParams($collection){
        return [
            'title' => $collection->title,
            'symbol' => $collection->symbol,
            'rate' => $collection->rate ?? 1,
            'active' => $collection->active ?? 0,
        ];
    }

    private function setCurrencyDefault($currency){
        $currency->default = 1;
        $currency->active = 1;
        $currency->save();
    }

}
