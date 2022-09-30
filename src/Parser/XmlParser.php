<?php

namespace Gino\Phplib\Parser;

use Gino\Phplib\Error\ParseException;

class XmlParser extends Parser {

    /**
     * @inheritDoc
     */
    public function parse(string $filepath) {

        $data = simplexml_load_file($filepath, null, LIBXML_NOCDATA);
        if (false === $data) {
            throw new ParseException(sprintf('can not parse xml file "%s"', $filepath));
        }

        $array = json_decode(json_encode($data), true);
        $array = $this->fitting($array);
        return $array;
    }


}