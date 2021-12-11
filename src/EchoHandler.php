<?php

declare(strict_types=1);

namespace Antidot\Async\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Webmozart\Assert\Assert;

/**
 * @psalm-import-type FormattedRecord from AbstractProcessingHandler
 */
final class EchoHandler extends AbstractProcessingHandler
{
    /**
     * @param FormattedRecord $record
     */
    protected function write(array $record): void
    {
        $formatted = $record['formatted'];
        Assert::string($formatted);
        if ('cli' === PHP_SAPI) {
            echo $formatted;
        } else {
            fwrite(STDERR, $formatted);
        }
    }
}
