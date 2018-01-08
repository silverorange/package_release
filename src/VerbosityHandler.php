<?php

namespace silverorange\PackageRelease;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2016-2017 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class VerbosityHandler
{
    /**
     * @var boolean
     */
    protected $is_quiet = false;

    public function __construct($is_quiet = false)
    {
        $this->setIsQuiet($is_quiet);
    }

    /**
     * Sets whether or not output should be suppressed
     *
     * @param boolean $is_quiet whether or not output should be suppressed.
     *
     * @return void
     */
    public function setIsQuiet($is_quiet)
    {
        $this->is_quiet = $is_quiet ? true : false;
    }

    /**
     * Gets whether or not output should be suppressed
     *
     * @return boolean
     */
    public function isQuiet()
    {
        return $this->is_quiet;
    }
}
