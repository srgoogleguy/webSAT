<?php
namespace webSAT;

use \ZMQSocket,
    \ZMQContext,
    \ZMQ,
    webSAT\Signal;
    
/**
 * @class       Satellite
 * @description Recieves signals from a beacon
 */
 
class Satellite
{
    protected $frequency,
              $blockingMode,
              $zmqContext,
              $zmqSocket;
    protected $signals   = 0;
    protected $connected = false;
    
    public function __construct($frequency = "tcp://*:5555", $blockingMode = true, $autoTrigger = false)
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
            $this->zmqSocket =  new ZMQSocket($this->zmqContext, ZMQ::SOCKET_PULL);
        }
        
        $this->zmqSocket->bind($this->frequency);
        
        return $this->connected = true;
    }
    
    public function trip()
    {
        if (!$this->connected) {
            return false;
        }
        
        $this->zmqSocket->unbind();
        $this->connected = false;
        
        return true;
    }
    
    public function capture()
    {
        $message = \json_decode($this->zmqSocket->recv($this->blockingMode));
        if (!\json_last_error() && isset($message->designation) && isset($message->payload)) {
            return new Signal($message->designation, $message->payload);
        }
    }
    
    public function getSignalsReceived()
    {
        return $this->signals;
    }
}
