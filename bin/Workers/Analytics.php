<?php
namespace webSAT\Workers;

use \webSAT\Microwave
    \webSAT\Signal,
    \webSAT\Worker;

class Analytics extends \webSAT\Microwave
{
    private $memcached;
    private $metrics = [
        'request' => [
                'name'    => 'requests',
                'value'   => false,
                'default' => 0,
              ],
        'requestTimeuSec' => [
                'name'    => 'ttlb',
                'value'   => true,
                'default' => 0,
              ],
    ];
    private static $prefix = 'webSAT';
    
    public function onStart(Array $dependencies = null)
    {
        $this->memcached = $dependencies ? $dependencies['memcached'] : null;
    }
    
    public function onReceive(\webSAT\Signal $signal)
    {
        foreach($signal->payload as $key => $value) {
            if ($metric  = $this->getMetric($key)) {
                $key     = $this->generateMemcachedKey($metric['name'], $signal->payload->time);
                $default = $metric['default'];
                $value   = $metric['value'] ? (int) $value : 1;
                $this->memcached->increment($key, $value, $default);
            }
        }
    }
    
    public function onStop()
    {
        unset($this->memcached);
    }
    
    private function getTimestamp($timestamp)
    {
        $date = new \DateTime($timestamp);
        $date->setTime($date->format('H'), $date->format('i'), 0);
        return $date->getTimestamp();
    }
    
    private function generateMemcachedKey($metric, $timestamp)
    {
        return static::$prefix . '.' . $metric . '.' . $this->getTimestamp($timestamp);
    }
    
    private function getMetric($name) {
        return isset($this->metrics[$name]) ? $this->metrics[$name] : false;
    }
}
