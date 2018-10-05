<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Tool\Npm;
use Silverorange\PackageRelease\Tool\Bower;
use Silverorange\PackageRelease\Tool\Ember;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
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
        return Npm::install($output)
            && Bower::install($output)
            && Ember::build($output);
    }

    public function getTitle(): string
    {
        return 'Ember';
    }
}
