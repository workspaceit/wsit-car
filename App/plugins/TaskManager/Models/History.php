<?php

namespace Plugins\TaskManager\Models;

use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = "task_histories";

    protected $guarded = ["id", "created_at", "updated_at"];

    protected $appends = ['change_log'];

    public function setLogsAttribute($logs)
    {
        $this->attributes['logs'] = !empty($logs) ? json_encode($logs) : null;
    }
    public function getLogsAttribute($logs)
    {
        return !empty($logs) ? json_decode($logs) : [];
    }

    public function task()
    {
        return $this->belongsTo(Tasks::class, "task_id", "id");
    }

    public function getChangeLogAttribute()
    {
        $data = collect();
        foreach($this->logs as $log){
            $modified = $this->format($log->modifed_data);
            $title = count($modified) > 1 ? sprintf("%s updated the task",  ucwords($log->modified_username)) :
                sprintf("%s updated the <strong>%s</strong>",  ucwords($log->modified_username), ucwords(array_key_first($modified)));

            $data->push((object) [
                'title' => $title,
                "modifed_data" => $modified,
                "old_data" => $this->format($log->old_data),
                "modified_at" => Carbon::parse($log->modified_at)->format('d M, Y h:i A'),
                "modified_username"=> ucwords($log->modified_username),
                "modified_by" => User::where("id", $log->modified_by)->select("id", "username", "email", "photo", "type", "dealers")->first(),
            ]);
        }

        return $data;
    }

    public function format($attributes, $data = [])
    {
        foreach($attributes as $key => $attribute){
            if($key === 'status_id'){
                $data['status'] = ucwords(Status::where("id", $attribute)->first()->name ?? "");
            }else if($key === 'assigner_id'){
                $data['assigner'] = ucwords(User::where("id", $attribute)->first()->username ?? "");
            }else if($key === 'priority'){
                $data['priority'] = $this->mapTaskPriority($attribute);
            }else if($key === 'delivery_date'){
                $data['delivery date'] = $attribute;
            }else if($key === 'files'){
                $data['attachments'] = $attribute;
            }else{
                $data[$key] = $attribute;
            }
        }

        return $data;
    }

    public function mapTaskPriority(int $priority = 0)
    {
        $priorities = [
            "0" => "<img class='priority' src='/backend_assets/assets/img/0.svg' width='16' height='16'> Urgent",
            "1" => "<img class='priority' src='/backend_assets/assets/img/1.svg' width='16' height='16'> Blocker",
            "2" => "<img class='priority' src='/backend_assets/assets/img/2.svg' width='16' height='16'> Major",
            "3" => "<img class='priority' src='/backend_assets/assets/img/3.svg' width='16' height='16'> Critical",
            "4" => "<img class='priority' src='/backend_assets/assets/img/4.svg' width='16' height='16'> Minor",
            "5" => "<img class='priority' src='/backend_assets/assets/img/5.svg' width='16' height='16'> Trivial"
        ];

        return $priorities[$priority] ?? "NULL";
    }
}
