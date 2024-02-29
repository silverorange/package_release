<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Tool\Corepack;
use Silverorange\PackageRelease\Tool\Npm;
use Silverorange\PackageRelease\Tool\PackageJson;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018-2024 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class ReactBuilder extends BaseBuilder
{
    public function isAppropriate(): bool
    {
        return (
            PackageJson::hasDependency('react-scripts') ||
            PackageJson::hasDevDependency('react-scripts')
        );
    }

    public function build(OutputInterface $output): bool
    {
        return Corepack::enable($output)
            && Npm::install($output)
            && Npm::build($output, '--silent');
    }

    public function getTitle(): string
    {
        return 'React';
    }
}
