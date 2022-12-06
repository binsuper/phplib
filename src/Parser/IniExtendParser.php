<?php

namespace Gino\Phplib\Parser;

use Gino\Phplib\ArrayObject;
use Gino\Phplib\Error\ParseException;

class IniExtendParser extends Parser {

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

            // check extend
            $extend_result = $this->extends($result, $section);

            $array = new ArrayObject();
            foreach ($sub as $k => $v) {
                if (strpos($k, $section) === 0) {
                    $k = substr($k, strlen($section) + 1);
                }
                $array->set($k, $v);
            }

            $result->set($section, $array->toArray() + $extend_result);
        }
        return $result->toArray();
    }

    /**
     * 继承
     *
     * @param ArrayObject $result
     * @param string $section
     * @return array
     */
    public function extends(ArrayObject $result, string &$section): array {
        $fragments     = explode(':', $section);
        $extend_result = [];
        if (count($fragments) > 1) {
            $section = array_shift($fragments);
            foreach ($fragments as $extend_section) {
                $extend_result += $result->get($extend_section, []);
            }
        }
        return $extend_result;
    }

}