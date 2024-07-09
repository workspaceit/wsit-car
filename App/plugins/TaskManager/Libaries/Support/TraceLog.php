<?php

namespace Plugins\TaskManager\Libaries\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Plugins\TaskManager\Models\Tasks;
use Throwable;

class TraceLog
{
    /**
     * @var Task $task
     */
    protected $task = null;

    protected $old_data = [];

    public function __construct(Tasks $task)
    {
        $this->old_data = [];
        $this->task = $task->toArray();
    }

    public function getChangeLogs(Tasks $task)
    {
        $changes = array_except($task->getChanges(), ['updated_at', 'is_seen', 'created_at']);
        if(!empty($changes)){
            $columns = array_keys($changes);
            foreach($columns as $column){
                $this->old_data[$column] = $this->task[$column] ?? null;
            }
        }

        return (object) [
            'modifed_data'=> $changes,
            'old_data'    => $this->old_data,
            'is_modified' => !empty($changes),
            'modified_by' => auth()->user()->id,
            'modified_username' => auth()->user()->username,
            'modified_at' => Carbon::now()->format('Y-m-d h:i:s')
        ];
    }

    public function writeLogs(Tasks $task)
    {
        try{
            $added = false;
            $log = $this->getChangeLogs($task);
            if($log->is_modified){
                $added =  $task->addLogs($log);
            }
        }catch(Throwable $th){
            $added = false;
            Log::error("Error occurs in Task TraceLog : " . $th->getMessage());
        }

        return !! $added;
    }
}
