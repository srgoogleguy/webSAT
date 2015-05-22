<?php
namespace webSAT;


interface Worker
{
    public function onStart(Array $dependencies = null);
    public function onReceive(\webSAT\Signal $signal);
    public function onStop();
    public function hasDesignation(\webSAT\Signal $signal);
}
