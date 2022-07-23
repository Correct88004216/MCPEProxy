<?php

require_once("./vendor/autoload.php");
require_once("./Lib.php");
require_once("./Logger.php");

echo "\nEnter IP: ";
$ip = substr(fgets(STDIN),0,-1);
echo "\nEnter PORT: ";
$port = substr(fgets(STDIN),0,-1);
echo "\nProxy started!";
new Lib($ip, $port, '0.0.0.0', 19132);