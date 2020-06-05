<?php

namespace Soen\Command;

use Symfony\Component\Console\Application;

class Main
{
    public $appConsole;
    function __construct()
    {
        $this->appConsole = new Application();
    }
    
    public function run(){
        
    }
}