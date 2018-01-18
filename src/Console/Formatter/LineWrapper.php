<?php

namespace Silverorange\PackageRelease\Console\Formatter;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class LineWrapper
{
    public function wrap(
        array $lines,
        int $width = 70,
        string $indent = ' '
    ): array {
        // Match anything 1 to $width chars long followed by whitespace or EOS,
        // otherwise match anything $width chars long
        $search = '/(.{1,' . $width . '})(?:\s|$)|(.{' . $width . '})/uS';
        $replace = $indent . '$1$2';

        return array_map(
            function (string $line) use ($search, $replace) {
                return preg_replace($search, $replace, $line);
            },
            $lines
        );
    }
}
