<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Tool\Composer;
use Silverorange\PackageRelease\Tool\Gulp;
use Silverorange\PackageRelease\Tool\Npm;
use Silverorange\PackageRelease\Tool\PackageJson;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018-2024 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class LegacyPHPBuilder extends BaseBuilder
{
    public function isAppropriate(): bool
    {
        return $this->hasFile('composer.json');
    }

    public function build(OutputInterface $output): bool
    {
        $hasSoGulp = PackageJson::hasDevDependency('sogulp');
        $hasLegaseer = PackageJson::hasDevDependency('silverorange-legaseer');

        $result = Composer::install($output);

        $result = $result && (!$hasSoGulp || Gulp::concentrate($output));
        $result = $result && (!$hasLegaseer || Npm::run($output, 'concentrate'));

        return $result;
    }

    public function getTitle(): string
    {
        return 'Legacy PHP';
    }
}
