<?php

use modules\menu\menu;

include 'vendor/autoload.php';

require 'include/init.php';
global $cmd;
global $cmdline;
//header
echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1"><title>';
echo $cmd->getTitle().'</title>';
include $cmd->getIncludeInHeader();
include "modules/menu/menu.html";
echo '</head>';
//body
echo '<body>';
$menu=new menu($cmd, $cmdline);
$menu->show();
$cmd->run();
echo '</body></html>';
