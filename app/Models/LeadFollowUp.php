<?php

namespace App\Models;

use App\Observers\LeadFollowUpObserver;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LeadFollowUp
 *
 * @property int $id
 * @property int $lead_id
 * @property string|null $remark
 * @property \Illuminate\Support\Carbon|null $next_follow_up_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \App\Models\Lead $lead
 * @method static \Illuminate\Database\Eloquent\Builder|LeadFollowUp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadFollowUp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadFollowUp query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadFollowUp whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadFollowUp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadFollowUp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadFollowUp whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadFollowUp whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadFollowUp whereNextFollowUpDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadFollowUp whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadFollowUp whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LeadFollowUp extends BaseModel
{
    protected $table = 'lead_follow_up';
    protected $dates = ['next_follow_up_date', 'created_at'];

    protected static function boot()
    {
        parent::boot();

        static::observe(LeadFollowUpObserver::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

}
