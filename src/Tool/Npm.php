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
class Npm
{
    public static function install(OutputInterface $output): bool
    {
        $prefix = N::getPrefix($output);

        $command = (static::isYarn())
            ? 'yarn install --silent'
            : 'npm install --no-package-lock --quiet';

        return (new ProcessRunner(
            $output,
            $prefix . $command,
            'installing npm dependencies',
            'installed npm dependencies',
            'failed to install npm dependencies'
        ))->run();
    }

    public static function build(
        OutputInterface $output,
        string $flags = ''
    ): bool {
        $prefix = N::getPrefix($output);

        // Note: Flags are not escaped so that multiple flags can be passed.
        $command = (static::isYarn())
            ? 'yarn build ' . $flags
            : 'npm build ' . $flags;

        return (new ProcessRunner(
            $output,
            $prefix . $command,
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
