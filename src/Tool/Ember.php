<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

class Ember
{
    public static function build(OutputInterface $output): bool
    {
        return (new ProcessRunner(
            $output,
            'ember build --environment=production',
            'building ember project',
            'built ember project',
            'failed to build ember project'
        ))->run();
    }
}
