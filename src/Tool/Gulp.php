<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

class Gulp extends Base
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
