<?php

namespace App\Models;

use App\Observers\TaskFileObserver;
use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TaskFile
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property string $filename
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $dropbox_link
 * @property string|null $external_link
 * @property string|null $external_link_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskFile whereUserId($value)
 * @mixin \Eloquent
 */
class TaskFile extends BaseModel
{
    use IconTrait;
    protected $appends = ['file_url', 'icon'];

    protected static function boot()
    {
        parent::boot();
        static::observe(TaskFileObserver::class);
    }

    public function getFileUrlAttribute()
    {
        return (!is_null($this->external_link)) ? $this->external_link : asset_url_local_s3('task-files/' . $this->task_id . '/' . $this->hashname);
    }

}
