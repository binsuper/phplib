<?php

namespace Gino\Phplib\Log\Processor;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RedisHandler;
use Monolog\Logger;

class RedisShellProcessor extends AbstractProcessor {

    /** @var \Predis\Client<\Predis\Client>|\Redis */
    protected $redis;

    /**
     * @inheritDoc
     */
    public function init(array $options) {
        $redis = $this->convert($options['handler']);

        $this->redis = $redis;
    }

    /**
     * @inheritDoc
     */
    public function getCreator(array $options): HandlerInterface {
        $key = $this->convert($options['key']);
        $cap = $this->convert($options['cap'] ?? 0);

        $level  = $this->convert($options['level'] ?? Logger::DEBUG);
        $bubble = $this->convert($options['bubble'] ?? true);

        $handler = new RedisHandler($this->redis, $key, $level, $bubble, $cap);
        $this->initFormatter($handler, $options);
        return $handler;
    }

}