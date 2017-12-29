<?php

namespace silverorange\PackageRelease;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2016 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class VerbosityHandler
{
    const VERBOSITY_QUIET = 0;
    const VERBOSITY_NORMAL = 1;

    /**
     * @var integer
     */
    protected $verbosity = self::VERBOSITY_NORMAL;

    public function __construct(
        $verbosity = self::VERBOSITY_NORMAL
    ) {
        $this->setVerbosity($verbosity);
    }

    /**
     * Sets the level of verbosity to use for the logger
     *
     * @param integer $verbosity the verbosity level to use.
     *
     * @return void
     */
    public function setVerbosity($verbosity)
    {
        $this->verbosity = min((integer)$verbosity, self::VERBOSITY_NORMAL);
    }

    public function isHandling($level = self::VERBOSITY_NORMAL)
    {
        return ($level >= $this->verbosity);
    }
}
