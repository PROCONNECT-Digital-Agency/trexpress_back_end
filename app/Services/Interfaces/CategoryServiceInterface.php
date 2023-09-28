<?php

namespace App\Services\Interfaces;

interface CategoryServiceInterface
{
    public function create(array $collection);

    public function update(string $uuid, $collection);

    public function delete(string $uuid);
}
