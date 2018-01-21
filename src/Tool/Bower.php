<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;

class Bower
{
    public static function install(OutputInterface $output): bool
    {
        return (new ProcessRunner(
            $output,
            'bower install',
            'installing bower dependencies',
            'installed bower dependencies',
            'failed to install bower dependencies'
        ))->run();
    }
}
