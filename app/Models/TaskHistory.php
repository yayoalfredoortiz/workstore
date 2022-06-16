<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TaskHistory
 *
 * @property int $id
 * @property int $task_id
 * @property int|null $sub_task_id
 * @property int $user_id
 * @property string $details
 * @property int|null $board_column_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TaskboardColumn|null $boardColumn
 * @property-read mixed $icon
 * @property-read \App\Models\SubTask|null $subTask
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereBoardColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereSubTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskHistory whereUserId($value)
 * @mixin \Eloquent
 */
class TaskHistory extends BaseModel
{
    protected $table = 'task_history';

    protected $with = ['user', 'subTask'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active']);
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function subTask()
    {
        return $this->belongsTo(SubTask::class, 'sub_task_id');
    }

    public function boardColumn()
    {
        return $this->belongsTo(TaskboardColumn::class, 'board_column_id');
    }

}
