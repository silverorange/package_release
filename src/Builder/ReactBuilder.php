<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Tool\Npm;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class ReactBuilder extends BaseBuilder
{
    public function isAppropriate(): bool
    {
        if ($this->hasFile('package.json')) {
            $json = json_decode(file_get_contents('package.json'), true);
            return (
                isset($json['dependencies']) && isset($json['dependencies']['react-scripts'])
            ) || (
                isset($json['devDependencies']) && isset($json['devDependencies']['react-scripts'])
            );
        }

        return false;
    }

    public function build(OutputInterface $output): bool
    {
        return Npm::install($output)
            && Npm::build($output);
    }

    public function getTitle(): string
    {
        return 'React';
    }
}
