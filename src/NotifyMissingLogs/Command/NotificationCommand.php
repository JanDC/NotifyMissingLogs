<?php

namespace NotifyMissingLogs\Command;

use NotifyMissingLogs\JiraQueries;
use NotifyMissingLogs\Login;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class NotificationCommand extends Command
{

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $configuration = Yaml::parse(file_get_contents(__DIR__ . '/../../../app/config/config.yml'));


        $server_endpoint = $configuration['jira']['server_endpoint'];
        $username = $configuration['jira']['username'];
        $password = $configuration['jira']['password'];

        $login = new Login($server_endpoint, $username, $password);

        $jiraQueries = new JiraQueries($login, $configuration['worklogs']);

        $result = $jiraQueries->validateLogs();
        $output->writeln(json_encode($result,JSON_PRETTY_PRINT));
    }

}