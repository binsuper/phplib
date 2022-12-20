<?php

namespace Gino\Phplib\Log\Processor;

use Monolog\Handler\HandlerInterface;
use \Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class RotatingFileProcessor extends AbstractProcessor {

    private $file_format;
    private $date_format;

    /**
     * @inheritDoc
     */
    public function init(array $options) {
        $this->file_format = $this->convert($options['file_format'] ?? '{filename}-{date}');
        $this->date_format = $this->convert($options['date_format'] ?? RotatingFileHandler::FILE_PER_DAY);
    }

    /**
     * @inheritDoc
     */
    public function getCreator(array $options): HandlerInterface {
        $path = $this->convert($options['path']);
        $days = $this->convert($options['max'] ?? 0);

        $level      = $this->convert($options['level'] ?? Logger::DEBUG);
        $bubble     = $this->convert($options['bubble'] ?? true);   // 冒泡
        $permission = $this->convert($options['chmod'] ?? null);    // 文件权限
        $lock       = $this->convert($options['lock'] ?? false);

        $handler = new RotatingFileHandler($path, $days, $level, $bubble, $permission, $lock);

        if ($this->file_format && $this->date_format) {
            $handler->setFilenameFormat($this->file_format, $this->date_format);
        }

        $this->initFormatter($handler, $options);
        return $handler;
    }

}