# WebSAT Library

webSAT (__Web Server Analytics and Telemetry__) is a library intended for performing real-time
analytics on your webserver's access log data using PHP, ZeroMQ, and Memcached. It works by using
ZeroMQ to transmit access log data over TCP or IPC from a producer script, and collecting the data
from a consumer script, that can then aggregate and store such data in Memcached. You can then use
aggregated data in Memcached to produce useful graphs for monitoring your webservers. This allows
you to also collect access log data from multiple webservers concurrently and aggregate them in one
or multiple places.

## How It Works

![webSAT Flowchart](http://i.imgur.com/Y3Isvac.png)

We equip your webserver with a beacon, that sends out signals about what your webserver is doing. These signals are then captured by a satellite, which transmitts them to a station that has one or more microwaves. If a given microwave tuned to the beacon's signal knows what to do with that signal it can process it, otherwise it is discarded.

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
