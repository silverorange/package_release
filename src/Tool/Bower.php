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
class Bower
{
    private static ?bool $isAppropriate = null;

    public static function install(OutputInterface $output): bool
    {
        if (!self::isAppropriate()) {
            return true;
        }

        $prefix = N::getPrefix($output);

        return (new ProcessRunner(
            $output,
            $prefix . 'bower install',
            'installing bower dependencies',
            'installed bower dependencies',
            'failed to install bower dependencies'
        ))->run();
    }

    public static function isAppropriate(): bool
    {
        if (self::$isAppropriate === null) {
            self::$isAppropriate = self::hasFile('bower.json');
        }

        return self::$isAppropriate;
    }

    protected static function hasFile(string $filename): bool
    {
        return (file_exists($filename) && is_readable($filename));
    }
}
