<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Gulp
{
    public static function concentrate(OutputInterface $output): bool
    {
        return (new ProcessRunner(
            $output,
            'gulp concentrate',
            'building static site assets',
            'built static site assets',
            'failed to build static assets'
        ))->run();
    }
}
