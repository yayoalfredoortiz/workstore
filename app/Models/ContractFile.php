<?php

namespace App\Models;

use App\Observers\ContractFileObserver;
use App\Traits\IconTrait;

/**
 * App\Models\ContractFile
 *
 * @property int $id
 * @property int $user_id
 * @property int $contract_id
 * @property string $filename
 * @property string $hashname
 * @property string $size
 * @property string $google_url
 * @property string $dropbox_link
 * @property string $external_link_name
 * @property string $external_link
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read \App\Models\Contract $contract
 * @property-read mixed $file_url
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereDropboxLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereExternalLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereGoogleUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereHashname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractFile whereUserId($value)
 * @mixin \Eloquent
 */
class ContractFile extends BaseModel
{
    use IconTrait;

    protected $appends = ['file_url', 'icon'];

    public function getFileUrlAttribute()
    {

        return (!is_null($this->external_link) && $this->external_link != '') ? $this->external_link : asset_url_local_s3('contract-files/' . $this->contract_id . '/' . $this->hashname);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::observe(ContractFileObserver::class);
    }

}
