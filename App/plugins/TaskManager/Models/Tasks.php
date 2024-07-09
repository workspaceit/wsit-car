<?php
namespace Plugins\TaskManager\Models;

use App\Jobs\SendTaskAlertMail;
use App\Models\Dealer;
use App\Models\DealerSetting;
use App\Models\Notification;
use App\Models\User;
use App\Models\Status;
use Illuminate\Database\Eloquent\Model;
use Plugins\TaskManager\Libaries\Support\TraceLog;

class Tasks extends Model
{
    const TYPE_UPDATED  = "task_updated";
    const TYPE_ASSIGNED = "task_assigned";
    const TYPE_STATUS   = "task_status";
    const TYPE_COMMENT  = "task_comment";
    const TYPE_REMINDER = "task_reminder";
    const TYPE_FIELD_EDITED = "task_fields";

    protected $table = 'tm_tasks';

    protected $guarded = ["id", "created_at", "updated_at"];


    public function getFilesAttribute($files)
    {
        return !empty($files) ? explode(",", $files) : [];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by')->select("users.id", "user.username", "user.type");
    }

    public function assigner()
    {
        return $this->belongsTo(User::class,'assigner_id')->select("users.id", "user.username", "user.type");
    }

    public function status()
    {
        return $this->belongsTo(Status::class,'status_id');
    }

    /**
     * Get all invitation models.
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Get all of the post's comments.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at', 'desc');
    }

    public function linked()
    {
        return $this->hasMany(TaskLinkedIssue::class, "task_id", "id");
    }

    public function saveComment($comment)
    {
        $comment = $this->comments()->create([
                'user_id'          => auth()->user()->id,
                'body'             => $comment
            ]);

        return $comment;
    }

    public static function getTmTaskAssignableUsers()
    {
        $assignable = collect([]);
        if(auth()->user()->isSuperAdmin())
        {
            $dealerIds =  DealerSetting::where('task_manager', 1)->pluck("dealer_id")->toArray();
            $assignable = $assignable->merge(User::whereIn("type", [User::TYPE_SUPER_ADMIN])->get());
        }
        else
        {
            $dealerIds = (array) auth()->user()->dealers ?? [];
        }
        foreach($dealerIds as $id){
            $users =  User::whereIn("type", [User::TYPE_SUPER_ADMIN, User::TYPE_ADMIN, User::TYPE_DEALER, User::TYPE_TEAM_MEMBER])->where('dealers', 'like', '["' . $id . '",%')->get();
            $merged = ($users->merge(  User::whereIn("type", [User::TYPE_SUPER_ADMIN, User::TYPE_ADMIN, User::TYPE_DEALER, User::TYPE_TEAM_MEMBER])->where('dealers', 'like', '%,"' . $id . '"%')->get()))
            ->merge(  User::whereIn("type", [User::TYPE_SUPER_ADMIN, User::TYPE_ADMIN, User::TYPE_DEALER, User::TYPE_TEAM_MEMBER])->where("dealers", "like", "[\"$id\"]")->get());

            $assignable = $assignable->merge($merged);
        }

        return $assignable ?? collect([]);

    }

    public static function getTmTaskCreators()
    {
        $creators = collect([]);
        $dealerIds =[];

        if(auth()->user()->isSuperAdmin())
        {
            $dealerIds =  DealerSetting::where('task_manager', 1)->pluck("dealer_id")->toArray();
        }
        else{
            $dealerIds = (array) auth()->user()->dealers ?? [];
        }

        if(!empty($dealerIds)){
            foreach($dealerIds as $id){
                $users =  User::whereIn("type", [User::TYPE_SUPER_ADMIN, User::TYPE_ADMIN, User::TYPE_DEALER, User::TYPE_TEAM_MEMBER])->where('dealers', 'like', '["' . $id . '",%')->get();
                $merged = ($users->merge(  User::whereIn("type", [User::TYPE_SUPER_ADMIN, User::TYPE_ADMIN, User::TYPE_DEALER, User::TYPE_TEAM_MEMBER])
                ->where('dealers', 'like', '%,"' . $id . '"%')->get()))
                ->merge(  User::whereIn("type", [User::TYPE_SUPER_ADMIN, User::TYPE_ADMIN, User::TYPE_DEALER, User::TYPE_TEAM_MEMBER])
                ->where("dealers", "like", "[\"$id\"]")->get())
                ->merge(  User::whereIn("type", [User::TYPE_SUPER_ADMIN])->get());

                $creators = $creators->merge($merged);
            }
        }

        return $creators ?? collect([]);
    }

    public function sendNotifications($taskType, $field = null, $userIds = [])
    {
        if (auth()->user()->id != $this->assigner_id && !empty($this->assigner_id)) {
            array_push($userIds, $this->assigner_id);
        }
        if (auth()->user()->id != $this->created_by && !empty($this->created_by)) {
            array_push($userIds, $this->created_by);
        }

        $userIds = array_unique($userIds);
        foreach ($userIds as $userId) {
            $lang = !empty($field) ? sprintf('task.notifications.%s.%s', $taskType, $field) : 'task.notifications.'.$taskType;
            if($taskType === self::TYPE_STATUS){
                $lang = sprintf("%s::%s", $lang, strtolower(trim(request()->status)));
            }

            $this->notifications()->create([
                'seen_by_owner'         => 1,
                'notified_to'           => $userId,
                'type'                  => $taskType,
                'created_by'            => auth()->user()->id,
                'lang_notification_key' => $lang
            ]);

            self::purgeNotifications($userId);
            $mailable = User::where('id', $userId)->where('email_notify', true)->first();
            if(!empty($mailable)){
                dispatch(new SendTaskAlertMail($mailable->id, $this->id, $taskType, $lang, auth()->user()));
            }
        }
    }

    public static function purgeNotifications($notified_to = null, $skip = 30, $take = 1000)
    {
        $notifications = Notification::where("notifiable_type", self::class)
            ->where('notified_to', $notified_to)->latest()->skip($skip)->take($take)->pluck("id")->toArray();

        return Notification::whereIn("id",  $notifications)->delete();
    }

    public function history()
    {
        return $this->hasOne(History::class, "task_id", "id");
    }

    public function addLogs(object $log)
    {
        $logs = (array) ($this->history->logs ?? []);
        array_push($logs, $log);

        return $this->history()->updateOrCreate([
            'task_id' => $this->id,
        ],[ 'logs' => $logs]);
    }
}
