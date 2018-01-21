<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

class Npm
{
    public static function install(OutputInterface $output): bool
    {
        $command = (static::isYarn())
            ? 'yarn install --silent'
            : 'npm install --no-package-lock --quiet';

        return (new ProcessRunner(
            $output,
            $command,
            'installing npm dependencies',
            'installed npm dependencies',
            'failed to install npm dependencies'
        ))->run();
    }

    public static function build(OutputInterface $output): bool
    {
        $command = (static::isYarn())
            ? 'yarn build --silent'
            : 'npm build --quiet';

        return (new ProcessRunner(
            $output,
            $command,
            'building project',
            'built project',
            'failed to build project'
        ))->run();
        static::start($output, 'building project');
    }

    protected static function isYarn(): bool
    {
        return file_exists('yarn.lock');
    }
}
