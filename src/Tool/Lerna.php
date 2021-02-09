<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

/**
 * @package   PackageRelease
 * @author    Meg Mitchell <meg@silverorange.com>
 * @copyright 2020-2021 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Lerna
{
    public static function bootstrap(
        OutputInterface $output,
        array $scopes
    ): bool {
        $prefix = N::getPrefix($output);

        $command = 'lerna bootstrap';

        foreach ($scopes as $scope) {
            $command .= sprintf(' --scope=%s', \escapeshellarg($scope));
        }

        return (new ProcessRunner(
            $output,
            $prefix . $command,
            'bootstrapping Lerna repository',
            'bootstrapped Lerna repository',
            'failed to bootstrap Lerna repository'
        ))->run();
    }

    public static function build(OutputInterface $output, string $scope): bool
    {
        $prefix = N::getPrefix($output);

        $command = sprintf(
            'lerna run build --scope=%s',
            \escapeshellarg($scope)
        );

        return (new ProcessRunner(
            $output,
            $prefix . $command,
            sprintf('building Lerna package <variable>%s</variable>', $scope),
            sprintf('built Lerna package <variable>%s</variable>', $scope),
            sprintf(
                'failed to build Lerna package <variable>%s</variable>',
                $scope
            )
        ))->run();
    }
}
