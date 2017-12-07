<?php

namespace silverorange\PackageRelease;

use Psr\Log;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2017 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Prompt
{
    /**
     * @var Log\LoggerInterface $logger
     */
    protected $logger = null;

    public function __construct(Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $this->logger->notice('');

        while (!$answered) {
            if ($line2 !== null) {
                $this->logger->notice($line1);
            }
            $response = readline($prompt);
            if (preg_match('/^y|yes$/i', $response) === 1) {
                $response = true;
                $answered = true;
            } elseif (preg_match('/^n|no$/i', $response) === 1) {
                $response = false;
                $answered = true;
            }
            $this->logger->notice('');
        }

        return $response;
    }
}
