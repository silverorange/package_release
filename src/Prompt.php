<?php

namespace silverorange\PackageRelease;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2017 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Prompt
{
    /**
     * @var silverorange\PackageRelease\Output
     */
    protected $output = null;

    public function __construct(Output $output)
    {
        $this->output = $output;
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
    public function ask($line1 = 'Yes or no? ', $line2 = null)
    {
        $answered = false;

        $prompt = ($line2 === null) ? $line1 : $line2;
        $this->output->notice(PHP_EOL);

        while (!$answered) {
            if ($line2 !== null) {
                $this->output->notice($line1);
            }
            $this->output->notice($prompt);
            $response = readline();
            if (preg_match('/^y|yes$/i', $response) === 1) {
                $response = true;
                $answered = true;
            } elseif (preg_match('/^n|no$/i', $response) === 1) {
                $response = false;
                $answered = true;
            }
            $this->output->notice(PHP_EOL);
        }

        return $response;
    }
}
