<?php

namespace App\Jobs;

use App\Models\Issue;
use App\Models\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\IssueService;

class IssuesUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Issue $issue
     */
    protected $issue;

    /**
     * @var ArrayConfiguration $jiraCredentials
     */
    protected $jiraCredentials;

    /**
     * Create a new job instance.
     *
     * @param Issue $issue
     * @return void
     */
    public function __construct(Issue $issue)
    {
        $this->issue = $issue;
        $this->jiraCredentials = new ArrayConfiguration([
            'jiraHost' => env('JIRA_HOST'),
            'jiraUser' => env('JIRA_USER'),
            'jiraPassword' => env('JIRA_PASS'),
        ]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $issueService = new IssueService($this->jiraCredentials);
            $ret = $issueService->get($this->issue->issue_key);

            $status = Status::firstOrCreate(['name' => strtolower(trim($ret->fields->status->name))]);
            $this->issue->status = $status->id;
            $this->issue->save();
        }catch (\Throwable $th) {
            Log::info("Error occurs in IssuesUpdateJob : ". $th->getMessage());
        }
    }
}
