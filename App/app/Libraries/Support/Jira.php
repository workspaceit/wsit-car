<?php


namespace App\Libraries\Support;


use Illuminate\Support\Facades\Log;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\IssueField;
use JiraRestApi\Issue\IssueService;
use SimpleUserAgent\UserAgent;
use Throwable;

class Jira
{
    /**
     * Jira credentials container
     * @var ArrayConfiguration
     */
    protected $jiraCredentials;

    /**
     * Jira constructor.
     */
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

    /**
     * Create jira issue for live exceptions
     *
     * @param $exception
     * @return \JiraRestApi\Issue\Issue|null
     */
    public function createAppExceptionIssue($exception)
    {
        try{
            date_default_timezone_set("Etc/UCT");
            $issueService = new IssueService($this->jiraCredentials);
            $issue = $issueService->create($this->field($exception));
            Log::info("Issue created response : ". json_encode($issue));

            # Add issue watcher
            $issueService->addWatcher($issue->key, env('JIRA_ERROR_WATCHER_ID'));
            Log::info("Issue created for exception : ". $exception->getMessage());
        }catch (Throwable $throwable){
            Log::error("Error occurs in createAppExceptionIssue actions.");
            Log::info($throwable->getMessage());
            Log::info($throwable->getTraceAsString());
        }

        return null;
    }

    /**
     *
     * @param $exception
     * @return IssueField
     */
    public function field($exception)
    {
        $namespaace = explode(DIRECTORY_SEPARATOR, get_class($exception));
        return (new IssueField())->setProjectKey(env('JIRA_ERROR_PROJECT_KEY'))
            ->setAssigneeAccountId(env('JIRA_ERROR_ASSIGNEE_ID'))
            ->setSummary("Error Email Report : " . end($namespaace))
            ->setIssueType("Bug")
            ->setPriorityName("Critical")
            ->setDescription($this->getUserDetails().'\\\\'. $exception->getMessage(). '\\\\ \\\\'.$exception->getTraceAsString());
    }
    /**
     * Fetch user details
     *
     * @return string
     */
    public function getUserDetails()
    {
        $details = 'Time: '.now()->setTimezone('EST')->format('Y-m-d H:i:s').'\\\\'.
            'User: '. auth()->check() ? auth()->user()->username : "Cron" . '\\\\'.
            'IP: ['.request()->ip().'|https://whois.domaintools.com/'.request()->ip().']';

        try {
            $userAgent = (new UserAgent())->getInfo();
        }
        catch(\Exception $e) {
            $userAgent = null;
            Log::info(request()->header('User-Agent'));
            Log::error($e);
        }
        if ($userAgent) {
            $details .= '\\\\'.'Device: '.$userAgent['device'].'\\\\'.
                'OS: '.$userAgent['os'].'\\\\'.
                'Browser: '.$userAgent['browser'].' '.$userAgent['version'];
        }

        return $details;
    }
}
