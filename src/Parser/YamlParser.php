<?php

namespace Gino\Phplib\Parser;

use Gino\Phplib\Error\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlParser extends Parser {

    /**
     * @inheritDoc
     */
    public function parse(string $filepath) {

        $data = Yaml::parseFile($filepath);

        if (false === $data) {
            throw new ParseException(sprintf('can not parse yaml file "%s"', $filepath));
        }

        return $this->fitting($data);
    }

}