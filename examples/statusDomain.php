<?php

require_once(dirname(__DIR__) . '/vendor/autoload.php');

use Bricksite\CentralNic\CentralNicClient;

$rrp = new CentralNicClient('USERNAME', 'PASSWORD', false);

$result = $rrp->request('StatusDomain', ['domain' => 'domain.com']);

print_r($result);
