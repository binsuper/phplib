<?php

namespace Gino\Phplib\Error;

class BadConfigurationException extends \Exception {

    const TYPE_MISS    = 1;
    const TYPE_INVALID = 2;

    /**
     * configuration is not found
     *
     * @param $key
     * @return static
     */
    public static function miss($key) {
        return new static(sprintf('configuration <%s> is not found', $key), static::TYPE_MISS);
    }

    /**
     * configuration is invalid
     *
     * @param $key
     * @return static
     */
    public static function invalid($key) {
        return new static(sprintf('configuration <%s> is invalid', $key), static::TYPE_INVALID);
    }

}