<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Tool\Corepack;
use Silverorange\PackageRelease\Tool\Npm;
use Silverorange\PackageRelease\Tool\Bower;
use Silverorange\PackageRelease\Tool\Ember;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018-2024 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class EmberBuilder extends BaseBuilder
{
    public function isAppropriate(): bool
    {
        return $this->hasFile('.ember-cli');
    }

    public function build(OutputInterface $output): bool
    {
        $hasBower = $this->hasFile('bower.json');

        return Corepack::enable($output)
            && Npm::install($output)
            && (!$hasBower || Bower::install($output))
            && Ember::build($output);
    }

    public function getTitle(): string
    {
        return 'Ember';
    }
}
