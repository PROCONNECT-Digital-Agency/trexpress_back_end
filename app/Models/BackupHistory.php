<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\BackupHistory
 *
 * @property int $id
 * @property string $title
 * @property int $status
 * @property string|null $path
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property-read User $user
 * @method static Builder|BackupHistory newModelQuery()
 * @method static Builder|BackupHistory newQuery()
 * @method static Builder|BackupHistory query()
 * @method static Builder|BackupHistory whereCreatedAt($value)
 * @method static Builder|BackupHistory whereCreatedBy($value)
 * @method static Builder|BackupHistory whereId($value)
 * @method static Builder|BackupHistory wherePath($value)
 * @method static Builder|BackupHistory whereStatus($value)
 * @method static Builder|BackupHistory whereTitle($value)
 * @mixin Eloquent
 */
class BackupHistory extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function getDates()
    {
        return ['created_at'];
    }

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }

}
