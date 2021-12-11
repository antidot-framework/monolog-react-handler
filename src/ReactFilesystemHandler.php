<?php

declare(strict_types=1);

namespace Antidot\Async\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Psr\Log\LogLevel;
use React\EventLoop\LoopInterface;
use React\Filesystem\Factory;
use React\Filesystem\Node\FileInterface;
use React\Filesystem\Node\NotExistInterface;
use React\Promise\ExtendedPromiseInterface;
use React\Promise\PromiseInterface;
use Webmozart\Assert\Assert;
use const FILE_APPEND;

/**
 * @psalm-import-type Level from \Monolog\Logger
 * @psalm-import-type LevelName from \Monolog\Logger
 * @psalm-import-type FormattedRecord from AbstractProcessingHandler
 */
final class ReactFilesystemHandler extends AbstractProcessingHandler
{
    private PromiseInterface $filesystem;
    private string $logPath;
    private LoopInterface $loop;

    /**
     * @psalm-param Level|LevelName|LogLevel::* $debug
     */
    public function __construct(LoopInterface $loop, string $logPath, int|string $debug, bool $bubble)
    {
        $this->loop = $loop;
        $this->logPath = $logPath;
        $filesystem = Factory::create();
        /** @psalm-suppress TooManyTemplateParams */
        $this->filesystem = $filesystem->detect($this->logPath)
            ->then(static function (NotExistInterface $node): PromiseInterface {
                /** @psalm-suppress TooManyTemplateParams */
                return $node->createFile();
            });

        parent::__construct($debug, $bubble);
    }

    /**
     * @param FormattedRecord $record
     */
    protected function write(array $record): void
    {
        $this->loop->futureTick(function () use ($record) {
            $this->filesystem
                ->then(static function (FileInterface $file) use ($record): PromiseInterface {
                    $formatted = $record['formatted'];
                    Assert::string($formatted);
                    /** @psalm-suppress TooManyTemplateParams */
                    return $file->putContents($formatted, FILE_APPEND);
                });
        });
    }

    public function close(): void
    {
        if ($this->filesystem instanceof ExtendedPromiseInterface) {
            $this->filesystem->done();
        }
    }
}
