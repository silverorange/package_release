<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018-2024 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Npm
{
    public static function install(OutputInterface $output): bool
    {
        $prefix = N::getPrefix($output);

        $command = match (true) {
            (static::isPnpm()) => 'pnpm install --silent',
            (static::isYarn()) => 'yarn install --silent',
            default => 'npm install --no-package-lock --quiet',
        };

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
        $command = match (true) {
            (static::isPnpm()) => 'pnpm build ' . $flags,
            (static::isYarn()) => 'yarn build ' . $flags,
            default => 'npm build ' . $flags,
        };

        return (new ProcessRunner(
            $output,
            $prefix . $command,
            'building project',
            'built project',
            'failed to build project'
        ))->run();
    }

    public static function run(OutputInterface $output, string $script): bool
    {
        $prefix = N::getPrefix($output);

        // Note: script is not escaped so that args can be passed.
        $command = match (true) {
            (static::isPnpm()) => 'pnpm run ' . $script,
            (static::isYarn()) => 'yarn run ' . $script,
            default => 'npm run ' . $script,
        };

        return (new ProcessRunner(
            $output,
            $prefix . $command,
            'running script "' . $script . '"',
            'ran script "' . $script . '"',
            'failed to run script "' . $script . '"'
        ))->run();
    }

    protected static function isYarn(): bool
    {
        return self::hasFile('yarn.lock');
    }

    protected static function isPnpm(): bool
    {
        return self::hasFile('pnpm-lock.yaml');
    }

    protected static function hasFile(string $filename): bool
    {
        return (file_exists($filename) && is_readable($filename));
    }
}
