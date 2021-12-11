<?php

declare(strict_types=1);

namespace Antidot\Test\Async\Logger;

use Antidot\Async\Logger\ReactFilesystemHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Loop;

final class ReactFileSystemHandlerTest extends TestCase
{
    const LOG_PATH = 'test/example.log';

    protected function tearDown(): void
    {
        unlink(self::LOG_PATH);
    }

    public function testItWritesLogInFutureTick(): void
    {
        $loop = Loop::get();
        $logger = new Logger('default');
        $handler = new ReactFilesystemHandler($loop, self::LOG_PATH, Logger::DEBUG, true);
        $logger->pushHandler($handler);
        $logger->debug('Hola Mundo');
        $loop->run();
        $log = file_get_contents(self::LOG_PATH);
        self::assertStringContainsString('Hola Mundo', $log);
    }
}
