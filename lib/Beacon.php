<?php
namespace webSAT;

use \ZMQSocket,
    \ZMQContext,
    \ZMQ,
    webSAT\Signal;
    
/**
 * @class       Beacon
 * @description Sends beacon signals to a satellite
 */
 
class Beacon
{
    protected $frequency,
              $blockingMode,
              $zmqContext,
              $zmqSocket;
    protected $signals   = 0;
    protected $connected = false;
    
    public function __construct($frequency = "tcp://*:5555", $blockingMode = false, $autoTrigger = true)
    {
        $this->frequency    = $frequency;
        $this->blockingMode = $blockingMode ? 0 : ZMQ::MODE_NOBLOCK;
        
        if ($autoTrigger) {
            $this->trigger();
        }
    }
    
    public function trigger()
    {
        if ($this->connected) {
            return false;
        }
        
        if (!$this->zmqContext) {
            $this->zmqContext = new ZMQContext;
        }
        
        if (!$this->zmqSocket) {
            $this->zmqSocket =  new ZMQSocket($this->zmqContext, ZMQ::SOCKET_PUSH);
        }
        
        $this->zmqSocket->connect($this->frequency);
        
        return $this->connected = true;
    }
    
    public function trip()
    {
        if (!$this->connected) {
            return false;
        }
        
        $this->zmqSocket->disconnect();
        $this->connected = false;
        
        return true;
    }
    
    public function signal(Signal $signal)
    {
        if ($this->zmqSocket->send((string) $signal, $this->blockingMode)) {
            $this->signals++;
        }
    }
    
    public function getSignalsSent()
    {
        return $this->signals;
    }
}
