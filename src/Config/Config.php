<?php

namespace Gino\Phplib\Config;

use Gino\Phplib\ArrayObject;
use Gino\Phplib\Error\NotFoundException;
use Gino\Phplib\Parser\ArrayParser;
use Gino\Phplib\Config\DomainFinder;
use Gino\Phplib\Config\IFinder;
use Gino\Phplib\Parser\HoconParser;
use Gino\Phplib\Parser\IniParser;
use Gino\Phplib\Parser\IParser;
use Gino\Phplib\Config\SimpleFinder;
use Gino\Phplib\Error\ParseException;
use Gino\Phplib\Parser\JsonParser;
use Gino\Phplib\Parser\TomlParser;
use Gino\Phplib\Parser\XmlParser;
use Gino\Phplib\Parser\YamlParser;

class Config {

    public static $PARSER_SETTING = [
        'php'  => ArrayParser::class,
        'yaml' => YamlParser::class,
        'toml' => TomlParser::class,
        'ini'  => IniParser::class,
        'json' => JsonParser::class,
        'xml'  => XmlParser::class,
    ];

    public static $FINDER_SETTING = [
        DomainFinder::class,
        SimpleFinder::class,
    ];

    private static $__instance = null;

    protected $_data = null;

    protected $root_dir = 'config';

    protected $parsers = [];

    protected $finders = [];


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

    /**
     * @param array $options
     * @return static
     * @throws \Exception
     */
    public static function instance(array $options = []) {
        if (static::$__instance === null) {
            static::$__instance = new static($options);
        }
        return static::$__instance;
    }

    /**
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = []) {
        // option
        $parser_setting = static::$PARSER_SETTING;
        $finder_setting = static::$FINDER_SETTING;

        if (isset($options['parsers'])) {
            $parser_setting = $options['parsers'];
        }
        if (isset($options['finder'])) {
            $finder_setting = $options['finder'];
        }
        if (isset($options['root_dir'])) {
            $this->setRootDir($options['root_dir']);
        }

        // init
        $this->_data = new ArrayObject();

        foreach ($parser_setting as $k => $class) {
            if (!class_implements($class)[IParser::class]) {
                throw new \Exception(sprintf('parser driver "%s" must implements interface "%s"', $class, IParser::class));
            }
            $this->parsers[$k] = new $class();
        }

        foreach (static::$FINDER_SETTING as $k => $class) {
            if (!class_implements($class)[IFinder::class]) {
                throw new \Exception(sprintf('finder driver "%s" must implements interface "%s"', $class, IParser::class));
            }
            $this->finders[$k] = new $class();
        }
    }

    /**
     * set directory path which to search config file in
     *
     * @param string $dir
     */
    public function setRootDir(string $dir) {
        $this->root_dir = $dir;
    }

    /**
     * load config from file
     *
     * @param string $scope
     */
    public function load(string $scope) {
        // finder
        foreach ($this->finders as $finder) {
            $search_files = call_user_func([$finder, 'find'], $this->root_dir, $scope);
            // parser
            foreach ($search_files as $path) {
                foreach ($this->parsers as $suffix => $parser) {
                    $file = $path . '.' . $suffix;
                    if (!is_file($file)) {
                        continue;
                    }
                    try {
                        $this->_data[$scope] = call_user_func([$parser, 'parse'], $file);
                    } catch (\Throwable $ex) {
                        throw new ParseException($ex->getMessage(), $ex->getCode(), $ex);
                    }
                    return;
                }
            }
        }
    }

    /**
     * load config from all config file
     */
    public function loadAll() {
        $support_suffix = array_keys($this->parsers);

        $search_dir = [];
        foreach ($this->finders as $finder) {
            $search_dir = array_unique(array_merge($search_dir, call_user_func([$finder, 'find'], $this->root_dir, '')));
        }

        $load_files = [];
        while ($dir = array_pop($search_dir)) {
            if (!is_dir($dir)) {
                continue;
            }

            // open dir
            $dh = dir($dir);
            if (false === $dh) {
                throw new \RuntimeException(sprintf('can not open dir "%s"', $dir));
            }

            // read dir
            while (false !== ($entry = $dh->read())) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $filepath = $dir . DIRECTORY_SEPARATOR . $entry;
                if (!is_file($filepath)) {
                    continue;
                }

                // check support file
                $suffix = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
                if (!in_array($suffix, $support_suffix)) {
                    continue;
                }

                $scope              = pathinfo($filepath, PATHINFO_FILENAME);
                $load_files[$scope] = [$suffix, $filepath];
            }
        }

        // load file
        try {
            foreach ($load_files as $scope => $fileinfo) {
                $this->_data[$scope] = call_user_func([$this->parsers[$fileinfo[0]], 'parse'], $fileinfo[1]);
            }
        } catch (\Throwable $ex) {
            throw new ParseException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @param $key
     */
    protected function initScope($key) {
        if (is_null($key) || !is_string($key)) return;
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
     * @param mixed $key
     * @param mixed|null $val
     * @return $this
     */
    public function set($key, $val = null) {
//        $this->initScope($key);
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
//        $this->initScope($key);
        return $this->_data->has($key);
    }

    /**
     * delete config
     *
     * @param $key
     * @return $this
     */
    public function del($key) {
//        $this->initScope($key);
        $this->_data->del($key);
        return $this;
    }

}