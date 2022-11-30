<?php

require_once 'vendor/autoload.php';

$arr = new \Gino\Phplib\ArrayObject(['a.b' => 123]);
var_dump($arr);
var_dump($arr->del('a.b'));