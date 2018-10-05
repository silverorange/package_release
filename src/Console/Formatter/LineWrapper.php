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
        int $width = 76,
        string $indent = '  '
    ): array {
        $text = implode("\n", $lines);

        // normalize line endings
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);

        // Match anything 1 to $width chars long followed by whitespace or EOS,
        // otherwise match anything $width chars long
        $search = '/(.{1,' . $width . '})(?:\s|$)|(.{' . $width . '})/uS';
        $replace = $indent . "\$1\$2\n";
        $text = preg_replace($search, $replace, $text);

        // remove last trailing newline
        $text = mb_substr($text, 0, -1);

        // convert back to array as expected by Symfony console writeln
        return explode("\n", $text);
    }
}
