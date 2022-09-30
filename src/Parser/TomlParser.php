<?php

namespace Gino\Phplib\Parser;

use Gino\Phplib\Error\ParseException;
use Yosymfony\Toml\Toml;

class TomlParser extends Parser {

    /**
     * @inheritDoc
     */
    public function parse(string $filepath) {
        $data = Toml::ParseFile($filepath);
        if (false === $data) {
            throw new ParseException(sprintf('can not parse yaml file "%s"', $filepath));
        }

        return $data;
    }

}