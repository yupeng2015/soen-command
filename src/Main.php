<?php


namespace Soen\Command;


use Soen\Container\Container;
use Soen\Container\Server;

class Main
{
    function __construct($httpConfigDirectory)
    {
        $array = new Container($httpConfigDirectory);
//        var_dump($array);
    }
    
    public function run(){
        \App::getComponent('router')->test();
//        context()->getComponent('router')->test();
    }
}