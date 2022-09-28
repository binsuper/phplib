<?php

namespace Gino\Phplib\Config\Parser;


class ArrayParser implements IParser {

    /**
     * @inheritDoc
     */
    public function load(string $filepath) {
        return include $filepath;
    }

}