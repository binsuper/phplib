<?php

namespace Gino\Phplib\Config\Parser;

use Gino\Phplib\ArrayObject;

class IniParser implements IParser {

    /**
     * @inheritDoc
     */
    public function load(string $filepath) {
        $data = parse_ini_file($filepath, true);
        if (false === $data) {
            throw new \Exception(sprintf('can not parse ini file "%s"', $filepath));
        }
        foreach ($data as $section => $sub) {
            $array = new ArrayObject();
            foreach ($sub as $k => $v) {
                if (strpos($k, $section) === 0) {
                    $k = substr($k, strlen($section) + 1);
                }
                $array->set($k, $v);
            }
            $data[$section] = $array->toArray();
        }
        return $data;
    }

}