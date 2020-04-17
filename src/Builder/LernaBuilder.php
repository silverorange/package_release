<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Tool\Npm;
use Silverorange\PackageRelease\Tool\Lerna;

/**
 * @package   PackageRelease
 * @author    Meg Mitchell <meg@silverorange.com>
 * @copyright 2020 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class LernaBuilder extends BaseBuilder
{
    protected $scopes;

    function __construct(Array $scopes=[]) {
       $this->scopes = $scopes;
    }

    public function isAppropriate(): bool
    {
        return $this->hasFile('lerna.json');
    }

    public function build(OutputInterface $output): bool
    {
        return Npm::install($output)
            && Lerna::verify($output)
            && Lerna::bootstrap($output)
            && Lerna::build($output, $this->scopes);
    }

    public function getTitle(): string
    {
        return 'Lerna';
    }
}
