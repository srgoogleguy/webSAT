<?php
namespace webSAT;


abstract class Microwave implements \webSAT\Worker
{
    protected $designations = [];
    
    public function __construct(Array $designations = null)
    {
        $this->designations = $designations ? $designations : [];
    }
    
    public function hasDesignation(Signal $signal)
    {
        return \in_array($signal->designation, $this->designations, true);
    }
}
