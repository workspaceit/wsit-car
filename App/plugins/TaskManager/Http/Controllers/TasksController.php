<?php

namespace Plugins\TaskManager\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Plugins\TaskManager\Models\Tasks;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Plugins\TaskManager\Models\Comment;
use App\Models\Status;
use App\Models\User;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Notification;
use Plugins\TaskManager\Http\Requests\TmTaskRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Plugins\TaskManager\Libaries\Support\TraceLog;
use Plugins\TaskManager\Models\TaskHistory;
use Plugins\Products\Models\Product ;
use Plugins\TaskManager\Models\TaskLinkedIssue;
use Throwable;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $creators = Tasks::getTmTaskCreators();
        $assignees = Tasks::getTmTaskAssignableUsers();

        if (request()->ajax()) {
            $query = Tasks::with('user:id,username,type', 'user.profile:user_id,first_name,last_name','assigner:id,username,type', 'assigner.profile:user_id,first_name,last_name', 'status:id,name');
            $query->when(!auth()->user()->isSuperAdmin() && !auth()->user()->isTmMember(), function($q){
                $q->where(function($query){
                    $query->whereIn("created_by", request()->input("creators"))
                    ->orWhere("assigner_id", auth()->user()->id);
                });
            });

            $query->when(auth()->user()->isTmMember(), function($q){
                $q->where("assigner_id", auth()->user()->id)->orWhere("created_by", auth()->user()->id);
            });

            if (request()->filled('assigned_to')) {
                $query->where('assigner_id', request()->get('assigned_to'));
            }

            if (request()->filled('status')) {
                $query->whereHas("status", function($q){
                    $q->where('name', request()->get('status'));
                });
            }

            if (request()->filled('priority')) {
                $query->where('priority', request()->get('priority'));
            }

            $query->when(!empty(request()->input('created_at')) && request()->input('created_at') !== "Created At", function($q){
                $q->WhereDate('created_at', Carbon::parse(request()->input('created_at'))->format('Y-m-d'));
            });

            $query->when(!empty(request()->input('updated_at')) && request()->input('updated_at') !== "Updated At", function($q){
                $q->WhereDate('updated_at', Carbon::parse(request()->input('updated_at'))->format('Y-m-d'));
            });

            if (request()->filled('creator')) {
                $query->where('created_by', request()->get('creator'));
            }

            return DataTables::of($query)
            ->addColumn("task_status", function($task){
                return ucwords($task->status->name ?? "");
            }) ->toJson();
        }

        return view("tasks.index", compact("assignees", "creators"));
    }

    public function fetchIssues(Request $request)
    {
        try {
            $resultCount = 50;
            $page        = Input::get('page');
            $offset      = ($page - 1) * $resultCount;
            $data        = trim(strtolower(Input::get("term")));

            $query = Car::without('block')->select([
                'ID as id', 'dealer_id', 'car_vin as text',
                DB::raw("'Vehicle' as type"),
                DB::raw("'icon-car' as icon"),
                DB::raw("'' as customer_id"),
                DB::raw("CONCAT_WS(' ', car_year, maker, model, car_vin) AS subText")
            ])->when(!auth()->user()->isSuperAdmin(), function($q){
                $q->whereIn("dealer_id", (array) auth()->user()->dealers ?? []);
            })->where('status', 29)->orderBy('text');

            if (!empty($request->term)) {
                $query->whereRaw("LOWER(CONCAT_WS(' ', ID, car_year, maker, model, car_vin)) like '%{$data}%' ");
            }

            $productQuery = Product::select([
                'id','dealer_id',
                DB::raw(" id AS text"),
                DB::raw("'Product' as type"),
                DB::raw("'icon-item' as icon"),
                DB::raw("'' as customer_id"),
                DB::raw("CONCAT_WS(' ', title) AS subText")
            ])->when(!auth()->user()->isSuperAdmin(), function($q){
                $q->whereIn("dealer_id", (array) auth()->user()->dealers ?? []);
            })->where('status_id', 29)->orderBy('text');

            if (!empty($request->term)) {
                $productQuery->whereRaw("LOWER(CONCAT_WS(' ', id, title)) like '%{$data}%' ");
            }

            $leadQuery = Lead::select([
                'id', 'dealer_id', 'car_vin as text',
                DB::raw("'Deal' as type"),
                DB::raw("'icon-shutter' as icon"),
                'customer_id',
                DB::raw("CONCAT_WS(' ', car_year, car_maker, car_model) AS subText")
            ])->when(!auth()->user()->isSuperAdmin(), function($q){
                $q->whereIn("dealer_id", Lead::getFetchableDealers());
            })->whereHas('car', function($q){
                $q->where('status', 29);
            })->whereHas('customer')->orderBy('text');

            if (!empty($request->term)) {
                $leadQuery->where(function($query) use($data){
                    $query->whereRaw("LOWER(CONCAT_WS(' ', id, car_year, car_maker, car_model, car_vin)) like '%{$data}%' ")
                    ->orWhereHas("customer", function($q) use($data){
                        $q->whereRaw("LOWER(CONCAT_WS(' ', first_name, last_name)) like '%{$data}%' ");
                    });
                });
            }

            $contactQuery = Customer::select([
                'id', 'dealer_id',
                DB::raw("CONCAT_WS(' ', first_name, last_name) AS text"),
                DB::raw("'Contact' as type"),
                DB::raw("'icon-user' as icon"),
                'id as customer_id',
                DB::raw("CONCAT_WS(' ', first_name, last_name, mobile) AS subText")
            ])->when(!auth()->user()->isSuperAdmin(), function($q){
                $q->whereIn("dealer_id", Lead::getFetchableDealers());
            })->orderBy('text');

            if (!empty($request->term)) {
                $contactQuery->whereRaw("LOWER(CONCAT_WS(' ', id, first_name, last_name, email, mobile)) like '%{$data}%' ");
            }
            $results   = $query->union($leadQuery)->union($contactQuery)->union($productQuery)->skip($offset)->take($resultCount)->get();
            $morePages = !$results->isEmpty() && count($results) === $resultCount;

            $results->each(function ($result) {
                $result->icon    = $result->icon;
                $result->type    = $result->type;
                $result->link    = $this->getLink($result);
                $result->subText = $this->getSubText($result);
                $result->text    = sprintf('<i class="%s"></i> %s', $result->icon, $result->id);
                $result->id      = $result->type === "Vehicle" ? sprintf("%s-%s-%s", $result->type , $result->id , $result->dealer_id):
                                    sprintf("%s-%s", $result->type , $result->id) ;
            });

            return response()->json(array("results" => $results, "pagination" => array("more" => $morePages)));
        }catch(Throwable $throwable){
            return response()->json(array("results" => collect([]), "pagination" => array("more" => false)));
        }
    }

    public function getSubText($result)
    {
        if($result->type === 'Deal'){
            $customer = Customer::find($result->customer_id);
            $name = trim(sprintf("%s %s", $customer->first_name ?? '', $customer->last_name ?? ''));

            return sprintf("%s - %s (%s)", $name, $result->subText, $result->text);
        }

        return $result->subText;
    }

    public function getLink($result)
    {
        if($result->type === 'Deal'){
            return route('leads.show', $result->id);
        }
        if($result->type === 'Contact'){
            return route('contacts.edit', $result->id);
        }
        if($result->type === 'Vehicle'){
            return route('cars.show', ["dealerId" => $result->dealer_id, "carId" => $result->id]);
        }

        return '';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $linked = null;
        $users = Tasks::getTmTaskAssignableUsers()->toArray();
        abort_if(!in_array(auth()->user()->type, [User::TYPE_SUPER_ADMIN, User::TYPE_ADMIN, User::TYPE_DEALER, User::TYPE_TEAM_MEMBER] ), 403);

        if(!empty(request()->get('contact'))){
            $linked = Customer::select([
                'id', 'dealer_id',
                DB::raw("CONCAT_WS(' ', first_name, last_name) AS text"),
                DB::raw("'Contact' as type"),
                DB::raw("'icon-user' as icon"),
                DB::raw("CONCAT_WS(' ', mobile) AS subText")
            ])->find(request()->get('contact'));
        }

        if(!empty(request()->get('vehicle')) && !empty(request()->get('dealer')) ){
            $linked = Car::select([
                'ID as id', 'dealer_id', 'car_vin as text',
                DB::raw("'Vehicle' as type"),
                DB::raw("'icon-car' as icon"),
                DB::raw("CONCAT_WS(' ', car_year, maker, model, car_vin) AS subText")
            ])->where('ID', request()->get('vehicle'))->where('dealer_id', request()->get('dealer'))->firstOrFail();
        }

        if(!empty(request()->get('deal'))){
            $linked = Lead::select([
                'id', 'dealer_id', 'car_vin as text',
                DB::raw("'Deal' as type"),
                DB::raw("'icon-shutter' as icon"),
                DB::raw("CONCAT_WS(' ', id, car_year, car_maker, car_model, car_vin) AS subText")
            ])->find(request()->get('deal'));
        }

        if(!empty($linked)){
            $linked = (object)[
                "icon"    => $linked->icon,
                "type"    => $linked->type,
                "subText" => $linked->subText,
                "text"    => sprintf('<i class="%s"></i> %s', $linked->icon, $linked->id),
                "id"      => $linked->type === "Vehicle" ? sprintf("%s-%s-%s", $linked->type , $linked->id , $linked->dealer_id):
                            sprintf("%s-%s", $linked->type , $linked->id)
            ];
        }

        $delivered_at = Carbon::now()->addHour(1)->format('Y-m-d h:i:00');
        return view('tasks.create',compact('users', 'linked', 'delivered_at'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TmTaskRequest $request)
    {
        $attachments = $request->file("attachments");
        $task = Tasks::create([
            "title" => $request->title,
            "priority" => $request->priority,
            "created_by" => auth()->user()->id,
            "assigner_id" => $request->assigner_id,
            "description" => $request->description,
            "status_id" => Status::firstOrCreate([ 'name' => strtolower(trim($request->status))])->id,
            "reminder" => !empty($request->reminder) ? Carbon::parse(strtolower($request->reminder)) : null,
            "delivery_date" =>  !empty($request->delivery_date) ? Carbon::parse(strtolower($request->delivery_date)) : null,
        ]);

        if(!empty($request->links)){
            foreach($request->links as $link){
                $data = explode("-", $link);
                $task->linked()->create([
                    "doc_id" => $data[1],
                    "type" => strtolower($data[0]),
                    "dealer_id" => (!empty($data[2]) && is_numeric($data[2])) ? $data[2] : null
                ]);
            }
        }

        $task->sendNotifications(Tasks::TYPE_ASSIGNED);

        if (!empty($attachments)) {
            $links = array();
            foreach($attachments as $attachment){
                $path = $attachment->store('', 's3');
                array_push($links, env('AWS_URL') . '/' . $path);
            }

            $task->update(["files" => implode(",", $links) ]);
        }

        return redirect(route('task-manager.tasks.index'))->with($task ? 'success' : 'error',
        $task ? 'Successfully added the task.' : 'Failed to added the task.');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = Tasks::with('user:id,username,photo,type','status:id,name','comments')
            ->where('id',$id)->firstOrFail();

        $task->load("assigner:id,username,photo,type");
        $changeLog = $task->history->change_log ?? collect();
        $task->created = Carbon::parse($task->created_at)->format('d M, Y h:i A');
        $task->updated = Carbon::parse($task->updated_at)->format('d M, Y h:i A');


        $linkeds = [];
        $task->load('linked');
        foreach($task->linked as $linked){
            $icon = $linked->type === 'vehicle' ? 'icon-car' : ($linked->type === 'contact' ? 'icon-user' : ($linked->type === 'product' ? 'icon-item' : 'icon-shutter'));
            $linkeds[] = (object)[
                "icon"    =>  $icon,
                "link"    => $linked->link,
                "type"    => ucFirst($linked->type),
                "text"    => sprintf('<i class="%s"></i> %s', $icon, $linked->doc_id),
                "link_id" => $linked->id
            ];
        }

        return view('tasks.details', compact('task', 'linkeds', 'changeLog'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $task = Tasks::find($id);
        $users = Tasks::getTmTaskAssignableUsers()->toArray();
        abort_if(!in_array(auth()->user()->type, [User::TYPE_SUPER_ADMIN, User::TYPE_ADMIN, User::TYPE_DEALER, User::TYPE_TEAM_MEMBER]) , 403);
        if(in_array(auth()->user()->type, [User::TYPE_TEAM_MEMBER]) )
        {
            abort_if(auth()->user()->id != $task->created_by , 403);
        }

        $attachment_config = [];
        foreach ($task->files as $file) {
            $expo = explode('/', $file);
            $name = end($expo);
            $attachment_config[] = [
                'key' => $file,
                'caption' => $name,
                'downloadUrl' => $file,
                'type' => getFileMimeType($file) == 'pdf' ? 'pdf' : 'image',
                'url' => route('task-manager.tasks.deleteAttachments'), // server api to delete the file based on key
            ];
        }

        $task->config = $attachment_config;
        $task->reminder = !empty($task->reminder) ? str_replace(" ", "T", $task->reminder) : null;
        $task->delivery_date = !empty($task->delivery_date) ? str_replace(" ", "T", $task->delivery_date) : null;

        $linkeds = [];
        $task->load('linked');
        foreach($task->linked as $linked){
            $icon = $linked->type === 'vehicle' ? 'icon-car' : ($linked->type === 'contact' ? 'icon-user' : ($linked->type === 'product' ? 'icon-item' : 'icon-shutter'));
            $linkeds[] = (object)[
                "icon"    =>  $icon,
                "link"    => $linked->Link,
                "type"    => ucFirst($linked->type),
                "text"    => sprintf('<i class="%s"></i> %s', $icon, $linked->doc_id),
                "id"      => $linked->type === "vehicle" ? sprintf("%s-%s-%s", $linked->type , $linked->doc_id , $linked->dealer_id):
                            sprintf("%s-%s", $linked->type , $linked->doc_id)
            ];
        }

        return view('tasks.edit', compact('users','task', 'linkeds'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TmTaskRequest $request, $id)
    {
        $task        = Tasks::find($id);
        $files       = $task->files;
        $attachments = $request->file('attachments');
        $oldAssigner = $task->assigner_id;
        $taskLog    = new TraceLog($task);

        if (!empty($attachments)) {
            $links = array();
            foreach($attachments as $attachment){
                $path = $attachment->store('', 's3');
                array_push($links, env('AWS_URL') .'/' . $path);
            }

            $files = array_merge($files, $links);
        }

        $update = $task->update([
            "title" => $request->title,
            "priority" => $request->priority,
            "description" => $request->description,
            "assigner_id" => $request->assigner_id,
            "files" => !empty($files) ? implode(",", $files) : null,
            "status_id" => Status::firstOrCreate([ 'name' => strtolower(trim($request->status))])->id,
            "reminder" => !empty($request->reminder) ? Carbon::parse(strtolower($request->reminder))->toDateTimeString() : null,
            "delivery_date" =>  !empty($request->delivery_date) ? Carbon::parse(strtolower($request->delivery_date))->toDateTimeString() : null,
        ]);

        # Write task change logs
        $taskLog->writeLogs($task);
        $task->linked()->delete();

        if(!empty($request->links)){
            foreach($request->links as $link){
                $data = explode("-", $link);
                $task->linked()->create([
                    "doc_id" => $data[1],
                    "type" => strtolower($data[0]),
                    "dealer_id" => (!empty($data[2]) && is_numeric($data[2])) ? $data[2] : null
                ]);
            }
        }

        $field = null;
        $changes = array_except($task->getChanges(), ['updated_at', 'created_at', 'assigner_id', 'is_seen', 'created_at']);
        if(count($changes) === 1){
            $field = array_key_first($changes);
        }

        $task =  Tasks::find($id);
        if ($oldAssigner != $task->assigner_id) {
            $task->sendNotifications(Tasks::TYPE_ASSIGNED);
        }

        $task->sendNotifications((!empty($field) ? Tasks::TYPE_FIELD_EDITED : Tasks::TYPE_UPDATED), $field);
        //return redirect()->back()->with($update ? 'success' : 'error', $update ? 'Task updated successfully.' : 'Failed to update the task.');
        return redirect()->route('task-manager.tasks.index')->with($update ? 'success' : 'error', $update ? 'Task updated successfully.' : 'Failed to update the task.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function ajaxUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                "status" => "required|string",
                "task" => "required|numeric|exists:tm_tasks,id"
            ]);

            $task =  Tasks::find($request->task);
            $taskLog = new TraceLog($task);
            $updated = $task->update([
                "status_id" => Status::firstOrCreate([ 'name' => strtolower(trim($request->status))])->id
            ]);

            # Write task change logs
            $taskLog->writeLogs($task);
            $task->sendNotifications(Tasks::TYPE_STATUS);

            if($updated) {
                return response()->json([
                    'status'  => (bool) $updated,
                    'message' => 'Status successfully updated.',
                ]);
            }

            throw new \ErrorException('Status update failed');
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function modifyTaskReminder(Request $request)
    {
        try {
            $request->validate([
                "reminder" => "required|date_format:Y-m-d H:i:s",
                "task" => "required|numeric|exists:tm_tasks,id"
            ]);

            $task =  Tasks::find($request->task);
            $taskLog = new TraceLog($task);
            $updated = $task->update([ "reminder" => $request->reminder]);

             # Write task change logs
             $taskLog->writeLogs($task);
            if (auth()->user()->id !== $task->assigner_id) {
                $task->sendNotifications(Tasks::TYPE_REMINDER);
            }

            if($updated) {
                return response()->json([
                    'status'  => (bool) $updated,
                    'message' => 'Successfully added reminder datetime.',
                ]);
            }

            throw new \ErrorException('Failed to add task reminder datetime.');
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Tasks::find($id);
        foreach($task->files as $file){
            deleteImageOrFile('tm-tasks', last(explode('/', $file)));
        }

        $deleted = $task->delete();
        return response()->json([
            "status" => $deleted ? 'success' : 'error',
            "message" => $deleted ? "Successfully deleted the task." : "Failed to delete the task."
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function addTaskComment(Request $request)
    {
        try {
            $task    = Tasks::find($request->task_id);
            $comment = $task->saveComment($request->comment);
            $comment->username = auth()->user()->username;
            $comment->created = $comment->created_at->format('d M, Y h:i A');

            $task->sendNotifications(Tasks::TYPE_COMMENT);
            $comment->setAttribute("photo", auth()->user()->photo);

            if($comment) {
                return response()->json([
                    'status'  => 1,
                    'message' => 'Comment added successfully',
                    'comment' => $comment,
                ]);
            }

            throw new \ErrorException('Failed to save comment');

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 0,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function updateTaskComment(Request $request)
    {
        try {
            $comment = Comment::find($request->comment_id);
            $comment = $comment->update([
                'body' => $request->comment
            ]);

            if($comment) {
                return response()->json([
                    'status'  => 1,
                    'message' => 'Comment updated successfully',
                ]);
            }

            throw new \ErrorException('Failed to update comment');

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 0,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function deleteTaskComment(Request $request)
    {
        try {
            $comment = Comment::find($request->comment_id);
            $comment = $comment->delete();

            if($comment) {
                return response()->json([
                    'status'  => 1,
                    'message' => 'Comment deleted successfully',
                ]);
            }

            throw new \ErrorException('Failed to delete comment');

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 0,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function deleteAttachments(Request $request)
    {
        aclUser('tasks.destroy');
        $task = Tasks::findOrFail($request->task);
        $files = $task->files;
        $pos = array_search($request->key, $files ?? []);
        unset($files[$pos]);

        $files = count($files) >= 1 ? implode(',', $files) : null;
        deleteImageOrFile('tm-tasks', last(explode('/', $request->key)));

        $task->update(["files" => $files ?? null]);
        return response()->json('Success');
    }


    /**
     * Destroy multiple tasks with their comments and attachments
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroyMultiple(Request $request)
    {
        // if(auth()->check()){
        //     abort_if(!in_array(auth()->user()->type, [User::TYPE_ADMIN, User::TYPE_SUPER_ADMIN]), 403);
        // }
        $delete = false;
        $ids = json_decode($request->ids);
        $tasks = Tasks::whereIn("id", is_array($ids) ? $ids : [])->get();

        foreach ($tasks as $task) {
            foreach($task->files as $file){
                deleteImageOrFile('tm-tasks', last(explode('/', $file)));
            }
            $delete = $task->delete();
        }

        return redirect()->back()->with($delete ? 'success' : 'error', $delete ? sprintf("Total %s tasks successfully deleted.", count($ids)) : "Failed to delete tasks!");
    }

    public function bulkAssign(Request $request)
    {
        $ids = $request->taskIds;
        $update = Tasks::whereIn("id", is_array($ids) ? $ids : [])->update([
            'assigner_id' => $request->assigner
        ]);

        $tasks = Tasks::whereIn("id", is_array($ids) ? $ids : [])->get();
        foreach ($tasks as $task) {
            $notify = $task->sendNotifications(Tasks::TYPE_ASSIGNED);
        }

        return  response()->json([
            'status'  => $update ? 'success' : 'error',
            'message' => $update ? sprintf("Total %s tasks successfully updated.", count($ids)) : "Failed to update tasks!",
        ]);
    }

    public function destroyLinkedLink(TaskLinkedIssue $link)
    {
        $deleted = $link->delete();
        return response()->json([
            "status" => (bool) $deleted ? 'success' : 'error',
            "message" => $deleted ? "Successfully removed 1 linked link" : "Failed to remove linked link"
        ]);
    }
}
