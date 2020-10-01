<?php

namespace Silverorange\PackageRelease\Console\Question;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2020 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class OptionsPrompt
{
    /**
     * @var Symfony\Component\Console\Helper\QuestionHelper
     */
    protected $helper = null;

    public function __construct(QuestionHelper $helper)
    {
        $this->setHelper($helper);
    }

    public function setHelper(QuestionHelper $helper): self
    {
        $this->helper = $helper;
        return $this;
    }

    /**
     * Asks a multiple-choice question, waits for a response and returns one of
     * the values
     *
     * @param string $prompt  The prompt text to use. This is displayed above
     *                        the input line.
     * @param array  $options an array of
     *                        {@link Silverorange\PackageRelease\Console\Question\OptionPromptOption}
     *                        objects.
     *
     * @return string the selected option value.
     */
    public function ask(
        InputInterface $input,
        OutputInterface $output,
        string $prompt,
        array $options
    ): string {
        $answered = false;

        $output->writeln('');
        $question = new Question('<prompt>></prompt> ', null);

        while (!$answered) {
            $output->writeln($prompt);
            $output->writeln('');
            foreach ($options as $option) {
                $output->writeln($option->getPrompt());
            }
            $output->writeln('');
            $response = $this->helper->ask($input, $output, $question);

            foreach ($options as $option) {
                if ($option->matches($response)) {
                    $value = $option->getValue();
                    $answered = true;
                    break 2;
                }
            }

            $output->writeln('');
        }

        return $value;
    }
}
