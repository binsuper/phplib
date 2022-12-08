<?php

namespace Gino\Phplib;

class ArrayObject extends \ArrayObject {

    /**
     * @param array $array
     * @return ArrayObject
     */
    public static function from(array $array): ArrayObject {
        return new static($array);
    }

    public function toArray(): array {
        return (array)$this;
    }

    /**
     * @param mixed $key
     * @param mixed|null $val
     * @return $this
     */
    public function set($key, $val = null) {
        if (is_null($key)) {
            return $this;
        }

        if (is_string($key)) {

            if (isset($this[$key])) {
                $this[$key] = $val;
                return $this;
            }

            $keys  = explode('.', $key);
            $array = $this;
            foreach ($keys as $i => $k) {
                if (count($keys) === 1) {
                    break;
                }
                unset($keys[$i]);

                if (!isset($array[$k]) || !is_array($array[$k])) {
                    $array[$k] = [];
                }

                $array = &$array[$k];
            }
            $array[array_shift($keys)] = $val;
            return $this;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
            return $this;
        }

        $this[$key] = $val;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function all() {
        return $this->get(null);
    }

    /**
     * @param mixed|null $key
     * @param mixed|null $def
     * @return mixed|null
     */
    public function get($key, $def = null) {
        if (is_null($key)) {
            return $this->toArray();
        }

        if (is_string($key)) {
            if (isset($this[$key])) {
                return $this[$key];
            }
            if (strpos($key, '.') === false) {
                return $this[$key] ?? $def;
            }
            $arr = $this;
            foreach (explode('.', $key) as $k) {
                if (isset($arr[$k])) {
                    $arr = $arr[$k];
                } else {
                    return $def;
                }
            }
            return $arr;
        }

        // multi get
        if (is_array($key)) {
            $a = array_map(function ($k) {
                return $this->get($k);
            }, $key);
            return $a;
        }

        return $this[$key] ?? $def;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key): bool {
        if (empty($key)) {
            return false;
        }

        if (is_string($key)) {
            $arr = $this;
            if (isset($this[$key])) {
                return true;
            }
            foreach (explode('.', $key) as $k) {
                if (isset($arr[$k])) {
                    $arr = $arr[$k];
                } else {
                    return false;
                }
            }
            return true;
        }

        return isset($item[$key]);
    }

    /**
     * @param $key
     * @return $this|ArrayObject
     */
    public function del($key) {
        if (empty($key)) {
            return $this;
        }

        if (is_string($key)) {
            if (isset($this[$key])) {
                unset($this[$key]);
                return $this;
            }
            $keys  = explode('.', $key);
            $array = $this;
            foreach ($keys as $i => $k) {
                if (count($keys) === 1) {
                    break;
                }
                unset($keys[$i]);

                if (!isset($array[$k]) || !is_array($array[$k])) {
                    $array[$k] = [];
                }

                $array = &$array[$k];
            }
            unset($array[array_shift($keys)]);
            return $this;
        }

        unset($this[$key]);
        return $this;
    }

}