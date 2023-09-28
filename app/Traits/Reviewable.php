<?php

namespace App\Traits;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Reviewable
{
    public function addReview($params)
    {
        $review = new Review();
        $review->user_id = auth('sanctum')->id();
        $review->rating = $params->rating;
        $review->comment = $params->comment ?? null;
        $review->assignable_id = $params->assignable_id ?? null;
        $this->reviews()->save($review);
        if (isset($params->images) && count($params->images) > 0){
            $review->uploads($params->images);
            $review->update(['img' => $params->images[0]]);
        }
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function review(): MorphOne
    {
        return $this->morphOne(Review::class, 'reviewable');
    }

}
