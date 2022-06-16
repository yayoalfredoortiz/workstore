<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadUserNote extends Model
{
    protected $table = 'lead_user_notes';
    protected $fillable = ['user_id', 'lead_note_id'];
}
