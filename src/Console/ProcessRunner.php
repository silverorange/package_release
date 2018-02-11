<?php

namespace Silverorange\PackageRelease\Console;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Silverorange\PackageRelease\Console\Formatter\LineWrapper;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class ProcessRunner
{
    /**
     * @var string
     */
    protected $starting_message = '';

    /**
     * @var string
     */
    protected $success_message = '';

    /**
     * @var string
     */
    protected $failure_message = '';

    /**
     * @var Symfony\Component\Console\Output\OutputInterface
     */
    protected $output = null;

    /**
     * @var Symfony\Component\Process\Process
     */
    protected $process = null;

    public function __construct(
        OutputInterface $output,
        string $command,
        string $starting_message,
        string $success_message,
        string $failure_message
    ) {
        $this->process = new Process(
            $command,
            null,
            null,
            null,
            null // disable timeout
        );
        $this->output = $output;
        $this->starting_message = $starting_message;
        $this->success_message = $success_message;
        $this->failure_message = $failure_message;
    }

    public function run(): bool
    {
        $this->start();
        $this->process->run();

        if ($this->process->isSuccessful()) {
            $this->success();
        } else {
            $this->failure();
        }

        return $this->process->isSuccessful();
    }

    protected function start(): void
    {
        $this->output->write(
            sprintf(
                '<waiting>…</waiting> %s',
                $this->starting_message
            )
        );
    }

    protected function success(): void
    {
        $this->clearStartingMessage();

        $this->output->writeln(
            sprintf(
                "\r<success>✓</success> %s",
                $this->success_message
            )
        );
    }

    protected function failure(): void
    {
        $this->clearStartingMessage();

        $this->output->writeln([
            sprintf(
                "\r<failure>✗</failure> %s",
                $this->failure_message
            ),
            ''
        ]);

        $debug_output = explode(PHP_EOL, $this->process->getOutput())
            + explode(PHP_EOL, $this->process->getErrorOutput());

        // strip escape codes
        $debug_output = array_map(function ($line) {
            return preg_replace('/\x1b\[[0-?]*[ -\/]*[@-~]/i', '', $line);
        }, $debug_output);

        $wrapped_lines = (new LineWrapper())->wrap($debug_output);

        $this->output->writeln(array_map(function ($line) {
            return sprintf(
                '<output>%s</output>',
                OutputFormatter::escape($line)
            );
        }, $wrapped_lines));
        $this->output->writeln('');
    }

    protected function clearStartingMessage(): void
    {
        $this->output->write(
            str_repeat(
                "\x08",
                mb_strlen($this->starting_message)
            )
        );

        $this->output->write(
            str_repeat(
                ' ',
                mb_strlen($this->starting_message)
            )
        );
    }
}
