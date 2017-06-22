<?php

namespace NotifyMissingLogs;

use chobie\Jira\Api;
use chobie\Jira\Api\Authentication\Basic;

class Login
{
    /** @var string */
    private $server_endpoint;

    /** @var  Api */
    private $api;

    /** @var string */
    private $username;

    public function __construct(string $server_endpoint, string $username, string $password)
    {
        $this->server_endpoint = $server_endpoint;
        $this->username = $username;

        try {
            $this->api = new Api($this->server_endpoint, new Basic($username, $password));
        } catch (Api\UnauthorizedException $uae) {
            // Let it slip
        }
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @throws Api\UnauthorizedException
     */
    public function login(string $username, string $password)
    {
        $this->api = new Api($this->server_endpoint, new Basic($username, $password));
        $this->api->getFields();
    }

    public function getJiraApi()
    {
        if (!($this->api instanceof Api)) {
            throw new Api\UnauthorizedException('Please login, before accessing the api');
        }
        return $this->api;
    }

    public function getUser()
    {
        return $this->username;
    }
}