<?php

namespace App\Models;

use App\Observers\FileUploadObserver;
use App\Traits\IconTrait;

/**
 * App\Models\ProjectFile
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property string $filename
 * @property string|null $hashname
 * @property string|null $size
 * @property string|null $description
 * @property string|null $google_url
 * @property string|null $dropbox_link
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $external_link_name
 * @property string|null $external_link
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @property-read \App\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectFile whereUserId($value)
 * @mixin \Eloquent
 */
class ProjectFile extends BaseModel
{
    use IconTrait;
    protected $appends = ['file_url', 'icon'];

    protected static function boot()
    {
        parent::boot();
        static::observe(FileUploadObserver::class);
    }

    public function getFileUrlAttribute()
    {
        return (!is_null($this->external_link)) ? $this->external_link : asset_url_local_s3('project-files/' . $this->project_id . '/' . $this->hashname);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

}
