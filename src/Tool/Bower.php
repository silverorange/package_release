<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018-2021 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Bower
{
    public static function install(OutputInterface $output): bool
    {
        $prefix = N::getPrefix($output);

        return (new ProcessRunner(
            $output,
            $prefix . 'bower install',
            'installing bower dependencies',
            'installed bower dependencies',
            'failed to install bower dependencies'
        ))->run();
    }
}
