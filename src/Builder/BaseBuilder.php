<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
abstract class BaseBuilder implements BuilderInterface
{
    protected function hasFile($filename)
    {
        return (file_exists($filename) && is_readable($filename));
    }
}
