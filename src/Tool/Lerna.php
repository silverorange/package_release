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
    public static function verify(OutputInterface $output): bool
    {
        $command = 'yarn global add lerna';

        return (new ProcessRunner(
            $output,
            $command,
            'verifying Lerna installation',
            'verified Lerna installation',
            'failed to verify Lerna installation'
        ))->run();
    }

    public static function bootstrap(OutputInterface $output): bool
    {
        $command = 'yarn lerna bootstrap';

        return (new ProcessRunner(
            $output,
            $command,
            'bootstrapping Lerna repository',
            'bootstrapped Lerna repository',
            'failed to bootstrap Lerna repository'
        ))->run();
    }

    public static function build(OutputInterface $output, Array $scopes): bool
    {
        $command = 'yarn lerna run build';
        foreach ($scopes as $scope) {
            $command .= sprintf(' --scope=%s', $scope);
        }

        return (new ProcessRunner(
            $output,
            $command,
            'building Lerna web packages',
            'built Lerna web packages',
            'failed to build Lerna web packages'
        ))->run();
    }
}
