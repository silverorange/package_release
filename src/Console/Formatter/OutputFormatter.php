<?php

namespace Silverorange\PackageRelease\Console\Formatter;

use Symfony\Component\Console\Formatter\OutputFormatter as SymfonyOutputFormatter;

/**
 * Custom output formatter that works with mbstring function overloading
 * enabled
 *
 * See https://github.com/symfony/symfony/pull/26101/
 *
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class OutputFormatter extends SymfonyOutputFormatter
{
    public function format($message)
    {
        $message = (string) $message;
        $offset = 0;
        $output = '';
        $tagRegex = '[a-z][a-z0-9,_=;-]*+';
        // Note: Offset capture is in bytes. For compatibility, explicitly use
        // mbstring with ASCII encoding for string manipulations.
        preg_match_all("#<(($tagRegex) | /($tagRegex)?)>#ix", $message, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $i => $match) {
            $pos = $match[1];
            $text = $match[0];

            if (0 !== $pos && '\\' == mb_substr($message, $pos - 1, 1, 'ASCII')) {
                continue;
            }

            // add the text up to the next tag
            $output .= $this->applyCurrentStyle(mb_substr($message, $offset, $pos - $offset, 'ASCII'));
            $offset = $pos + strlen($text);

            // opening tag?
            if ($open = '/' != mb_substr($text, 1, 1, 'ASCII')) {
                $tag = $matches[1][$i][0];
            } else {
                $tag = isset($matches[3][$i][0]) ? $matches[3][$i][0] : '';
            }

            if (!$open && !$tag) {
                // </>
                $this->getStyleStack()->pop();
            } elseif (false === $style = $this->createStyleFromString(strtolower($tag))) {
                $output .= $this->applyCurrentStyle($text);
            } elseif ($open) {
                $this->getStyleStack()->push($style);
            } else {
                $this->getStyleStack()->pop($style);
            }
        }

        $output .= $this->applyCurrentStyle(mb_substr($message, $offset, null, 'ASCII'));

        if (false !== mb_strpos($output, "\0", 0, 'ASCII')) {
            return strtr($output, array("\0" => '\\', '\\<' => '<'));
        }

        return str_replace('\\<', '<', $output);
    }

    /**
     * Tries to create new style instance from string.
     *
     * @return OutputFormatterStyle|false False if string is not format string
     */
    private function createStyleFromString(string $string)
    {
        if ($this->hasStyle($string)) {
            return $this->getStyle($string);
        }

        if (!preg_match_all('/([^=]+)=([^;]+)(;|$)/', $string, $matches, PREG_SET_ORDER)) {
            return false;
        }

        $style = new OutputFormatterStyle();
        foreach ($matches as $match) {
            array_shift($match);

            if ('fg' == $match[0]) {
                $style->setForeground($match[1]);
            } elseif ('bg' == $match[0]) {
                $style->setBackground($match[1]);
            } elseif ('options' === $match[0]) {
                preg_match_all('([^,;]+)', $match[1], $options);
                $options = array_shift($options);
                foreach ($options as $option) {
                    $style->setOption($option);
                }
            } else {
                return false;
            }
        }

        return $style;
    }

    private function applyCurrentStyle(string $text): string
    {
        return ($this->isDecorated() && strlen($text) > 0)
            ? $this->getStyleStack()->getCurrent()->apply($text)
            : $text;
    }
}
