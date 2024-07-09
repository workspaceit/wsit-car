<?php

namespace Plugins\TaskManager\Observers;

use App\Models\Notification;
use Plugins\TaskManager\Models\Comment;
use Plugins\TaskManager\Models\Tasks;

class TmTasksObserver
{
    /**
     * Handle the tasks "created" event.
     *
     * @param  Tasks  $task
     * @return void
     */
    public function created(Tasks $task)
    {
        //
    }

    /**
     * Handle the tasks "updated" event.
     *
     * @param  Tasks  $task
     * @return void
     */
    public function updated(Tasks $task)
    {
        //
    }

    /**
     * Handle the tasks "deleted" event.
     *
     * @param  Tasks  $task
     * @return void
     */
    public function deleted(Tasks $task)
    {
       Notification::where("notifiable_type", Tasks::class)->where("notifiable_id", $task->id)->delete();
       Comment::where("commentable_type", Tasks::class)->where("commentable_id", $task->id)->delete();
    }

    /**
     * Handle the tasks "restored" event.
     *
     * @param Tasks  $task
     * @return void
     */
    public function restored(Tasks $task)
    {
        //
    }

    /**
     * Handle the tasks "force deleted" event.
     *
     * @param Tasks  $tasks
     * @return void
     */
    public function forceDeleted(Tasks $task)
    {
        //
    }
}
