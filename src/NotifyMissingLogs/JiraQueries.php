<?php


namespace NotifyMissingLogs;

use chobie\Jira\Api;
use chobie\Jira\Api\Result;
use chobie\Jira\Issue;
use chobie\Jira\Issues\Walker;
use DateTime;
use Symfony\Component\Debug\Debug;

class JiraQueries
{
    /**
     * @var Login
     */
    private $login;

    /** @var array */
    private $officehours;

    /**
     * JiraQueries constructor.
     *
     * @param Login $login
     * @param array $workLogsConfig
     */
    public function __construct(Login $login, array $workLogsConfig)
    {
        $this->login = $login;
        $this->officehours = (array)$workLogsConfig['office_hours'];
    }


    public function validateLogs()
    {
        $requiredResult = $this->determineMinimumResult();

        $todaysLogs = $this->getLogsSum();

        return ['logged' => $todaysLogs, 'required' => $requiredResult, 'missing' => $requiredResult - $todaysLogs];

    }

    /**
     * @return int
     */
    private function determineMinimumResult()
    {
        $now = new DateTime();
        // Is office open? mo-fri
        if ($now->format('N') > 5) {
            return 0;
        }

        $interval = new \DateInterval("PT{$this->officehours['start']}H");

        $remainder = $now->sub($interval);
        $now = new DateTime();

        if ($remainder->format('d') !== $now->format('d')) {
            return 0;
        }

        $minutes = (60 * $remainder->format('H')) + intval($remainder->format('i'));

        return $minutes;
    }

    /**
     * @return int
     */
    private function getLogsSum()
    {
        $now = new DateTime();
        $walker = new Walker($this->getApi());
        $jqlString = sprintf("worklogAuthor = currentUser() AND worklogDate = '%s'", $now->format('Y-m-d'));

        $walker->push($jqlString);

        $sum = 0;
        foreach ($walker as $issue) {
            /** @var Issue $issue */
            $worklogs = $this->getApi()->getWorklogs($issue->getKey(), [])->getResult();
            foreach ($worklogs['worklogs'] as $worklog) {
                if ($worklog['updateAuthor']['name'] !== $this->getUser()) {
                    continue;
                }
                $sum += intval($worklog['timeSpentSeconds'] / 60);
            }

        }

        return $sum;
    }

    private function getApi()
    {
        return $this->login->getJiraApi();
    }

    private function getUser()
    {
        return $this->login->getUser();
    }
}