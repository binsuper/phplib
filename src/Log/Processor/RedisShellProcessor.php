<?php

namespace Gino\Phplib\Log\Processor;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RedisHandler;

class RedisShellProcessor extends AbstractProcessor {

    /** @var \Predis\Client<\Predis\Client>|\Redis */
    protected $redis;

    /**
     * @inheritDoc
     */
    public function init(array $options) {
        $redis = $options['handler'];

        if (is_callable($redis)) {
            $redis = call_user_func($redis);
        }

        $this->redis = $redis;
    }

    /**
     * @inheritDoc
     */
    public function getCreator(array $options): HandlerInterface {
        $key = $options['key'];
        $cap = $options['cap'] ?? 0;

        $level  = $options['level'] ?? \Monolog\Logger::DEBUG;
        $bubble = $options['bubble'] ?? true;

        $handler = new RedisHandler($this->redis, $key, $level, $bubble, $cap);
        $this->initFormatter($handler, $options);
        return $handler;
    }

}