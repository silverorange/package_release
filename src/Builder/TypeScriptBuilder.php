<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Tool\Npm;
use Silverorange\PackageRelease\Tool\PackageJson;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2020 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class TypeScriptBuilder extends BaseBuilder
{
    public function isAppropriate(): bool
    {
        return (
            PackageJson::hasDependency('typescript') ||
            PackageJson::hasDevDependency('typescript')
        );
    }

    public function build(OutputInterface $output): bool
    {
        return Npm::install($output)
            && Npm::build($output);
    }

    public function getTitle(): string
    {
        return 'TypeScript';
    }
}
