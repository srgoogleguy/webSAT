<?php

require '../autoloader.php';

$satellite = new webSAT\Satellite('tcp://127.0.0.1:5555');
$memcached = new Memcached;
$memcached->addServer('127.0.0.1', 11211);
$memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);

$dependencies = ['memcached' => $memcached];
$workers[]    = new webSAT\Workers\Analytics(['webserver']);
$station      = new webSAT\Station($satellite, $workers, $dependencies);
$station->oscillate();
