<?php

namespace Gino\Phplib\Parser;

use Gino\Phplib\ArrayObject;
use Gino\Phplib\Error\ParseException;

class IniParser extends Parser {

    /**
     * @inheritDoc
     */
    public function parse(string $filepath) {
        $data = parse_ini_file($filepath, true);
        if (false === $data) {
            throw new ParseException(sprintf('can not parse ini file "%s"', $filepath));
        }
        $result = new ArrayObject();

        foreach ($data as $section => $sub) {
            if (!is_array($sub)) {
                $result->set($section, $sub);
                continue;
            }

            $array = new ArrayObject();
            foreach ($sub as $k => $v) {
                if (strpos($k, $section) === 0) {
                    $k = substr($k, strlen($section) + 1);
                }
                $array->set($k, $v);
            }

            $result->set($section, $array->toArray());
        }
        return $result->toArray();
    }

}