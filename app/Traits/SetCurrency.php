<?php

namespace App\Traits;

use App\Models\Currency;

trait SetCurrency
{
    public function currency()
    {
        $currency = isset(request()->currency_id)
            ? Currency::currenciesList()->find(request()->currency_id) : Currency::currenciesList()->where('default', 1)->first();

        return $currency->rate ?? 1;
    }
}
