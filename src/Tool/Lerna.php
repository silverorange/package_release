<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

/**
 * @package   PackageRelease
 * @author    Meg Mitchell <meg@silverorange.com>
 * @copyright 2020 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Lerna
{
    public static function bootstrap(
        OutputInterface $output,
        array $scopes
    ): bool {
        $command = 'lerna bootstrap';
        foreach ($scopes as $scope) {
            $command .= sprintf(' --scope=%s', \escapeshellarg($scope));
        }

        return (new ProcessRunner(
            $output,
            $command,
            'bootstrapping Lerna repository',
            'bootstrapped Lerna repository',
            'failed to bootstrap Lerna repository'
        ))->run();
    }

    public static function build(OutputInterface $output, string $scope): bool
    {
        $command = sprintf('lerna run build --scope=%s', $scope);

        return (new ProcessRunner(
            $output,
            $command,
            sprintf('building Lerna web package "%s"', $scope),
            sprintf('built Lerna web package "%s"', $scope),
            sprintf('failed to build Lerna web package "%s"', $scope)
        ))->run();
    }
}
