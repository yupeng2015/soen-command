<?php

namespace Soen\Command\Event;

/**
 * Class CommandBeforeExecuteEvent
 * @package Soen\Command\Event
 */
class CommandBeforeExecuteEvent
{

    /**
     * @var string
     */
    public $command;

    /**
     * CommandBeforeExecuteEvent constructor.
     * @param string $command
     */
    public function __construct(string $command)
    {
        $this->command = $command;
    }

}
