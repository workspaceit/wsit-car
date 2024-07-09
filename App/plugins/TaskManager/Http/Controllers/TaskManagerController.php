<?php

namespace Plugins\TaskManager\Http\Controllers;

use App\Http\Controllers\Controller;
// use Plugins\TaskManager\Models\Project;
use Plugins\TaskManager\Models\Tasks;
use App\Models\User;
use DateTime;
use Illuminate\Support\Str;

class TaskManagerController extends Controller
{
    public function board()
    {
        abort_if(!auth()->user()->isSuperAdmin() && !auth()->user()->canManageTaskDashboard(), 403);
        $all_tasks = Tasks::with('status:id,name')
                        ->when(!auth()->user()->isSuperAdmin(), function($q){
                            $q->where('assigner_id',auth()->user()->id);
                        })->get();

        $data['all_tasks_count'] = $all_tasks->count();

        $data['todo_count'] = $all_tasks->filter(function ($task) {
            return $task->status->name == strtolower('To Do');
        })->values()->count();

        $data['in_Progress_count'] = $all_tasks->filter(function ($task) {
            return $task->status->name == strtolower('In Progress');
        })->values()->count();

        $data['in_Review_count'] = $all_tasks->filter(function ($task) {
            return $task->status->name == strtolower('In Review');
        })->values()->count();

        $data['done_count'] = $all_tasks->filter(function ($task) {
            return $task->status->name == strtolower('Done');
        })->values()->count();

        $data['urgent_count'] = $all_tasks->filter(function ($task) {
            $dayLimit = 2;
            $dayLimit = (new DateTime())->modify("+$dayLimit day")->format('Y-m-d');

            return $task->delivery_date <= $dayLimit || $task->status->name == strtolower('Urgent');
        })->values()->count();

        $data['priorities'] = [
            "0" => "Urgent",
            "1" => "Blocker",
            "2" => "Major",
            "3" => "Critical",
            "4" => "Minor",
            "5" => "Trivial"
        ];

        $dealers = (array) auth()->user()->dealers ?? [];
        $data['users'] = User::where("type", User::TYPE_TEAM_MEMBER)
        ->when(!auth()->user()->isSuperAdmin(), function($q) use($dealers){
            $q->where(function ($query) use ($dealers) {
                foreach ($dealers as $dealer) {
                    $query->orWhere('dealers', "LIKE", '%"' . $dealer .'"%');
                }
            });
        })->with('created_task','assigned_task')->get();

        return view("dashboard", $data);
    }
}
