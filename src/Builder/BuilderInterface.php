<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
interface BuilderInterface
{
    public function isAppropriate(): bool;

    public function build(OutputInterface $output): bool;

    public function getTitle(): string;
}
