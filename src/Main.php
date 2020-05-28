<?php


namespace Soen\Command;


use Soen\Container\Server;

class Main
{
    function __construct($httpConfigDirectory)
    {
        $array = (new Server($httpConfigDirectory))->load();
//        var_dump($array);
    }
    
    public function run(){
        context()->getComponent('router')->test();
    }
}