<?php

namespace App\Repositories\Interfaces;

interface ShopRepoInterface
{
    public function shopsList(array $array = []);

    public function shopsPaginate(int $perPage, array $array = []);

    public function shopDetails(string $uuid);

    public function shopsSearch(string $search, $array = []);

    public function shopsByIDs(array $ids = [], $status = null);

}
