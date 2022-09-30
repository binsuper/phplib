<?php

namespace Gino\Phplib\Parser;


use Gino\Phplib\Error\ParseException;

class ArrayParser extends Parser {

    /**
     * @inheritDoc
     */
    public function parse(string $filepath) {
        $array = include $filepath;
        if (!$array || !is_array($array)) {
            throw new ParseException(sprintf('can not parse php file "%s"', $filepath));
        }
        return $this->fitting($array);
    }

}