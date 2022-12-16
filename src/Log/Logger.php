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
use Gino\Phplib\Log\Processor\IProcessor;
use Gino\Phplib\Log\Processor\RedisShellProcessor;
use Monolog\Handler\RotatingFileHandler;
use Gino\Phplib\Log\Processor\RotatingFileProcessor;

class Logger {

    /** @var array 通道 */
    protected $channels = [];

    /** @var ArrayObject 配置 */
    protected $config = null;

    protected $handler = null;


    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $this->setConfig($config);
    }

    /**
     * 获取配置信息
     *
     * @return ArrayObject
     */
    public function getConfig(): ArrayObject {
        return $this->config;
    }

    /**
     * @param array $config
     * [
     *     'default' => 'app',
     *     'channels' => [
     *         'app' => [
     *              'driver' => 'daily',
     *              'path'   => 'logs/app.log',
     *              'level'  => 'error',
     *              'days'   => 15
     *         ]
     *     ],
     *     'drivers' => [
     *          'daily' => [
     *              'class' => ''
     *          ]
     *     ]
     * ]
     *
     * @return $this
     */
    public function setConfig(array $config) {
        $default            = static::getDefaultOptions();
        $config['default']  = $config['default'] ?? $default['default'];
        $config['channels'] = $config['channels'] ?? $default['channels'];
        $config['drivers']  = ($config['drivers'] ?? []) + $default['drivers'];

        $this->config = new ArrayObject($config);

        return $this;
    }

    /**
     * 默认配置
     *
     * @return array[]
     */
    public static function getDefaultOptions(): array {
        return [
            'default'  => 'default',
            'channels' => [
                'default' => [
                    'driver' => 'daily',
                    'path'   => 'default.log',
                ]
            ],
            'drivers'  => [
                'daily'   => [
                    'class'       => RotatingFileProcessor::class,
                    'date_format' => RotatingFileHandler::FILE_PER_DAY,
                ],
                'monthly' => [
                    'class'       => RotatingFileProcessor::class,
                    'date_format' => RotatingFileHandler::FILE_PER_MONTH,
                ],
                'yearly'  => [
                    'class'       => RotatingFileProcessor::class,
                    'date_format' => RotatingFileHandler::FILE_PER_YEAR,
                ]
            ]
        ];
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
            $channel = $this->config->get('default');
        }
        $key     = 'channels.' . $channel;
        $options = $this->config->get($key, false);
        if (false === $options) {
            throw BadConfigurationException::miss($key);
        }

        $this->channels[$channel] = $this->channelFactory($channel, $options);
        return $this->channels[$channel];
    }

    /**
     * 日志实例工厂
     *
     * @param string $channel
     * @param array $option
     * @return \Monolog\Logger
     * @throws BadConfigurationException
     */
    protected function channelFactory(string $channel, array $option): \Monolog\Logger {
        // make it support multi handler
        if (!isset($option[0])) {
            $options = [$option];
        } else {
            $options = $option;
        }

        $handlers = [];
        foreach ($options as $option) {
            $driver   = $option['driver'];
            $callback = $option['callback'] ?? false;

            // driver
            $key            = 'drivers.' . $driver;
            $driver_options = $this->config->get($key, false);
            if (false === $driver_options) {
                throw BadConfigurationException::miss($key);
            }

            $driver_callback = $driver_options['callback'] ?? false;
            $driver          = $driver_options['class'] ?? false;
            if (!$driver || !is_a($driver, IProcessor::class, true)) {
                throw new BadConfigurationException(sprintf('driver only support an type of %s, but %s given', IProcessor::class, $driver));
            }

            // handler
            /** @var IProcessor $driver */
            $driver = new $driver();
            $driver->init($driver_options);
            $handler = $driver->getCreator($option);

            // driver callback
            if (is_callable($driver_callback)) {
                call_user_func($driver_callback, $handler);
            }

            // options callback
            if (is_callable($callback)) {
                call_user_func($callback, $handler);
            }

            // push
            $handlers[] = $handler;
        }

        // logger
        $logger = new \Monolog\Logger($channel);
        $logger->setHandlers($handlers);

        return $logger;
    }

}