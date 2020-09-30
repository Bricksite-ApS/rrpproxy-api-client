<?php

namespace Bricksite\RRPProxy;

use Bricksite\RRPProxy\Connector;

class RRPClient
{
    public $connector;

    public function __construct(string $username = null, string $password = null, bool $test = false) {
        $this->connector = new Connector($username, $password, $test);
    }

    public function setUsername(string $username)
    {
        $this->connector->username = $username;
    }

    public function setPassword(string $password)
    {
        $this->connector->password = $password;
    }

    public function setTestMode(bool $enable)
    {
        $this->connector->test = $enable;
    }

    public function request(string $command, array $args = [])
    {
        return $this->connector->request($command, $args);
    }
}
