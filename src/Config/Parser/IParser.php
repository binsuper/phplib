<?php

namespace Gino\Phplib\Config\Parser;

interface IParser {

    /**
     * @param string $filepath
     * @param bool $forObject
     * @param object|null $object
     * @return array
     */
    public function load(string $filepath);

}