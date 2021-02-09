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
class Ember
{
    public static function build(OutputInterface $output): bool
    {
        $prefix = N::getPrefix($output);

        return (new ProcessRunner(
            $output,
            $prefix . 'ember build --environment=production',
            'building ember project',
            'built ember project',
            'failed to build ember project'
        ))->run();
    }
}
