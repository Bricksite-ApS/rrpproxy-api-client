<?php

namespace Bricksite\RRPProxy;

class RRPClient
{
    public Connector $connector;

    public function __construct(string $username, string $password, bool $test = false) {
        $this->connector = new Connector($username, $password, $test);
    }

    public function request(string $command, array $args = [])
    {
        return $this->connector->request($command, $args);
    }
}
