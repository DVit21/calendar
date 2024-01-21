<?php

namespace libs;

class commands
{
    private array $commands;
    private int $activeCommand;
    public function __construct()
    {
        $this->commands=json_decode(file_get_contents('config/commands.json'),true);
    }
    public function setCommand()
    {
        $this->activeCommand=0;
    }
    public function getActiveCommand():int
    {
        return $this->activeCommand;
    }
    public function getTitle():string
    {
        return $this->commands[$this->activeCommand]['title'];
    }
    public function getIncludeInHeader():string
    {
        return $this->commands[$this->activeCommand]['file'].'.html';
    }
    public function run():void
    {
        include $this->commands[$this->activeCommand]['file'].'.php';
    }
    public function getCommands():array
    {
        return $this->commands;
    }
}