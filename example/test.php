<?php

require_once 'vendor/autoload.php';

$config = new \Gino\Phplib\Config\Config();
$data = $config->get('testini');

//print_r($data);