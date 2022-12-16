<?php

namespace Gino\Phplib\Log\Processor;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;

abstract class AbstractProcessor implements IProcessor {

    /**
     * @param array $options
     */
    public function initFormatter(HandlerInterface $handler, array $options) {

        $line_format = $options['line-format'] ?? false; // 日志格式
        $line_break  = $options['line-breaks'] ?? false; // 支持换行符
        $line_tidy   = $options['line-tidy'] ?? false; // 忽略空的 contxt 和 extra 信息

        if ($handler instanceof AbstractProcessingHandler) {
            if (false !== $line_format) {
                $handler->setFormatter(new LineFormatter($line_format));
            }

            $formatter = $handler->getFormatter();
            if ($formatter instanceof LineFormatter) {
                $handler->getFormatter()->allowInlineLineBreaks($line_break);
                $handler->getFormatter()->ignoreEmptyContextAndExtra($line_tidy);
            }
        }

    }

}