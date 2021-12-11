<?php

declare(strict_types=1);

namespace Antidot\Test\Async\Logger;

use Antidot\Async\Logger\EchoHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class EchoHandlerTest extends TestCase
{
    public function testItEchoesLogsInSTDERROutput(): void
    {
        $logger = new Logger('default');
        $handler = new EchoHandler(Logger::DEBUG, true);
        $logger->pushHandler($handler);
        ob_start();
        $logger->debug('Hola Mundo');
        $outputContent = ob_get_clean();
        self::assertStringContainsString('Hola Mundo', $outputContent);
    }
}
