<?php

namespace Bricksite\RRPProxy;

class RRPClient
{
    public $connector;

    public function __construct(string $username = null, string $password = null, bool $test = false) {
        $this->connector = new Connector($username, $password, $test);
    }

    public function request(string $command, array $args = [])
    {
        return $this->connector->request($command, $args);
    }
}
