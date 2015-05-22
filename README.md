# WebSAT Library

webSAT (__Web Server Analytics and Telemetry__) is a library intended for performing real-time
analytics on your webserver's access log data using PHP, ZeroMQ, and Memcached. It works by using
ZeroMQ to transmit access log data over TCP or IPC from a producer script, and collecting the data
from a consumer script, that can then aggregate and store such data in Memcached. You can then use
aggregated data in Memcached to produce useful graphs for monitoring your webservers. This allows
you to also collect access log data from multiple webservers concurrently and aggregate them in one
or multiple places.

Important things you can monitor in real time using this library:

- TTLB (_Time To Last Byte_ - otherwise known as request time)
- TTFB (_Time To First Byte_ - [using mod_log_firstbyte](https://code.google.com/p/mod-log-firstbyte/))
- Number of 2XX, 3XX, 4XX, 5XX response codes
- Daily active unique visitors (using IP or cookie data from the access log)
- Slowest performing URLs
- Most popular URLs

## How It Works

![webSAT Flowchart](http://i.imgur.com/Y3Isvac.png)

We equip your webserver with a beacon, that sends out signals about what your webserver is doing. These signals are then captured by a satellite, which transmitts them to a station that has one or more microwaves. If a given microwave tuned to the beacon's signal knows what to do with that signal it can process it, otherwise it is discarded.

### Terminology

First, to clear up some things, let's explain what all these terms mean...

Term          | Definition
------------- | -------------
`Satellite`   | A Satellite is the PULL mechanism of the consumer in this PUSH/PULL topology
`Beacon`      | A Beacon is the PUSH mechanism of the producer in this PUSH/PULL topology
`Signal`      | A Signal is what a Beacon will transmit to a Satellite and is comprised of two parts (namely: a _designation_ and a _payload_)
`Microwave`   | A Microwave is the worker mechanism of the consumer, which does the actual work when consuming a given Signal
`Station`     | A Station is the collection of consumer mechanisms, including the Satellite and one or more Microwaves (_workers_)

A `Station` can only have a single `Satellite` (this is the thing pulling in the actual messages off the queue), but multiple `Microwave`s (the thing doing the actual work). Each `Microwave` can have one or more _designations_. When the `Station` receives a `Signal` from the `Satellite`, it will ask all of its `Microwave`s if they have the given _designation_ in the incoming `Signal`. If the `Microwave` has such _designation_ it will be handed the `Signal` for processing. It is possible for more than one `Microwave` to have the same _designation(s)_, since the same `Signal` may be consumed by more than one worker in a given `Station`. Once a message is consumed by the `Satellite`, however, it can not be consumed again by the same `Satellite`. It's the `Station`'s job to hand off `Signal`s to its `Microwave`s after consumption from a `Satellite`.

Each `Microwave` implements a `Worker` interface, which extends a `Microwave` _abstract_ class. Each `Microwave` (_worker_) then has to implement four basic methods. The `onStart`, `onStop`, `onRecieve`, and `hasDesignation` methods. The `hasDesignation` method must always return a boolean `true`/`false` and accepts a `Signal` argument. If `Microwave::hasDesignation()` returns `true`, the `Signal` is handed to that `Microwave`'s `onReceive` callback method for processing.

## Benefits

Since webSAT is completely isolated from your application layer, it does not introduce any meaningful overhead either on the client or server side. While Google Analytics is a great tool it can not give you certain insight into how your individual webservers are performing (such as TTFB). webSAT utilizes ZeroMQ and TCP/IP, which makes it a portable and versatile solution that can be used in a distributed fashoin across multiple servers.

## Caveats

Since nginx does not support a piped logging facility such as that of Apache httpd, you will need to write an additional script to tail and pipe your access log for you. You get this for free with Apache httpd, however (no additional moving parts). For now the library does not offer this facility to you directly, but there are plenty of tools out there that support this - see [mkfifo(1)](http://linux.die.net/man/3/mkfifo), for example.

## Quick Start Guide

The easiest way to use webSAT is through Apache httpd's piped logging facility. Simply clone the
library's git repository onto your server, then place the following line into your vhost file or
httpd.conf file:

`CustomLog "|/path/to/webSat/producer.php" combined`

Be sure to modify to your local path. The producer script will then take the access log data Apache
httpd spits out to STDIN and pipes it into the queue. You can then run the consumer script in
`supervisord` and have the data aggregated in Memcached.

The aggregate data has a granularity of 1 minute intervals. You can tweak this from the consumer's
worker script if you'd like, but this is usually sufficient for high-load environments as it's more
memory, I/O, and CPU efficient.
