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
class Composer
{
    public static function install(OutputInterface $output): bool
    {
        $command = 'composer install '
          . '--quiet '
          . '--no-interaction '
          . '--optimize-autoloader '
          . '--classmap-authoritative';

        return (new ProcessRunner(
            $output,
            $command,
            'installing composer dependencies',
            'installed composer dependencies',
            'failed to install composer dependencies'
        ))->run();
    }
}
