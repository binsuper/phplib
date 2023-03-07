<?php

namespace Gino\Phplib;

use Closure;

class ArrayObject extends \ArrayObject {

    /** @var string 键名分隔字符 */
    private $key_separator = '.';

    /** @var int 分隔深度 */
    private $key_separation_deep = 0;

    /**
     * @param array $array
     * @return static
     */
    public static function from(array $array): self {
        return new static($array);
    }

    /**
     * @param array $array
     * @return static
     */
    public static function with(array $array): self {
        return new static($array);
    }

    /**
     * 设置键名解析的分隔符
     *
     * @param string $separator
     * @return static
     */
    public function setSeparator(string $separator): self {
        $this->key_separator = $separator;
        return $this;
    }

    /**
     * 设置键名解析的深度
     *
     * @param int $level 深度，0为不限制
     * @return static
     */
    public function setSeparatorLevel(int $level): self {
        $this->key_separation_deep = $level;
        return $this;
    }

    /**
     * 解析键名并执行操作
     *
     * @param string $key
     * @param Closure|null $operator
     * @return mixed
     */
    protected function operate(string $key, ?Closure $operator = null) {
        if (isset($this[$key])) {
            return $operator([], $key);
        }

        $keys    = explode($this->key_separator, $key);
        $deep    = $this->key_separation_deep;
        $struct  = $this;
        $section = [];
        $leaf    = $key;

        while ($k = array_shift($keys)) {
            // last
            if (count($keys) == 0) {
                $leaf = $k;
                break;
            }

            // is not array, then set the leave keys to one
            if (isset($struct[$k])) {
                if (!is_array($struct[$k])) {
                    $leaf = $k . $this->key_separator . implode($this->key_separator, $keys);
                    break;
                }
                $struct = &$struct[$k];

                $child_key = implode($this->key_separator, $keys);
                if (isset($struct[$child_key])) {
                    array_push($section, $k);
                    $leaf = $child_key;
                    break;
                }
            }

            array_push($section, $k);

            if (--$deep == 0) {
                $leaf = implode($this->key_separator, $keys);
                break;
            }
        }

        return $operator($section, $leaf);
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array {
        return (array)$this;
    }

    /**
     * @param mixed $key
     * @param mixed|null $val
     * @return static
     */
    public function set($key, $val = null): self {

        if (is_null($key) || $key === '') {
            return $this;
        }

        if (is_string($key)) {
            $this->operate($key, function ($root, $leaf) use ($val) {
                $struct = $this;
                while ($branch = array_shift($root)) {
                    $struct = &$struct[$branch];
                }
                $struct[$leaf] = $val;
            });

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
            return $this->operate($key, function ($root, $leaf) use ($def) {
                $struct = $this;
                while ($branch = array_shift($root)) {
                    if (!isset($struct[$branch])) {
                        return $def;
                    }
                    $struct = &$struct[$branch];
                }
                return $struct[$leaf] ?? $def;
            });
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
            return $this->operate($key, function ($root, $leaf) {
                $struct = $this;
                while ($branch = array_shift($root)) {
                    if (!isset($struct[$branch])) {
                        return false;
                    }
                    $struct = &$struct[$branch];
                }
                return isset($struct[$leaf]);
            });
        }

        return isset($this[$key]);
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
            $this->operate($key, function ($root, $leaf) {
                $struct = $this;
                while ($branch = array_shift($root)) {
                    if (!isset($struct[$branch])) {
                        return $this;
                    }
                    $struct = &$struct[$branch];
                }
                if (isset($struct[$leaf])) unset($struct[$leaf]);
            });
            return $this;
        }

        unset($this[$key]);
        return $this;
    }

}