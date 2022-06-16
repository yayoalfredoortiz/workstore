<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\KnowledgeBaseCategories
 *
 * @property int $id
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KnowledgeBase[] $knowledgebase
 * @property-read int|null $knowledgebase_count
 */

class KnowledgeBaseCategories extends Model
{
    use HasFactory;
    protected $table = 'knowledge_categories';

    public function knowledgebase()
    {
        return $this->hasMany(KnowledgeBase::class, 'category_id');
    }

}
