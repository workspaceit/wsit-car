<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Dealer;
use App\Models\Issue;
use App\Models\IssueFile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JiraRestApi\JiraException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JiraRestApi\Attachment\AttachmentService;
use JiraRestApi\Issue\IssueField as JiraField;
use JiraRestApi\Issue\IssueService as JiraService;
use JiraRestApi\Configuration\ArrayConfiguration as JiraArrayConfiguration;

class IssueApiController extends Controller
{
    protected $jiraCredentials;

    private $validationRules = [
        'name' => 'required',
        'email' => 'required|email',
        'phone' => 'required',
        'type' => 'required',
        'dealer_id' => 'required_if:type,Dealer Feed',
        'description' => 'required',
        'photos.*' => 'mimes:jpeg,jpg,png,webp'
    ];

    public function __construct()
    {
        $this->middleware('api.auth');

        $this->jiraCredentials = new JiraArrayConfiguration(
            [
                'jiraHost' => env('JIRA_HOST'),
                'jiraUser' => env('JIRA_USER'),
                'jiraPassword' => env('JIRA_PASS'),
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $jiraService = new JiraService($this->jiraCredentials);

        $page = (int) $request->get('page') ?: 1;
        $perPage = (int) $request->input('perpage') ?: 10;
        $skip = ($page - 1) * $perPage;

        $issuesQuery = Issue::select('id', 'ticket_no', 'issue_key', 'status')
            ->where('created_by', Auth::guard('api')->user()->getAuthIdentifier());

        if ($request->filled('filter') && $request->input('filter')['query']) {
            $issuesQuery->where('ticket_no', 'like', '%'. $request->input('filter')['query'] .'%');
        }

        $totalItems = $issuesQuery->count();

        $issues = $issuesQuery
            ->skip($skip)
            ->take($perPage)
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(function ($issue) use ($jiraService) {
                $jiraTicket = $jiraService->get($issue->issue_key);

                if ($issue->status()->name !== $jiraTicket->fields->status->name) {
                    $status = Status::firstOrCreate([ 'name' => strtolower(trim($jiraTicket->fields->status->name))]);
                    $issue->status = $status->id;
                    $issue->save();
                }
            })
            ->transform(function ($issue) {
                $issue->links = [
                    'self' => route('api.v1.issues.show', $issue->id),
                ];

                return $issue;
            });

        $lastPage = ceil($totalItems / $perPage);

        $previousPage = !$lastPage || $page <= 1 ? null : sprintf(url($request->path()) .'?page=%d', $page - 1);
        $nextPage = !$lastPage || $page >= $lastPage ? null : sprintf(url($request->path()) .'?page=%d', $page + 1);

        return response()->json([
            'status' => 'success',
            'data' => [
                'issues' => $issues,
                'totalItems' => $totalItems,
                'itemsPerPage' => $perPage,
                'currentPage' => $page,
                'lastPage' => $lastPage,
                'links' => [
                    'previousPage' => $previousPage,
                    'nextPage' => $nextPage
                ]
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        if (Auth::guard('api')->user()->cant('create', Issue::class)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logged in user can\'t do that action.',
                'data' => [
                    'issue' => null
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), $this->validationRules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not valid.',
                'data' => [
                    'errors' => $validator->messages()->toArray(),
                    'issue' => null
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        date_default_timezone_set("Etc/UCT");
        $ticketNo = ucfirst(substr(Auth::guard('api')->user()->username, 0, 1)) . date('dmYhi');

        try {
            $jiraField = new JiraField();
            $jiraService = new JiraService($this->jiraCredentials);

            /** Description **/
            $description = 'User: '. Auth::guard('api')->user()->username ."\n";
            $description .= 'Name: '. $request->input('name') ."\n";
            $description .= 'Email: [mailto:'. $request->input('email') ."]\n";
            $description .= 'Phone Number: '.$request->input('phone') ."\n";
            $description .= 'Type: '. $request->input('type') ."\n";

            if ($request->input('type') === 'Dealer Feed') {
                $dealerName = ($request->input('dealer_id') == 0)
                    ? 'All'
                    : Dealer::findOrFail($request->input('dealer_id'))->name;

                $description .= 'Dealer: '. $dealerName ."\n";
            }

            if ($request->filled('ip')) {
                $description .= 'IP: '. $request->input('ip') ."\n";
            }

            if ($request->filled('device')) {
                $description .= 'Device: '. ucwords($request->input('device')) ."\n";
            }

            if ($request->filled('browser')) {
                $description .= 'Browser: '. ucwords($request->input('browser')) ."\n";
            }

            $description .= "\n". $request->input('description');
            /** Description END **/

            $jiraField->setProjectKey(env('JIRA_PROJECT_KEY'))
                ->setIssueType('Task')
                ->setSummary('User submitted task (Ticket no: '. $ticketNo .')')
                ->setAssigneeAccountId(env('JIRA_ASSIGNEE_ID'))
                ->setReporterAccountId(env('JIRA_REPORTER_ID'))
                ->setDescription($description);

            $jiraTicket = $jiraService->create($jiraField);
            $jiraTicket = $jiraService->get($jiraTicket->key);

            $issue = new Issue();
            $issue->ticket_no = $ticketNo;
            $issue->issue_key = $jiraTicket->key;
            $issue->name = $request->input('name');
            $issue->email = $request->input('email');
            $issue->phone = $request->input('phone');
            $issue->type = $request->input('type');
            $issue->dealer_id = $request->input('type') == 'Dealer Feed' ? $request->input('dealer_id') : null;
            $issue->description = $request->input('description');

            $status = Status::firstOrCreate([ 'name' => strtolower(trim($jiraTicket->fields->status->name))]);
            $issue->status = $status->id;

            $issue->created_by = Auth::guard('api')->user()->getAuthIdentifier();
            $issue->save();

            /** Photos **/
            if ($request->hasFile('photos')) {
                $photos = [];

                foreach ($request->file('photos') as $photo) {
                    $fileName = $photo->store('', 'dealer-issues');
                    // dispatch(new \App\Jobs\ImageOptimize($fileName, 'issues'))->onQueue('default');

                    $new_file_name = saveImageInWebp('issues', $fileName);
                    array_push($photos, public_path('images/issues/'. $new_file_name));

                    $issueFile = new IssueFile();
                    $issueFile->issue_id = $issue->id;
                    $issueFile->file_url = $new_file_name;
                    $issueFile->save();
                }

                $attachments = $jiraService->addAttachments($jiraTicket->key, $photos);

                foreach ($attachments as $attachment) {
                    $issueFile = IssueFile::where('file_url', $attachment->filename)->first();
                    $issueFile->attachment_id = $attachment->id;
                    $issueFile->save();
                }
            }
            /** Photos END **/

            return response()->json([
                'status' => 'success',
                'data' => [
                    'issue' => $issue
                ]
            ], Response::HTTP_CREATED);
        }
        catch (JiraException $e) {
            DB::rollBack();

            Log::error('On Issue Create API', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong.',
                'data' => [
                    'issue' => null
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $issueId
     * @return JsonResponse
     */
    public function show($issueId)
    {
        $issue = Issue::with('photos')->where('id', $issueId)->first();

        if (! $issue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Issue not found.',
                'data' => [
                    'issue' => null
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        if (Auth::guard('api')->user()->cant('view', $issue)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logged in user can\'t do that action.',
                'data' => [
                    'issue' => null
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        $jiraService = new JiraService($this->jiraCredentials);
        $jiraTicket = $jiraService->get($issue->issue_key);

        if ($issue->status()->name !== $jiraTicket->fields->status->name) {
            $status = Status::firstOrCreate([ 'name' => strtolower(trim($jiraTicket->fields->status->name))]);
            $issue->status = $status->id;
            $issue->save();
        };

        $issue->photos
            ->transform(function ($file) {
                $file->file_url = env('AWS_URL') . '/' . $file->file_url;
                return $file;
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'issue' => $issue
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $issueId
     * @return JsonResponse
     */
    public function update(Request $request, $issueId): JsonResponse
    {
        $issue = Issue::find($issueId);

        if (! $issue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Issue not found.',
                'data' => [
                    'issue' => null
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        if (Auth::guard('api')->user()->cant('update', $issue)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logged in user can\'t do that action.',
                'data' => [
                    'issue' => null
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), $this->validationRules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not valid.',
                'data' => [
                    'errors' => $validator->messages()->toArray(),
                    'issue' => null
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        date_default_timezone_set("Etc/UCT");

        try {
            $jiraField = new JiraField();
            $jiraService = new JiraService($this->jiraCredentials);

            /** Description **/
            $description = 'User: '. Auth::guard('api')->user()->username ."\n";
            $description .= 'Name: '. $request->input('name') ."\n";
            $description .= 'Email: [mailto:'. $request->input('email') ."]\n";
            $description .= 'Phone Number: '.$request->input('phone') ."\n";
            $description .= 'Type: '. $request->input('type') ."\n";

            if ($request->input('type') === 'Dealer Feed') {
                $dealerName = ($request->input('dealer_id') == 0)
                    ? 'All'
                    : Dealer::findOrFail($request->input('dealer_id'))->name;

                $description .= 'Dealer: '. $dealerName ."\n";
            }

            if ($request->filled('ip')) {
                $description .= 'IP: '. $request->input('ip') ."\n";
            }

            if ($request->filled('device')) {
                $description .= 'Device: '. ucwords($request->input('device')) ."\n";
            }

            if ($request->filled('browser')) {
                $description .= 'Browser: '. ucwords($request->input('browser')) ."\n";
            }

            $description .= "\n". $request->input('description');
            /** Description END **/

            $jiraField->setProjectKey(env('JIRA_PROJECT_KEY'))
                ->setIssueType('Task')
                ->setDescription($description);

            $jiraService->update($issue->issue_key, $jiraField);
            $jiraTicket = $jiraService->get($issue->issue_key);

            $issue->name = $request->input('name');
            $issue->email = $request->input('email');
            $issue->phone = $request->input('phone');
            $issue->type = $request->input('type');
            $issue->dealer_id = $request->input('type') == 'Dealer Feed' ? $request->input('dealer_id') : null;
            $issue->description = $request->input('description');

            $status = Status::firstOrCreate([ 'name' => strtolower(trim($jiraTicket->fields->status->name))]);
            $issue->status = $status->id;

            $issue->save();

            /** Photos **/
            if ($request->hasFile('photos')) {
                $photos = [];

                foreach ($request->file('photos') as $photo) {
                    $fileName = $photo->store('', 'dealer-issues');
                    // dispatch(new \App\Jobs\ImageOptimize($fileName, 'issues'))->onQueue('default');

                    $new_file_name = saveImageInWebp('issues', $fileName);
                    array_push($photos, public_path('images/issues/'. $new_file_name));

                    $issueFile = new IssueFile();
                    $issueFile->issue_id = $issue->id;
                    $issueFile->file_url = $new_file_name;
                    $issueFile->save();
                }

                $attachments = $jiraService->addAttachments($jiraTicket->key, $photos);

                foreach ($attachments as $attachment) {
                    $issueFile = IssueFile::where('file_url', $attachment->filename)->first();
                    $issueFile->attachment_id = $attachment->id;
                    $issueFile->save();
                }
            }
            /** Photos END **/

            return response()->json([
                'status' => 'success',
                'data' => [
                    'issue' => $issue
                ]
            ], Response::HTTP_CREATED);
        }
        catch (JiraException $e) {
            DB::rollBack();

            Log::error('On Issue Update API', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong.',
                'data' => [
                    'issue' => null
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $issueId
     * @return JsonResponse
     */
    public function destroy($issueId)
    {
        $issue = Issue::with('photos')->where('id', $issueId)->first();

        if (! $issue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Issue not found.',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        if (Auth::guard('api')->user()->cant('delete', $issue)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logged in user can\'t do that action.',
                'data' => [
                    'issue' => null
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        DB::beginTransaction();

        //delete from jira
        $jiraService = new JiraService($this->jiraCredentials);
        $ret = $jiraService->deleteIssue($issue->issue_key);

        $issue->photos()->delete();
        $issue->delete();

        foreach ($issue->photos as $file) {
            deleteImageOrFile('dealer-issues', $file->file_url);
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'data' => []
        ], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param $issueId
     * @return JsonResponse
     */
    public function photoDestroy(Request $request, $issueId)
    {
        $issue = Issue::find($issueId);

        if (! $issue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Issue not found.',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        if (Auth::guard('api')->user()->cant('delete', $issue)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logged in user can\'t do that action.',
                'data' => [
                    'issue' => null
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $request->filled('photo')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You didn\'t provide the photo URL correctly.',
                'data' => []
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $fileName = last(explode('/', $request->input('photo')));
        $issueFile = IssueFile::where('file_url', $fileName)->first();

        if (! $issueFile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Photo not found in issue photos.',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $jiraAttachmentService = new AttachmentService($this->jiraCredentials);
            $jiraAttachmentService->remove($issueFile->attachment_id);

            $issueFile->delete();
            deleteImageOrFile('dealer-issues', $fileName);
        }
        catch (JiraException $e) {
            DB::rollBack();

            Log::error('On Issue Create API', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong.',
                'data' => []
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'status' => 'success',
            'data' => []
        ], Response::HTTP_OK);
    }
}
