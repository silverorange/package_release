<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */

namespace silverorange\ModuleRelease;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\HandlerWrapper;
use Monolog\Logger;

/**
 * Monolog handler warapper that filters a handler based on a set verbosity
 * level
 *
 * @package   ModuleRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2016 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class VerbosityHandler extends HandlerWrapper
{
    const VERBOSITY_QUIET = 0;
    const VERBOSITY_NORMAL = 1;
    const VERBOSITY_VERBOSE  = 2;
    const VERBOSITY_VERY_VERBOSE  = 3;
    const VERBOSITY_DEBUG = 4;

    /**
     * @var integer
     */
    protected $verbosity = self::VERBOSITY_NORMAL;

    /**
     * @var array
     */
    protected $verbosity_map = array(
        self::VERBOSITY_NORMAL => Logger::WARNING,
        self::VERBOSITY_VERBOSE => Logger::NOTICE,
        self::VERBOSITY_VERY_VERBOSE => Logger::INFO,
        self::VERBOSITY_DEBUG => Logger::DEBUG,
    );

    public function __construct(
        HandlerInterface $handler,
        $verbosity = self::VERBOSITY_NORMAL
    ) {
        parent::__construct($handler);
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
        $this->verbosity = (integer)$verbosity;
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return (
            isset($this->verbosity_map[$this->verbosity])
            && $record['level'] >= $this->verbosity_map[$this->verbosity]
        );
    }
}
