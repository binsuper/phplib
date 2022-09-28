<?php

namespace Gino\Phplib;

class ArrayObject extends \ArrayObject {

    public function toArray(): array {
        return (array)$this;
    }

    /**
     * @param mixed $key
     * @param mixed $val
     * @return $this
     */
    public function set($key, $val) {
        if (is_null($key)) {
            return $this;
        }

        if (is_string($key)) {
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

        $this[$key] = $val;
        return $this;
    }

    /**
     * @return $this|array|ArrayObject|mixed|null
     */
    public function all() {
        return $this->get(null);
    }

    /**
     * @param mixed|null $key
     * @param mixed|null $def
     * @return $this|ArrayObject|mixed|null
     */
    public function get($key, $def = null) {
        if (is_null($key)) {
            return $this->toArray();
        }

        if (is_string($key)) {
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
        if (is_callable($key)) {
            $array = [];
            foreach ($this as $k => $v) {
                if (call_user_func($key, $v, $k) === true) {
                    $array[$k] = $v;
                }
            }
            return $array;
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
            foreach (explode('.', $key) as $k) {
                if (isset($arr[$k])) {
                    $arr = $arr[$k];
                } else {
                    return false;
                }
            }
            return true;
        }
        if (is_callable($key)) {
            foreach ($this as $k => $v) {
                if (call_user_func($key, $k, $v) === true) {
                    return true;
                }
            }
            return false;
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

        if (is_callable($key)) {
            foreach ($this as $k => $v) {
                if (call_user_func($key, $k, $v) === true) {
                    unset($this[$k]);
                }
            }
            return $this;
        }

        unset($this[$key]);
        return $this;
    }

}