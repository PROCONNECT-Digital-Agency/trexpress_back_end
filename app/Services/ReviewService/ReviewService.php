<?php

namespace App\Services\ReviewService;

use App\Models\Review;

class ReviewService extends \App\Services\CoreService
{

    protected function getModelClass()
    {
        return Review::class;
    }
}
