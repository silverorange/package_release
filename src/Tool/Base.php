<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Silverorange\PackageRelease\Console\Formatter\LineWrapper;

abstract class Base
{
    protected static function start(
        OutputInterface $output,
        string $message
    ): void {
        $output->write(sprintf('<waiting>…</waiting> %s', $message));
    }

    protected static function success(
        OutputInterface $output,
        string $message
    ): void {
        $output->writeln(
            sprintf(
                "\r<success>✓</success> %s",
                $message
            )
        );
    }

    protected static function fail(
        OutputInterface $output,
        string $message,
        array $debug_output
    ): void {
        $output->writeln([
            sprintf(
                "\r<failure>✗</failure> %s",
                $message
            ),
            ''
        ]);

        // strip escape codes
        $debug_output = array_map(function ($line) {
            return preg_replace('/\x1b\[[0-?]*[ -\/]*[@-~]/i', '', $line);
        }, $debug_output);

        $wrapped_lines = (new LineWrapper())->wrap($debug_output);

        $output->writeln(array_map(function ($line) {
            return sprintf(
                '<output>%s</output>',
                OutputFormatter::escape($line)
            );
        }, $wrapped_lines));
        $output->writeln('');
    }
}
