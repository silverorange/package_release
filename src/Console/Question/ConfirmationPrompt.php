<?php

namespace Silverorange\PackageRelease\Console\Question;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2017-2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class ConfirmationPrompt
{
    /**
     * @var Symfony\Component\Console\Helper\QuestionHelper
     */
    protected $helper = null;

    public function __construct(QuestionHelper $helper)
    {
        $this->setHelper($helper);
    }

    public function setHelper(QuestionHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Asks a yes or no question, waits for a response and returns a boolean
     *
     * @param string $line1 optional. The prompt text to use. If $line2 is
     *                      specified, this is displayed above the input line.
     *                      If not specified, 'Yes or no? ' is used.
     * @param string $line2 optional. The prompt text to use. $line1 is
     *                      displayed above the input and this line is displayed
     *                      before the input. If not specified, $line1 is
     *                      displayed before the input.
     *
     * @return boolean true if the user entered yes, otherwise false.
     */
    public function ask(
        InputInterface $input,
        OutputInterface $output,
        string $message
    ): bool {
        $answered = false;

        $output->writeln('');
        $question = new Question('<prompt>></prompt> ', null);

        while (!$answered) {
            $output->writeln($message);
            $response = $this->helper->ask($input, $output, $question);
            if (preg_match('/^y|yes$/i', $response) === 1) {
                $response = true;
                $answered = true;
            } elseif (preg_match('/^n|no$/i', $response) === 1) {
                $response = false;
                $answered = true;
            }
            $output->writeln('');
        }

        return $response;
    }
}
