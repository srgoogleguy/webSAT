<?php
namespace webSAT;

class LogParser
{
    protected $formatString;
    protected static $defaultFormat = '%h %l %u %t "%r" %>s %b';
    protected $patterns = [
        '%%' => '(?P<percent>\%)',
        '%a' => '(?P<remoteIp>(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))',
        '%A' => '(?P<localIp>(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))',
        '%h' => '(?P<host>[a-zA-Z0-9\-\._:]+)',
        '%l' => '(?P<logname>(?:-|[\w-]+))',
        '%m' => '(?P<requestMethod>OPTIONS|GET|HEAD|POST|PUT|DELETE|TRACE|CONNECT)',
        '%p' => '(?P<port>\d+)',
        '%r' => '(?P<request>(?:(?:[A-Z]+) .+? HTTP/1.(?:0|1))|-|)',
        '%t' => '\[(?P<time>\d{2}/(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/\d{4}:\d{2}:\d{2}:\d{2} (?:-|\+)\d{4})\]',
        '%u' => '(?P<user>(?:-|[\w-]+))',
        '%U' => '(?P<URL>.+?)',
        '%v' => '(?P<serverName>([a-zA-Z0-9]+)([a-z0-9.-]*))',
        '%V' => '(?P<canonicalServerName>([a-zA-Z0-9]+)([a-z0-9.-]*))',
        '%>s' => '(?P<status>\d{3}|-)',
        '%b' => '(?P<responseBytes>(\d+|-))',
        '%T' => '(?P<requestTime>(\d+\.?\d*))',
        '%O' => '(?P<sentBytes>[0-9]+)',
        '%I' => '(?P<receivedBytes>[0-9]+)',
        '%D' => '(?P<requestTimeuSec>(\d+|-))',
        '\%\{(?P<name>[a-zA-Z]+)(?P<name2>[-]?)(?P<name3>[a-zA-Z]+)\}i' => '(?P<\\1\\3>.*?)',
    ];
    
    public function __construct($format = null)
    {
        if (!$format) {
            $format = static::$defaultFormat;
        }
        $this->formatString = $format;
        $this->setFormat($this->formatString);
    }
    
    public function setFormat($format)
    {
        //$format = \preg_quote($format, '/');
        $expression = "#^{$format}$#";
        foreach ($this->patterns as $pattern => $replace) {
            $expression = \preg_replace("/{$pattern}/", $replace, $expression);
        }
        return $this->formatString = $expression;
    }
    
    public function parse($line)
    {
        if (!\preg_match($this->formatString, $line, $matches)) {
            return false;
        }
        $data = [];
        foreach($matches as $key => $value) {
            if (is_string($key)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
