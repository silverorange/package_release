<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Tool\Composer;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class LaravelBuilder extends BaseBuilder
{
    public function isAppropriate(): bool
    {
        return $this->hasFile('artisan') && $this->hasFile('composer.json');
    }

    public function build(OutputInterface $output): bool
    {
        return Composer::install($output);
    }

    public function getTitle(): string
    {
        return 'Laravel';
    }
}
