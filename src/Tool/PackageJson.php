<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2021-2024 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class PackageJson
{
    const PACKAGE_JSON = 'package.json';

    private static $jsonContent = null;

    public static function hasPackageJson(): bool
    {
        return self::hasFile(self::PACKAGE_JSON);
    }

    public static function hasDependency(string $dependency): bool
    {
        $json = self::getJsonContent();

        return (
            isset($json['dependencies']) &&
            isset($json['dependencies'][$dependency])
        );
    }

    public static function hasDevDependency(string $dependency): bool
    {
        $json = self::getJsonContent();

        return (
            isset($json['devDependencies']) &&
            isset($json['devDependencies'][$dependency])
        );
    }

    public static function hasEngine(string $engine): bool
    {
        $json = self::getJsonContent();

        return (
            isset($json['engines']) &&
            isset($json['engines'][$engine])
        );
    }

    public static function hasPackageManager(): bool
    {
        $json = self::getJsonContent();

        return (
            isset($json['hasPackageManager'])
        );
    }

    protected static function getJsonContent(): array
    {
        if (self::$jsonContent === null) {
            if (self::hasPackageJson()) {
                self::$jsonContent = json_decode(
                    file_get_contents(self::PACKAGE_JSON),
                    true
                );
            } else {
                self::$jsonContent = [];
            }
        }

        return self::$jsonContent;
    }

    protected static function hasFile(string $filename): bool
    {
        return (file_exists($filename) && is_readable($filename));
    }
}
