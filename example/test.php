<?php

require_once 'vendor/autoload.php';

$config = new \Gino\Phplib\Config\Config();
$config->loadAll();
print_r($config->get());