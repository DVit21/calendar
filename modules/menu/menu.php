<?php

namespace modules\menu;

use libs\ComLine;
use libs\commands;

class menu
{
    private commands $cmd;
    private ComLine $cmdline;
    public function __construct(commands $cmd, ComLine $cmdline)
    {
        $this->cmd=$cmd;
        $this->cmdline=$cmdline;
    }
    public function show(): void
    {
        $this->showButton();
        $this->showMenu();
        $this->showScript();
    }
    private function showMenu(): void
    {
        echo '<div class="menu">';
        $list=$this->cmd->getCommands();
        $cl=new ComLine(true);
        foreach ($list as $key=>$item) {
            $cl->setArgvGet("cmd",$key);
            echo'<div class="listItem';
            if($key==$this->cmd->getActiveCommand()) echo ' activeItem';
            echo '">';
            echo '<a href="'.$cl->CreateString().'">'.$item['title'].'</a>';
            echo '</div>';
        }
        echo '</div>';
    }
    private function showButton(): void
    {
        echo '<div id="top_menu"><img src="/img/icon-menu.svg" alt=""></div>';
    }
    private function showScript(): void
    {
        echo '<script>';

        echo '</script>';
    }


}