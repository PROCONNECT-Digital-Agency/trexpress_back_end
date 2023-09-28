<?php

namespace App\Repositories;

use App\Models\Currency;
use App\Models\Language;

abstract class CoreRepository
{
    protected object $model;
    protected $currency;
    protected $language;
    protected string $updatedDate;

    /**
     * CoreRepository constructor.
     */
    public function __construct()
    {
        $this->model = app($this->getModelClass());
        $this->language = request('lang') ?? null;
        $this->currency = request('currency_id')?? null;
        $this->updatedDate = request('updated_at') ?? '2021-01-01';
    }

    abstract protected function getModelClass();

    protected function model() {
        return clone $this->model;
    }

    /**
     * Set default Currency
     */
    protected function setCurrency() {
        return $this->currency ?? Currency::where('default', 1)->pluck('id')->first();
    }

    /**
     * Set default Language
     */
    protected function setLanguage(): array|string|\Illuminate\Http\Request|\Illuminate\Contracts\Foundation\Application
    {
        return $this->language ?? Language::where('default', 1)->pluck('locale')->first();
    }


}
