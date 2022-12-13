<?php

namespace Gino\Phplib\Validation;

use Nette\Utils\Strings;
use Nette\Utils\Validators;

class Processor extends Validators {

    protected static $_init = false;

    protected static $_extra = null;

    /**
     * init
     */
    protected static function init() {
        static::$validators += [
            'required' => [static::class, 'isMixed'],
            'enum' => [static::class, 'isEnum'],
            'in'   => [static::class, 'isEnum'],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function is($value, string $expected): bool {
        if (!static::$_init) {
            static::init();
        }

        foreach (explode('|', $expected) as $item) {
            if (substr($item, -2) === '[]') {
                if (is_iterable($value) && self::everyIs($value, substr($item, 0, -2))) {
                    return true;
                }

                continue;
            } elseif (substr($item, 0, 1) === '?') {
                $item = substr($item, 1);
                if ($value === null) {
                    return true;
                }
            }

            [$type] = $item = explode(':', $item, 2);
            static::$_extra = $item[1] ?? null;
            if (isset(static::$validators[$type])) {
                try {
                    if (!static::$validators[$type]($value)) {
                        continue;
                    }
                } catch (\TypeError $e) {
                    continue;
                }
            } elseif ($type === 'pattern') {
                if (Strings::match($value, '|^' . ($item[1] ?? '') . '$|D')) {
                    return true;
                }

                continue;
            } elseif (!$value instanceof $type) {
                continue;
            }

            if (isset($item[1])) {
                $length = $value;
                if (isset(static::$counters[$type])) {
                    $length = static::$counters[$type]($value);
                }

                $range = explode('..', $item[1]);
                if (!isset($range[1])) {
                    $range[1] = $range[0];
                }

                if (($range[0] !== '' && $length < $range[0]) || ($range[1] !== '' && $length > $range[1])) {
                    continue;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * enum
     *
     * @param $value
     * @return bool
     */
    public static function isEnum($value): bool {
        $haystack = explode(',', static::$_extra ?: '');
        return in_array($value, $haystack);
    }

}