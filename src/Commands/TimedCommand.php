<?php

namespace Chiiya\Common\Commands;

use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;

class TimedCommand extends Command
{
    protected LoggerInterface $logger;
    protected ?float $start = null;
    protected ?string $time = null;

    /**
     * TimedCommand constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    /**
     * Log an error to console and application logfiles.
     */
    protected function log(string $message): void
    {
        $this->error($message);
        $this->logger->error("`{$this->getName()}` command: {$message}");
    }

    /**
     * Start the command execution.
     */
    protected function start(): void
    {
        $this->start = microtime(true);
    }

    /**
     * End the execution, calculate execution time.
     */
    protected function end(): void
    {
        $end = microtime(true);
        $this->time = number_format($end - $this->start, 2);
    }

    /**
     * Print total and average execution time.
     */
    protected function printAverageExecutionTime(int $count): void
    {
        $this->end();
        $average = number_format($this->time / $count, 2);
        $this->info("\nAll items have been processed.");
        $this->info("Execution time: {$this->time}s (average of {$average}s per item).");
    }
}
