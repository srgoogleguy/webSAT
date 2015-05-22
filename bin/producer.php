#!/usr/local/bin/php
<?php

require '../autoloader.php';

$beacon = new webSAT\Beacon('tcp://127.0.0.1:5555');
$logParser = new webSAT\LogParser('%h %l %u %t "%r" %>s %O %D "%{Referer}i" "%{User-Agent}i"');
while($msg = fgets(STDIN)) {
    $msg = trim($msg);
    $data = $logParser->parse($msg);
    $signal = new webSAT\Signal('webserver', $data);
    $beacon->signal($signal);
}
