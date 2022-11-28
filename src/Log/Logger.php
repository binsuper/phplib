<?php
/**
 * config struct example:
 * -----------------------------------
 * [
 *     // 默认日志通道
 *     'default'  => 'app',
 *
 *     // 日志通道
 *     'channels' => [
 *             'app' => [
 *             'driver' => 'daily',
 *             'path'   => 'logs/app.log',
 *             'level'  => 'debug',
 *             'days'   => 15,
 *         ],
 *     ],
 * ];
 * -----------------------------------
 */


namespace Gino\Phplib\Log;

use Gino\Phplib\ArrayObject;
use Gino\Phplib\Error\BadConfigurationException;
use Monolog\Handler\RotatingFileHandler;

class Logger {

    const DRIVER_DAILY   = 'daily';
    const DRIVER_MONTHLY = 'monthly';
    const DRIVER_YEARLY  = 'yearly';

    /** @var array 通道 */
    protected $channels = [];

    /** @var ArrayObject 配置 */
    protected $config = [];

    protected $handler = null;


    public function __construct(array $config) {
        $this->config = new ArrayObject($config);
    }

    /**
     * 返回日志通道
     *
     * @param string $channel
     * @return \Monolog\Logger
     * @throws BadConfigurationException
     */
    public function channel(string $channel = ''): \Monolog\Logger {
        if (isset($this->channels[$channel])) {
            return $this->channels[$channel];
        }

        if ('' === $channel) {
            $channel = $this->config->get('logger.default');
        }
        $key = 'logger.channels.' . $channel;
        $cfg = $this->config->get($key, false);
        if (false === $cfg) {
            throw BadConfigurationException::miss($key);
        }

        $this->channels[$channel] = static::channelFactory($channel, $cfg);
        return $this->channels[$channel];
    }

    /**
     * 日志实例工厂
     *
     * @param string $channel
     * @param array $cfg
     * @return \Monolog\Logger
     * @throws BadConfigurationException
     */
    protected static function channelFactory(string $channel, array $cfg): \Monolog\Logger {
        $driver   = $cfg['driver'];
        $callback = $cfg['callback'] ?? false;
        $formatter = $cfg['formatter'] ?? false;

        // get handler
        $method = $driver . 'Creator';
        if (!method_exists(static::class, $method)) {
            throw BadConfigurationException::invalid($driver);
        }
        $handler = forward_static_call([static::class, $method], $cfg);
        if (is_callable($callback)) {
            call_user_func($callback, $handler);
        }

        // logger
        $logger = new \Monolog\Logger($channel);
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * 支持按每天切割日志文件
     *
     * @param array $cfg
     * @return RotatingFileHandler
     */
    protected static function dailyCreator(array $cfg) {
        $path  = $cfg['path'];
        $level = $cfg['level'] ?? \Monolog\Logger::DEBUG;
        $days  = $cfg['max'] ?? 10;

        $handler = new RotatingFileHandler($path, $days, $level);
        return $handler;
    }

    /**
     * 支持按每月切割日志文件
     *
     * @param array $cfg
     * @return RotatingFileHandler
     */
    protected static function monthlyCreator(array $cfg) {
        $path  = $cfg['path'];
        $level = $cfg['level'] ?? \Monolog\Logger::DEBUG;
        $days  = $cfg['max'] ?? 10;

        $handler = new RotatingFileHandler($path, $days, $level);
        $handler->setFilenameFormat('{filename}-{date}', RotatingFileHandler::FILE_PER_MONTH);
        return $handler;
    }

    /**
     * 支持按每年切割日志文件
     *
     * @param array $cfg
     * @return RotatingFileHandler
     */
    protected static function yearlyCreator(array $cfg) {
        $path  = $cfg['path'];
        $level = $cfg['level'] ?? \Monolog\Logger::DEBUG;
        $days  = $cfg['max'] ?? 10;

        $handler = new RotatingFileHandler($path, $days, $level);
        $handler->setFilenameFormat('{filename}-{date}', RotatingFileHandler::FILE_PER_YEAR);
        return $handler;
    }


}