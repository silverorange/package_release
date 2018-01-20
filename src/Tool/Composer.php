<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

class Composer extends Base
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
