<?php

namespace Silverorange\PackageRelease\Console\Formatter;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Style
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->getFormatter()->setStyle(
            'variable',
            new OutputFormatterStyle('magenta')
        );

        $output->getFormatter()->setStyle(
            'tip',
            new OutputFormatterStyle('cyan')
        );

        $output->getFormatter()->setStyle(
            'header',
            new OutputFormatterStyle(null, null, ['bold', 'underscore'])
        );

        $output->getFormatter()->setStyle(
            'link',
            new OutputFormatterStyle('blue')
        );

        $output->getFormatter()->setStyle(
            'waiting',
            new OutputFormatterStyle('gray')
        );

        $output->getFormatter()->setStyle(
            'success',
            new OutputFormatterStyle('green')
        );

        $output->getFormatter()->setStyle(
            'failure',
            new OutputFormatterStyle('red')
        );

        $output->getFormatter()->setStyle(
            'bold',
            new OutputFormatterStyle(null, null, ['bold'])
        );

        $output->getFormatter()->setStyle(
            'prompt',
            new OutputFormatterStyle('yellow')
        );

        $output->getFormatter()->setStyle(
            'output',
            new OutputFormatterStyle(null, null, ['dim'])
        );
    }
}
