<?php
/**
 * Displays statistics aggregated by your webserver for the last hour
 * You can modify the code here to get data for different ranges
 */

$memcached = new Memcached;
$memcached->addServer('127.0.0.1', 11211);

$endRange = new DateTime;
$endRange ->setTime($endRange ->format('H'), $endRange->format('i'), 0);
$startRange = clone $endRange;
$startRange->sub(new DateInterval('PT1H'));
$interval = new DateInterval('PT1M');

$period = new DatePeriod($startRange, $interval, $endRange);

foreach ($period as $dateTime) {
    $requestKeys[] = 'webSAT.requests.' . $dateTime->getTimestamp();
    $ttlbKeys[] = 'webSAT.ttlb.' . $dateTime->getTimestamp();
}

$requests = $memcached->getMulti($requestKeys);
$ttlbs = $memcached->getMulti($ttlbKeys);

$requests = array_sum($requests);
$ttlbs = array_sum($ttlbs);

$avgTTLB = $ttlbs / $requests / 1000; // in ms
printf("Average TTLB per request for the last hour was: %.3fms @ %d total requests served\n", $avgTTLB, $requests);
