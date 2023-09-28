<?php

namespace App\Repositories\Interfaces;

interface ProductRepoInterface
{
    public function productsList($active, $array = []);

    public function productsPaginate($perPage, $active = null, $array = []);

    public function productDetails(int $id);

    public function productByUUID(string $uuid);

    public function productsByIDs(array $ids);

    public function productsSearch(string $search, $active = null, $shop = null);
}
