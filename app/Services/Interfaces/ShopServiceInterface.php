<?php

namespace App\Services\Interfaces;

interface ShopServiceInterface
{
    public function create($collection);

    public function update(string $uuid, $collection);

    public function delete(string $uuid);

}
