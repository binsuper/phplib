<?php

namespace Gino\Phplib\Log\Processor;

use Monolog\Handler\HandlerInterface;
use \Monolog\Handler\RotatingFileHandler;

class RotatingFileProcessor implements IProcessor {

    private $file_format = '{filename}-{date}';
    private $date_format = RotatingFileHandler::FILE_PER_DAY;

    /**
     * @inheritDoc
     */
    public function init(array $options) {
        isset($options['file_format']) && ($this->date_format = $options['file_format']);
        isset($options['date_format']) && ($this->date_format = $options['date_format']);
    }

    /**
     * @inheritDoc
     */
    public function getCreator(array $options): HandlerInterface {
        $path = $options['path'];
        $days = $options['max'] ?? 10;

        $level      = $options['level'] ?? \Monolog\Logger::DEBUG;
        $bubble     = $options['bubble'] ?? true;
        $permission = $options['chmod'] ?? null;
        $lock       = $options['lock'] ?? false;

        $handler = new RotatingFileHandler($path, $days, $level, $bubble, $permission, $lock);
        $handler->setFilenameFormat($this->file_format, $this->date_format);

        return $handler;
    }

}