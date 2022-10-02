<?php

require_once 'vendor/autoload.php';

$config = new \Gino\Phplib\Config\Config([
//    'parsers' => ['php' => \Gino\Phplib\Parser\ArrayParser::class]
]);
//$data = $config->get('testini');

//$config->loadAll();
//$config->set('array.owner.sex', 'man');
//print_r($config->get('json'));



//$yp = new \Gino\Phplib\Config\YamlParser();
//$yp->parse('config/yaml.yaml');


var_dump(\Gino\Phplib\Config\Config::instance()->get('jso1n'));