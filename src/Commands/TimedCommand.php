<?php declare(strict_types=1);

namespace Chiiya\Common\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TimedCommand extends Command
{
    protected ?float $start = null;
    protected ?string $time = null;

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->start();
        $result = parent::execute($input, $output);
        $this->end();
        $this->info("Execution time: {$this->time}s");

        return $result;
    }

    /**
     * Log an error to console and application logfiles.
     */
    protected function log(string $message): void
    {
        $this->error($message);
        Log::error("`{$this->getName()}` command: {$message}");
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
}
