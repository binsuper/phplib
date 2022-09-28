<?php

namespace Gino\Phplib\Config;

use Gino\Phplib\ArrayObject;
use Gino\Phplib\Config\Parser\ArrayParser;
use Gino\Phplib\Config\Parser\IFinder;
use Gino\Phplib\Config\Parser\IniParser;
use Gino\Phplib\Config\Parser\IParser;
use Gino\Phplib\Config\Parser\SimpleFinder;

class Config {

    public static $PARSER_SETTING = [
        'php' => ArrayParser::class,
        'ini' => IniParser::class,
    ];

    public static $FINDER_SETTING = [
        SimpleFinder::class
    ];

    protected $_data = null;

    public $cfg_load_dir = 'config';

    public $parsers = [];

    public $finders = [];


    /**
     * add parser
     *
     * @param string $suffix suffix of file
     * @param string $class IParse class name
     */
    public static function addParser(string $suffix, string $class) {
        static::$PARSER_SETTING[$suffix] = $class;
    }

    /**
     * add finder
     *
     * @param string $suffix suffix of file
     * @param string $class IFinder class name
     */
    public static function addFinder(string $suffix, string $class) {
        static::$FINDER_SETTING[] = $class;
    }

    public function __construct() {
        $this->_data = new ArrayObject();

        foreach (static::$PARSER_SETTING as $k => $class) {
            if (!class_implements($class)[IParser::class]) {
                throw new \Exception("parser driver($class) must implements class(" . IParser::class . ')');
            }
            $this->parsers[$k] = new $class();
        }

        foreach (static::$FINDER_SETTING as $k => $class) {
            if (!class_implements($class)[IFinder::class]) {
                throw new \Exception("finder driver($class) must implements class(" . IFinder::class . ')');
            }
            $this->finders[$k] = new $class();
        }
    }

    /**
     * set directory path which to search config file in
     *
     * @param string $dir
     */
    public function setCfgLoadDir(string $dir) {
        $this->cfg_load_dir = $dir;
    }

    /**
     * load config from file
     *
     * @param string $scope
     */
    protected function load(string $scope) {
        // finder
        foreach ($this->finders as $finder) {
            $filepath = call_user_func([$finder, 'find'], $this->cfg_load_dir, $scope);
            if (!empty($filepath)) {
                break;
            }
        }

        // parser
        foreach ($this->parsers as $suffix => $parser) {
            $file = $filepath . '.' . $suffix;
            if (!file_exists($file)) {
                continue;
            }
            $this->_data[$scope] = call_user_func([$parser, 'load'], $file);
            return;
        }
    }

    /**
     * @param $key
     */
    protected function initScope($key) {
        if (is_null($key)) return;
        $keys  = explode('.', $key);
        $scope = array_shift($keys);
        if (!$this->_data->has($scope)) {
            $this->load($scope);
        }
    }

    /**
     * get config
     *
     * @param null $key
     * @param null $def
     * @return array|ArrayObject|mixed|null
     */
    public function get($key = null, $def = null) {
        $this->initScope($key);
        return $this->_data->get($key, $def);
    }

    /**
     * set config
     *
     * @param $key
     * @param $val
     * @return $this
     */
    public function set($key, $val) {
        $this->initScope($key);
        $this->_data->set($key, $val);
        return $this;
    }

    /**
     * check key exists in config
     *
     * @param $key
     * @return bool
     */
    public function has($key) {
        $this->initScope($key);
        return $this->_data->has($key);
    }

    /**
     * delete config
     *
     * @param $key
     * @return $this
     */
    public function del($key) {
        $this->initScope($key);
        $this->_data->del($key);
        return $this;
    }

}