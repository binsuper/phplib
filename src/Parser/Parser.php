<?php

namespace Gino\Phplib\Parser;

use Gino\Phplib\ArrayObject;

abstract class Parser implements IParser {

    /**
     * @param array $data
     * @return array
     */
    protected function fitting(array $data): array {
        $array = new ArrayObject();
        foreach ($data as $k => $v) {
            if (is_array($v) || $v instanceof ArrayObject) {
                $v = $this->fitting($v);
            }
            $array->set($k, $v);
        }
        return $array->toArray();
    }

}