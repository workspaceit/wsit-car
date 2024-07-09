<?php

namespace Plugins\TaskManager\Http\Controllers;

use App\Models\User;
use App\Models\Dealer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MembersController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|Response|\Illuminate\View\View
     * @throws \Exception
     */
    public function index()
    {
        aclUser('members.listing');
        abort_if(!auth()->user()->isSuperAdmin() && !auth()->user()->canManageTask(), 403);

        if (request()->ajax()) {
            $query = User::where("type", User::TYPE_TEAM_MEMBER)
                ->with("creator:id,username,type", "profile:id,user_id,first_name,last_name")
                ->when(!auth()->user()->isSuperAdmin(), function($q){
                    $q->where("creator_id", auth()->user()->id);
                })->select("users.*");

            return datatables()->of($query)
                ->editColumn('created_at', function ($model) {
                    $date = date_create($model->created_at);
                    return $date = date_format($date, 'Y-m-d');
                })
                ->addColumn('last_active_at', function($user){
                    return $user->last_active_at ?? null;
                })->make(true);
        }

        return view('members.index');
    }

    public function edit($id)
    {
        aclUser('members.modify');
        abort_if(!auth()->user()->isSuperAdmin() && !auth()->user()->canManageTask(), 403);
        $member = User::where("type", User::TYPE_TEAM_MEMBER)->findOrFail($id);

        $config = [];
        if(!empty($member->photo)){
            $expo = explode('/', $member->photo);
            $name = end($expo);
            $config[] = [
                'key' => $member->photo,
                'caption' => $name,
                'downloadUrl' => $member->photo,
                'type' => getFileMimeType($member->photo) == 'pdf' ? 'pdf' : 'image',
                'url' => route('task-manager.members.deleteImage'), // server api to delete the file based on key
            ];
        }

        $member->config = $config;
        $userDealers = auth()->user()->getUserDealers();
        $selectedDealers = Dealer::when(!auth()->user()->isSuperAdmin(), function($q) use($userDealers){
            $q->whereIn("id", $userDealers ?? []);
        })->whereIn("id", $member->dealers ?? [])->where("active", 1)->first()->id ?? null;

        if (empty($selectedDealers)) {
            $selectedDealers = count($userDealers ?? []) === 1 && !auth()->user()->isSuperAdmin() ? $userDealers[0] : null;
        }

        return view('members.form', compact('member', 'selectedDealers', 'userDealers'));
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id)
    {
        aclUser('members.modify');
        abort_if(!auth()->user()->isSuperAdmin() && !auth()->user()->canManageTask(), 403);
        $member = User::where("type", User::TYPE_TEAM_MEMBER)->findOrFail($id);

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $member->id,
            'password' => 'sometimes|nullable|min:6',
            'active' => 'required',
            'type' => 'required',
            'dealers' => 'required|array|min:1|max:1',
            'default_lang' => 'required|numeric',
            'email_notify' => 'nullable',
        ];

        $old_data = $member->getOriginal();
        $photo = $this->request->file('photo');
        $data = $this->request->validate($rules);
        $data['username'] = $this->request->input('email');
        $data['email_notify'] = filter_var($this->request->email_notify, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        if (!empty($photo)) {
            $path = saveImageInWebp('members', $photo->store('', 'members'));
            $member->update(["photo" => env('AWS_URL') . '/' . $path ]);
        }

        $member->update($data);
        $member->syncRoles($data["type"]);
        $member->profile()->updateOrCreate([
            'user_id' => $member->id
        ],['first_name' => request()->first_name, 'last_name' => request()->last_name ]);

        $new_data = $member->getChanges();
        update_user_log('Member Details Updated', 'Member Id: ' . $member->id .
            '<br>Member Name: ' . $member->username, $new_data, $old_data);

        return redirect()->route('task-manager.members.index')->with('success', 'Updated Successfully');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function create()
    {
        aclUser('members.store');
        abort_if(!auth()->user()->isSuperAdmin() && !auth()->user()->canManageTask(), 403);
        $userDealers = auth()->user()->getUserDealers();
        $selectedDealers = count($userDealers ?? []) === 1 && !auth()->user()->isSuperAdmin() ? $userDealers[0] : null;

        return view('members.form', compact('selectedDealers', 'userDealers'));
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'active' => 'nullable',
            'type' => 'required',
            'dealers' => 'required|array|min:1|max:1',
            'default_lang' => 'required|numeric',
            "photo" => "nullable|mimes:jpg,jpeg,jfif,pjpeg,pjp,png,PNG,webp,gif,heic,heif",
            "email_notify" => "nullable",
        ];

        aclUser('members.store');
        abort_if(!auth()->user()->isSuperAdmin() && !auth()->user()->canManageTask(), 403);

        $photo = $this->request->file("photo");
        $data = $this->request->validate($rules);
        $data['username'] = $this->request->input('email');
        $data['email_notify'] = filter_var($this->request->email_notify, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $member = User::create(array_merge($data, ["creator_id" => auth()->user()->id]));
        $member->assignRole($member->type);
        $member->profile()->updateOrCreate([
            'user_id' => $member->id
        ],['first_name' => request()->first_name, 'last_name' => request()->last_name ]);

        if (!empty($photo)) {
            $path = saveImageInWebp('members', $photo->store('', 'members'));
            $member->update(["photo" => env('AWS_URL') . '/' . $path ]);
        }

        update_user_log('New Member Created', 'User Id: ' . $member->id .
            '<br>User Name: ' . $member->username, $member->getOriginal());

        return redirect()->route('task-manager.members.index')->with('success', 'User Saved Successfully');
    }

/**
 * User delete
 *
 * @param mixed $id
 * @return RedirectResponse
 */
    public function destroy($id)
    {
        aclUser('members.destroy');
        abort_if(!auth()->user()->isSuperAdmin() && !auth()->user()->canManageTask(), 403);
        $member = User::where("type", User::TYPE_TEAM_MEMBER)->findOrFail($id);

        if($member->delete()){
            update_user_log('Member Deleted', 'Member Id: ' . $member->id .
            '<br>Member Name: ' . $member->username, $member->getOriginal());
        }

        return redirect()->route('task-manager.members.index')->with('success', 'Member Deleted Successfully');
    }

    public function deleteImage(Request $request)
    {
        aclUser('members.destroy');
        abort_if(!auth()->user()->isSuperAdmin() && !auth()->user()->canManageTask(), 403);
        $member = User::where("type", User::TYPE_TEAM_MEMBER)->findOrFail($request->member);
        deleteImageOrFile('members', last(explode('/', $member->photo)));

        $member->update(["photo" => null]);
        return response()->json('Success');
    }
}
