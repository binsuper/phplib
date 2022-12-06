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

    public const OPT_ROOT_DIR = 'root_dir';
    public const OPT_PARSERS  = 'parsers';
    public const OPT_FINDERS  = 'finders';

    /** @var array default option */
    public const DEFAULT_OPTIONS = [
        self::OPT_PARSERS  => self::DEFAULT_PARSER_SETTING,
        self::OPT_FINDERS  => self::DEFAULT_FINDER_SETTING,
        self::OPT_ROOT_DIR => 'config',
    ];

    public const DEFAULT_PARSER_SETTING = [
        'php'  => ArrayParser::class,
        'yaml' => YamlParser::class,
        'toml' => TomlParser::class,
        'ini'  => IniParser::class,
        'json' => JsonParser::class,
        'xml'  => XmlParser::class,
    ];

    public const DEFAULT_FINDER_SETTING = [
        DomainFinder::class,
        SimpleFinder::class,
    ];

    /**
     * @var array<static>
     */
    private static $__instance = [];

    /**
     * @var ArrayObject|null
     */
    protected $_data = null;

    protected $root_dir = 'config';

    protected $parsers = [];

    protected $finders = [];

    /**
     * regist singleton object
     *
     * @param string $name
     * @param array $options
     * @return static
     * @throws \Exception
     */
    public static function registe(string $name, array $options = []) {
        static::$__instance[$name] = new static($options);
        return static::$__instance[$name];
    }

    /**
     * 获取单例
     *
     * @param string $name
     * @return static
     * @throws \Exception
     */
    public static function instance(string $name = '__MAIN__') {
        return static::$__instance[$name] ?? static::registe($name);
    }

    /**
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = []) {
        $options = array_merge(self::DEFAULT_OPTIONS, $options);

        // option
        $parser_setting = $options[self::OPT_PARSERS];
        $finder_setting = $options[self::OPT_FINDERS];
        $this->setRootDir($options[self::OPT_ROOT_DIR]);

        // init
        $this->_data = new ArrayObject();

        foreach ($parser_setting as $k => $class) {
            if (!class_implements($class)[IParser::class]) {
                throw new \Exception(sprintf('parser driver "%s" must implements interface "%s"', $class, IParser::class));
            }
            $this->parsers[$k] = new $class();
        }

        foreach ($finder_setting as $k => $class) {
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
        $this->root_dir = trim(rtrim($dir, DIRECTORY_SEPARATOR));
    }

    /**
     * read file and return the config
     *
     * @param string $filepath
     * @param string $suffix
     * @return array|null
     * @throws ParseException
     */
    public function readFile(string $filepath, string $suffix = ''): ?array {
        if (empty($suffix)) {
            $suffix = ($info = pathinfo($filepath))['extension'] ?? '';
            $file   = $filepath;
        } else {
            $file = $filepath . '.' . $suffix;
        }

        if (!is_file($file)) {
            echo $file . PHP_EOL;
            return null;
        }

        $parser = $this->parsers[$suffix] ?? false;
        if (!$parser) {
            return null;
        }

        try {
            $data = call_user_func([$parser, 'parse'], $file);
        } catch (\Throwable $ex) {
            throw new ParseException($ex->getMessage(), $ex->getCode(), $ex);
        }

        return $data ?? [];
    }

    /**
     * load config from file
     *
     * @param string $scope
     * @return $this
     * @throws ParseException
     */
    public function load(string $scope) {
        // finder
        foreach ($this->finders as $finder) {
            $search_files = call_user_func([$finder, 'find'], $this->root_dir, $scope);
            // parser
            foreach ($search_files as $path) {
                foreach ($this->parsers as $suffix => $parser) {
                    $this->_data->set($scope, $this->readFile($path, $suffix) ?: []);
                    return $this;
                }
            }
        }

        return $this;
    }

    /**
     * load config from all config file
     *
     * @return $this
     * @throws ParseException
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

                $scope = pathinfo($filepath, PATHINFO_FILENAME);

                if (isset($load_files[$scope])) {
                    continue;
                }

                $load_files[$scope] = [$suffix, $filepath];
            }
        }

        // load file
        try {
            foreach ($load_files as $scope => $finfo) {
                list($suffix, $filepath) = $finfo;
                $this->_data->set($scope, $this->readFile($filepath) ?: []);
            }
        } catch (\Throwable $ex) {
            throw new ParseException($ex->getMessage(), $ex->getCode(), $ex);
        }

        return $this;
    }

    /**
     * @param $key
     * @return $this|void
     * @throws ParseException
     */
    protected function initScope($key) {
        if (is_null($key) || !is_string($key)) return;
        $keys  = explode('.', $key);
        $scope = array_shift($keys);
        if (!$this->_data->has($scope)) {
            $this->load($scope);
        }
        return $this;
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
        return $this->_data->has($key);
    }

    /**
     * delete config
     *
     * @param $key
     * @return $this
     */
    public function del($key) {
        $this->_data->del($key);
        return $this;
    }

}