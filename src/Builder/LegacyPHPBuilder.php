<?php

namespace Silverorange\PackageRelease\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use Silverorange\PackageRelease\Tool\Composer;
use Silverorange\PackageRelease\Tool\Gulp;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class LegacyPHPBuilder extends BaseBuilder
{
    public function isAppropriate(): bool
    {
        $has_composer_json = $this->hasFile('composer.json');
        $node_14_15_1 = false;
        if ($this->hasFile('package.json')) {
            $json = json_decode(file_get_contents('package.json'));
            if (property_exists($json, 'engines') && property_exists($json->engines, 'node')) {
                $node_14_15_1 =  version_compare($json->engines->node, "14.15.1", ">=");
            }
        }
        
        return $has_composer_json && !$node_14_15_1;
    }

    public function build(OutputInterface $output): bool
    {
        return Composer::install($output)
            && Gulp::concentrate($output);
    }

    public function getTitle(): string
    {
        return 'Legacy PHP';
    }
}
