<?php

namespace App\Http\Controllers;

use App\Mail\IssueCreateEmail;
use App\Models\Dealer;
use App\Models\Issue;
use App\Models\IssueFile;
use App\Models\User;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleUserAgent\UserAgent;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Attachment\AttachmentService;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue\IssueField;
use JiraRestApi\JiraException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class IssueController extends Controller
{
    protected $jiraCredentials;

    public function __construct()
    {
        $this->jiraCredentials = new ArrayConfiguration(
            array(
                'jiraHost' => env('JIRA_HOST'),
                'jiraUser' => env('JIRA_USER'),
                'jiraPassword' => env('JIRA_PASS'),
            )
        );
    }

    public function index()
    {
        abort_if(auth()->user()->isIndividual(), 403);
        aclUser('supports.listing');

        if (request()->ajax()) {
            $issues = Issue::with('files', 'dealers', 'created_by', 'status:id,name')
                ->where('created_by', Auth::id());

            return datatables()->of($issues)->make();
        }

        return view('issues.index');
    }

    public function create()
    {
        aclUser('supports.store');

        $dealersQuery = Dealer::where('active', true);
        if (Auth::user()->type === User::TYPE_DEALER || Auth::user()->type === User::TYPE_ADMIN) {
            $dealersQuery->whereIn('id', Auth::user()->dealers);
        }
        $dealers = $dealersQuery->get();

        return view('issues.form', compact('dealers'));
    }

    public function store(Request $request)
    {
        aclUser('supports.store');

        $rules = [];
        $rules['name'] = 'required';
        $rules['email'] = 'required|email';
        $rules['phone'] = 'required';
        $rules['type'] = 'required';
        $rules['description'] = 'required';
        if ($request->type == 'Dealer Feed'){
            $rules['dealer_id'] = 'required';
            $dealer_id = $request->dealer_id;
            $dealer_name = ($dealer_id == 0) ? 'All':Dealer::findOrFail($dealer_id)->name;
        }

        $request->validate($rules);
        date_default_timezone_set("Etc/UCT");
        $ticket_no = ucfirst(substr(Auth::user()->username, 0, 1)) . date('dmYhi');

        try {
            $additional_details = $this->get_additional_user_details();

            // Save issue to jira
            $issueField = new IssueField();
            $issueField->setProjectKey(env('JIRA_PROJECT_KEY'))
                        ->setAssigneeAccountId(env('JIRA_ASSIGNEE_ID'))
                        ->setReporterAccountId(env('JIRA_REPORTER_ID'))
                        ->setSummary("User submitted task (Ticket no: ".$ticket_no.")")
                        ->setIssueType("Task")
                        ->setDescription('Name: '.$request->name.'\\\\'.'Email: [mailto:'.$request->email.']'.
                        '\\\\'.'Phone Number: '.$request->phone.'\\\\'.'Type: '.$request->type.
                        ($request->type == 'Dealer Feed' ? '\\\\Dealer: '.$dealer_name:'').'\\\\ \\\\'.
                        $additional_details.'\\\\ \\\\'.$request->description);

            $issueService = new IssueService($this->jiraCredentials);
            $status = Status::firstOrCreate([ 'name' => strtolower(trim('new'))]);
            $ret = $issueService->create($issueField);

            // Save issue to db
            $issue = new Issue();
            $issue->ticket_no = $ticket_no;
            $issue->issue_key = $ret->key;
            $issue->name = $request->name;
            $issue->email = $request->email;
            $issue->phone = $request->phone;
            $issue->type = $request->type;
            $issue->dealer_id = ($request->type == 'Dealer Feed' ? $request->dealer_id : null);
            $issue->description = $request->description;
            $issue->status = $status->id;
            $issue->created_by = Auth::id();
            $issue->save();

            $temp_photo_arr = [];
            // Check if a image has been uploaded
            if ($files = $request->file('photos')) {
                foreach ($files as $file) {
                    $extension  = $file->getClientOriginalExtension();
                    $path       = $file->store('', 'dealer-issues');
                    //$public_path = public_path('images/issues');
                    //$temp_photo_arr[] = $public_path.'/'.$path;
                    //dispatch(new \App\Jobs\ImageOptimize($path, 'issues'))->onQueue('default');

                    if ($extension == 'heic' || $extension == 'HEIC') { //if heic convert it to jpg

                        $path = convertHeicToJpg('issues', $path);
                    }

                    $new_file_name = saveImageInWebp('issues', $path);
                    $temp_photo_arr[] = public_path('images/issues').'/'.$new_file_name;

                    $issue_file = new IssueFile();
                    $issue_file->issue_id = $issue->id;
                    $issue_file->file_url = $new_file_name;
                    $issue_file->save();
                }
            }

            //upload image to jira as attachment
            if (!empty($temp_photo_arr)) {
                $ret2 = $issueService->addAttachments($ret->key, $temp_photo_arr);
                foreach ($ret2 as $attachment) {
                    $issue_file = IssueFile::where('file_url', $attachment->filename)->first();
                    $issue_file->attachment_id = $attachment->id;
                    $issue_file->save();
                }
            }

            //send mail
            $mail_info['ticket_no'] = $issue->ticket_no;
            $mail_info['to'] = Auth::user()->email;

            \Mail::send((new IssueCreateEmail($mail_info)));

            return redirect()->route('supports')->with('success', __('ui.issue').' '.__('ui.form.created').' '.__('ui.successfully').'!');

        } catch (JiraException $e) {
            $msg = explode("\n", $e->getMessage());

            Log::error("Error occurs in IssueController : ". $e->getMessage());
            return redirect()->back()->with("error", explode(",", $msg[0])[0]);
        }
    }

    public function edit($id)
    {
        aclUser('supports.modify');

        $issue = Issue::findOrFail($id);
        if (auth()->user()->type != 'super_admin' && $issue->created_by != Auth::user()->id ) abort(Response::HTTP_FORBIDDEN, 'You don\'t have access on this page.');

        $issue_images = IssueFile::where('issue_id', $id)->pluck('file_url');
        $dealersQuery = Dealer::where('active', true);
        if (Auth::user()->type === User::TYPE_DEALER || Auth::user()->type === User::TYPE_ADMIN) {
            $dealersQuery->whereIn('id', Auth::user()->dealers);
        }
        $dealers = $dealersQuery->get();

        $photos = [];
        $photo_config = [];
        $temp_photo_config = [];
        foreach ($issue_images as $issue_image) {
            $photos[] = env('AWS_URL').'/'.$issue_image;
            $temp_photo_config[] = [
                'key' => env('AWS_URL').'/'.$issue_image,
                // 'caption' => $issue_image,
                'downloadUrl' => env('AWS_URL').'/'.$issue_image, // the url to download the file
                'url' => '/supports/deleteImage', // server api to delete the file based on key
                'extra' => ['issue_id' => $id]
            ];
        }
        $issue->photos = $photos;
        $issue->photo_config = $temp_photo_config;
        // dump($issue);
        // dd('end');

        return view('issues.edit', compact('issue', 'dealers'));
    }

    public function update(Request $request)
    {
        aclUser('supports.modify');

        $rules = [];
        $rules['name'] = 'required';
        $rules['email'] = 'required|email';
        $rules['phone'] = 'required';
        $rules['type'] = 'required';
        $rules['description'] = 'required';

        if ($request->type == 'Dealer Feed'){
            $rules['dealer_id'] = 'required';
            $dealer_id = $request->dealer_id;
            $dealer_name = ($dealer_id == 0) ? 'All':Dealer::findOrFail($dealer_id)->name;
        }
        $request->validate($rules);

        try {
            $additional_details = $this->get_additional_user_details();
            $status = Status::firstOrCreate([ 'name' => strtolower(trim('new'))]);

            // Update issue to db
            $issue_id = $request->issue_id;
            $issue = Issue::findOrFail($issue_id);
            $issue->name = $request->name;
            $issue->email = $request->email;
            $issue->phone = $request->phone;
            $issue->type = $request->type;
            $issue->dealer_id = ($request->type == 'Dealer Feed' ? $request->dealer_id : null);
            $issue->description = $request->description;
            $issue->status = $status->id;
            $issue->created_by = Auth::id();
            $issue->save();

            // Update issue in jira
            $issueField = new IssueField();
            $issueField->setProjectKey(env('JIRA_PROJECT_KEY'))
                    ->setIssueType("Task")
                    ->setDescription('Name: '.$request->name.'\\\\'.'Email: [mailto:'.$request->email.']'.
                    '\\\\'.'Phone Number: '.$request->phone.'\\\\'.'Type: '.$request->type.
                    ($request->type == 'Dealer Feed' ? '\\\\Dealer: '.$dealer_name:'').'\\\\ \\\\'.
                    $additional_details.'\\\\ \\\\'.$request->description);
            $issueService = new IssueService($this->jiraCredentials);
            $ret = $issueService->update($issue->issue_key, $issueField);

            $temp_photo_arr = [];
            // Check if a image has been uploaded
            if ($files = $request->file('photos')) {
                foreach ($files as $file) {
                    $extension  = $file->getClientOriginalExtension();
                    $path       = $file->store('', 'dealer-issues');
                    //$public_path = public_path('images/issues/'.$path);
                    //$temp_photo_arr[] = $public_path;
                    //dispatch(new \App\Jobs\ImageOptimize($path, 'issues'))->onQueue('default');

                    if ($extension == 'heic' || $extension == 'HEIC') { //if heic convert it to jpg

                        $path = convertHeicToJpg('issues', $path);
                    }

                    $new_file_name = saveImageInWebp('issues', $path);
                    $temp_photo_arr[] = public_path('images/issues').'/'.$new_file_name;

                    $issue_file = new IssueFile();
                    $issue_file->issue_id = $issue->id;
                    $issue_file->file_url = $new_file_name;
                    $issue_file->save();
                }
            }
            //upload image to jira as attachment
            if (!empty($temp_photo_arr)) {
                $ret2 = $issueService->addAttachments($issue->issue_key, $temp_photo_arr);
                foreach ($ret2 as $attachment) {
                    $issue_file = IssueFile::where('file_url', $attachment->filename)->first();
                    $issue_file->attachment_id = $attachment->id;
                    $issue_file->save();
                }
            }
            return redirect()->route('supports')->with('success', __('ui.issue').' '.__('ui.updated').' '.__('ui.successfully').'!');

        } catch (Throwable $e) {
            $msg = explode("\n", $e->getMessage());

            Log::error("Error occurs in IssueController : ". $e->getMessage());
            return redirect()->back()->with("error", explode(",", $msg[0])[0]);
        }

    }

    public function destroy(Request $request)
    {
        aclUser('supports.destroy');

        try {
            $id = $request->issue_id;
            $issue = Issue::findOrFail($id);

            if (auth()->user()->type != 'super_admin' && $issue->created_by != Auth::user()->id ) abort(Response::HTTP_FORBIDDEN, 'You don\'t have access on this page.');

            $issue_key = $issue->issue_key;
            $issue_files = IssueFile::where('issue_id', $id)->get();
            foreach ($issue_files as $issue_file) {
                deleteImageOrFile('dealer-issues', $issue_file->file_url);
            }
            IssueFile::where('issue_id', $id)->forceDelete();
            $issue->delete();

            //delete from jira
            $issueService = new IssueService($this->jiraCredentials);
            $ret = $issueService->deleteIssue($issue_key);

            return redirect()->route('supports')->with('success', __('ui.issue').' '.__('ui.deleted').' '.__('ui.successfully').'!');

        } catch (JiraException $e) {
            $this->assertTrue(FALSE, "Remove Issue Failed : " . $e->getMessage());
        }
    }

    public function deleteImage(Request $request)
    {
        $file_name = last(explode('/', $request->key));
        if(Storage::disk('s3')->exists( $file_name) ){
            try {
                $issue_file = IssueFile::where('issue_id', $request->issue_id)->where('file_url', $file_name)->first();

                //delete from jira
                $atts = new AttachmentService($this->jiraCredentials);
                $atts->remove($issue_file->attachment_id);

                //delete from DB
                $issue_file->forceDelete();

                //delete from S3 storage
                deleteImageOrFile('dealer-issues', $file_name);

            } catch (JiraRestApi\JiraException $e) {
                print("Error Occured! " . $e->getMessage());
            }
        }
        return response()->json( 'Success' );
    }

    private function get_additional_user_details()
    {
        $additional_details = 'Time: '.now()->setTimezone('EST')->format('Y-m-d H:i:s').'\\\\'.
                                'User: '.Auth::user()->username.'\\\\'.
                                'IP: ['.request()->ip().'|https://whois.domaintools.com/'.request()->ip().']';
        $userAgent = null;
        try {
            $userAgent = (new UserAgent())->getInfo();
        }
        catch(Exception $e) {
            Log::info(request()->header('User-Agent'));
            Log::error($e);
        }
        if ($userAgent) {
            $additional_details .= '\\\\'.'Device: '.$userAgent['device'].'\\\\'.
                                    'OS: '.$userAgent['os'].'\\\\'.
                                    'Browser: '.$userAgent['browser'].' '.$userAgent['version'];
        }

        return $additional_details;
    }
}
