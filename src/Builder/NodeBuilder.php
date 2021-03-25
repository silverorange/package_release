<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Tool\Npm;
use Silverorange\PackageRelease\Tool\PackageJson;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class NodeBuilder extends BaseBuilder
{
    public function isAppropriate(): bool
    {
        return PackageJson::hasPackageJson();
    }

    public function build(OutputInterface $output): bool
    {
        return Npm::install($output);
    }

    public function getTitle(): string
    {
        return 'Node';
    }
}
