<?php
namespace webSAT;

class Station
{
    protected $satellite;
    protected $online       = false;
    protected $workers      = [];
    protected $dependencies = [];
    
    public function __construct(Satellite $satellite, Array $workers = null, Array $dependencies = null)
    {
        $this->satellite    = $satellite;
        $this->dependencies = $dependencies;
        
        if ($workers) {
            foreach($workers as $worker) {
                $this->addWorker($worker);
            }
        }
    }
    
    public function addWorker(Worker $worker)
    {
        $this->workers[] = $worker;
    }
    
    public function removeWorker(Worker $removableWorker)
    {
        foreach ($this->workers as $key => $worker) {
            if ($worker === $removableWorker) {
                unset($this->workers[$key]);
            }
        }
    }
    
    public function oscillate()
    {
        if ($this->online) {
            return false;
        }
        $this->satellite->trigger();
        foreach($this->workers as $worker) {
            $worker->onStart($this->dependencies);
        }
        $this->online = true;
        while($signal = $this->satellite->capture()) {
            if ($signal) {
                foreach($this->workers as $worker) {
                    if ($worker->hasDesignation($signal)) {
                        $worker->onReceive($signal);
                    }
                }
            }
        }
        return true;
    }
    
    public function __destruct()
    {
        $this->satellite->trip();
        foreach($this->workers as $worker) {
            $worker->onStop();
        }
    }
}
