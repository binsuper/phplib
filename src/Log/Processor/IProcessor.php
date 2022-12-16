<?php

namespace Gino\Phplib\Log\Processor;

use Monolog\Handler\HandlerInterface;

interface IProcessor {

    /**
     * init creator
     *
     * @param array $options
     * @return mixed
     */
    public function init(array $options);

    /**
     * create an object of handler
     *
     * @param array $options
     * @return HandlerInterface
     */
    public function getCreator(array $options): HandlerInterface;

}