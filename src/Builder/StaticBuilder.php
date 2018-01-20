<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class StaticBuilder extends BaseBuilder
{
    public function isAppropriate(): bool
    {
        return true;
    }

    public function build(OutputInterface $output): bool
    {
        return true;
    }

    public function getTitle(): string
    {
        return 'Static';
    }
}
