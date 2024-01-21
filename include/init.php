<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("allow_url_fopen", 1);

$cmdline=new \libs\ComLine();
$cmd=new \libs\commands();
$cmd->setCommand();
$lang=new \libs\languages("ru");
