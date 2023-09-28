<?php

namespace App\Services\Interfaces;


interface UserServiceInterface
{
    public function create($collection);

    public function update(string $uuid, $collection);
}
