<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportVideo extends Model
{
    use HasFactory, Loadable, Reviewable;

    protected $table = 'videos';

    public $fillable = ['user_id', 'name', 'banner', 'description'];

    const TYPES = [
        'blog' => 1,
        'notification' => 2,
    ];

    public function getTypeAttribute($value)
    {
        foreach (self::TYPES as $index => $type) {
            if ($type === $value){
                return $index;
            }
        }
    }

    public function translations() {
        return $this->hasMany(video_translations::class);
    }

    public function translation() {
        return $this->hasOne(video_translations::class);
    }
}
