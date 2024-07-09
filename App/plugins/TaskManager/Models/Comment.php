<?php

namespace Plugins\TaskManager\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Comment extends Model
{
     /**
     * @var string[]
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    /**
     * Get the parent commentable model Task.
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
