<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2024 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Corepack
{
    private static ?bool $isAppropriate = null;

    public static function enable(OutputInterface $output): bool
    {
        if (!self::isAppropriate()) {
            return true;
        }

        $prefix = N::getPrefix($output);

        $command = 'corepack enable';

        return (new ProcessRunner(
            $output,
            $prefix . $command,
            'enabling corepack',
            'enabled corepack',
            'failed to enable corepack'
        ))->run();
    }

    public static function isAppropriate(): bool
    {
        if (self::$isAppropriate === null) {
            self::$isAppropriate = PackageJson::hasPackageManager();
        }

        return self::$isAppropriate;
    }
}
