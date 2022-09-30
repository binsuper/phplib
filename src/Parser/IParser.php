<?php

namespace Gino\Phplib\Parser;

interface IParser {

    /**
     * @param string $filepath
     * @param bool $forObject
     * @param object|null $object
     * @return array
     */
    public function parse(string $filepath);

}