<?php

namespace silverorange\PackageRelease;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2017 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Output
{
    /**
     * @var \silverorange\PackageRelease\VerbosityHandler
     */
    protected $verbosity_handler = null;

    /**
     * @var resource
     */
    protected $stdout = null;

    public function __construct(VerbosityHandler $handler)
    {
        $this->setVerbosityHandler($handler);

        $this->stdout = fopen('php://stdout', 'w');
    }

    public function setVerbosityHandler(VerbosityHandler $handler)
    {
        $this->verbosity_handler = $handler;
    }

    public function error($message)
    {
        if (!$this->verbosity_handler->isQuiet()) {
            fwrite($this->stdout, $message);
        }
    }

    public function warn($message)
    {
        if (!$this->verbosity_handler->isQuiet()) {
            fwrite($this->stdout, $message);
        }
    }

    public function notice($message)
    {
        if (!$this->verbosity_handler->isQuiet()) {
            fwrite($this->stdout, $message);
        }
    }

    public function wrap($text, $width = 70, $indent = ' ')
    {
        $width = (int)$width;

        // Match anything 1 to $width chars long followed by whitespace or EOS,
        // otherwise match anything $width chars long
        $search = '/(.{1,' . $width . '})(?:\s|$)|(.{' . $width . '})/uS';
        $replace = $indent . '$1$2' . PHP_EOL;

        return preg_replace($search, $replace, $text);
    }
}
