<?php
header("X-Content-Type-Options: nosniff");
include 'vendor/autoload.php';

require 'include/init.php';
global $cmd;
//header
echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1"><title>';
echo $cmd->getTitle().'</title>';
include $cmd->getIncludeInHeader();
echo '</head>';
//body
echo '<body>';
$cmd->run();
echo '</body></html>';
