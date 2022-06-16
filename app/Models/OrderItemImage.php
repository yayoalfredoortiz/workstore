<?php

namespace App\Models;

use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Model;

class OrderItemImage extends Model
{

    use IconTrait;

    protected $appends = ['file_url', 'icon'];

    Protected $fillable = ['order_item_id', 'external_link'];

    public function getFileUrlAttribute()
    {
        return $this->external_link;
    }

}
