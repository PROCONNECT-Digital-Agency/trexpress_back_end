<?php

namespace App\Services\Interfaces;

interface ProductServiceInterface
{
    public function create($collection);

    public function update($uuid, $collection);
}
