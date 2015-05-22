<?php
namespace webSAT;
    
/**
 * @class       Signal
 * @description Carries a signal from a beacon to a satellite
 */
 
class Signal
{
    public $designation, $payload;
    
    public function __construct($designation, $payload)
    {
        $this->designation = $designation;
        $this->payload     = $payload;
    }
    
    public function __toString()
    {
        return \json_encode($this);
    }
}
