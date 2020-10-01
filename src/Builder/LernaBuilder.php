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
    /**
     * @var string[]
     */
    protected $scopes;

    public function __construct(array $scopes = [])
    {
        $this->scopes = $scopes;
    }

    public function isAppropriate(): bool
    {
        return $this->hasFile('lerna.json');
    }

    public function build(OutputInterface $output): bool
    {
        $result = Lerna::bootstrap($output, $this->scopes);

        foreach ($this->scopes as $scope) {
            $result = $result && Lerna::build($output, $scope);
        }

        return $result;
    }

    public function getTitle(): string
    {
        return 'Lerna';
    }
}
