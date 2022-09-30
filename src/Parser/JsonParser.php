<?php

namespace Gino\Phplib\Parser;

use Gino\Phplib\Error\ParseException;
use Symfony\Component\Yaml\Yaml;

class JsonParser extends Parser {

    /**
     * @inheritDoc
     */
    public function parse(string $filepath) {
        $string = file_get_contents($filepath);
        if (false === $string) {
            throw new ParseException(sprintf('can not parse json file "%s"', $filepath));
        }
        $data = json_decode($string, true);
        if (!is_array($data)) {
            throw new ParseException(sprintf('can not parse json file "%s"', $filepath));
        }

        return $this->fitting($data);
    }

}