<?php

namespace Silverorange\PackageRelease\Tool;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Console\ProcessRunner;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2021 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class N
{
    private static $isAppropriate = null;
    private static $isDownloaded = false;

    public static function download(OutputInterface $output): bool
    {
        if (self::isAppropriate() && !self::$isDownloaded) {
            self::$isDownloaded = (new ProcessRunner(
                $output,
                'n --download engine',
                'downloading required node version',
                'downloaded required node version',
                'failed to download required node version'
            ))->run();
        }

        return self::$isDownloaded;
    }

    public static function getPrefix(OutputInterface $output): string
    {
        self::download($output);

        if (self::isAppropriate()) {
            return 'n exec engine ';
        }

        return '';
    }

    public function isAppropriate(): bool
    {
        if (self::$isAppropriate === null) {
            if (self::hasFile('package.json')) {
                $json = json_decode(file_get_contents('package.json'), true);
                self::$isAppropriate = (
                    isset($json['engines']) && isset($json['engines']['node'])
                );
            } else {
                self::$isAppropriate = false;
            }
        }

        return self::$isAppropriate;
    }

    protected function hasFile(string $filename): bool
    {
        return (file_exists($filename) && is_readable($filename));
    }
}
