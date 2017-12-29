<?php

namespace silverorange\PackageRelease;

use Chalk\Chalk;
use Chalk\Style;
use Chalk\Color;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2016 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class CLI
{
    /**
     * @var \Console_CommandLine
     */
    protected $parser = null;

    /**
     * @var \silverorange\PackageRelease\Manager
     */
    protected $manager = null;

    /**
     * The logging interface of this application.
     *
     * @var silverorange\PackageRelease\Output
     */
    protected $output = null;

    /**
     * @var silverorange\PackageRelease\VerbosityHandler
     */
    protected $verbosity_handler = null;

    /**
     * @var silverorange\PackageRelease\Prompt
     */
    protected $prompt = null;

    public function __construct(
        \Console_CommandLine $parser,
        Manager $manager,
        VerbosityHandler $handler,
        Output $output,
        Prompt $prompt
    ) {
        $this->setParser($parser);
        $this->setManager($manager);
        $this->setVerbosityHandler($handler);
        $this->setOutput($output);
        $this->setPrompt($prompt);
    }

    public function setParser(\Console_CommandLine $parser)
    {
        $this->parser = $parser;
    }

    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function setVerbosityHandler(VerbosityHandler $handler)
    {
        $this->verbosity_handler = $handler;
    }

    public function setOutput(Output $output)
    {
        $this->output = $output;
    }

    public function setPrompt(Prompt $prompt)
    {
        $this->prompt = $prompt;
    }

    public function run()
    {
        try {
            $result = $this->parser->parse();

            $this->verbosity_handler->setIsQuiet($result->options['quiet']);

            if (!$this->manager->isInGitRepo()) {
                $this->output->error(
                    'This tool must be run from a git repository.'
                    . PHP_EOL . PHP_EOL
                );
                exit(1);
            }

            if (!$this->manager->isComposerPackage()) {
                $this->output->error(
                    sprintf(
                        'Could not find %s. Make sure you are in the project '
                        . 'root and the project is a composer package.'
                        . PHP_EOL . PHP_EOL,
                        Chalk::magenta('composer.json')
                    )
                );
                exit(1);
            }

            $repo_name = $this->manager->getRepoName();
            if ($repo_name === null) {
                $this->output->error(
                    sprintf(
                        'Could not find git repository name. Git repository '
                        . 'must have a remote named %s.' . PHP_EOL . PHP_EOL,
                        Chalk::magenta('origin')
                    )
                );
                exit(1);
            }

            $remote_url = sprintf(
                'git@github.com:silverorange/%s.git',
                $repo_name
            );
            $remote = $this->manager->getRemoteByUrl($remote_url);
            if ($remote === null) {
                $this->output->error(
                    sprintf(
                        'Could not find silverorange remote. A remote with the '
                        . 'URL %s must exist.' . PHP_EOL . PHP_EOL,
                        Chalk::magenta($remote_url)
                    )
                );
                exit(1);
            }

            $current_version = $this->manager->getCurrentVersionFromRemote(
                $remote
            );
            if ($current_version === '0.0.0') {
                $this->output->warn(
                    Chalk::cyan(
                        'No existing release. Next release will be first '
                        . 'release.' . PHP_EOL
                    )
                );
            }

            $next_version = $this->manager->getNextVersion(
                $current_version,
                $result->options['type']
            );

            // Prompt to continue release.
            if (!$result->options['yes'] && !$result->options['quiet']) {
                $continue = $this->prompt->ask(
                    sprintf(
                        'Ready to release new %s version %s. '
                        . 'Continue? %s' . PHP_EOL,
                        $result->options['type'],
                        Chalk::magenta($next_version),
                        Chalk::yellow('[Y/N]')
                    ),
                    Chalk::yellow('> ')
                );

                if (!$continue) {
                    $this->output->notice(
                        Chalk::style(
                            'Got it. Not releasing.',
                            new Style([ Color::BLACK, Style::BOLD ])
                        )
                        . PHP_EOL . PHP_EOL
                    );
                    exit(0);
                }
            }

            $this->output->notice(
                Chalk::style(
                    sprintf(
                        'Releasing version %s:' . PHP_EOL,
                        $next_version
                    ),
                    new Style([Color::BLACK, Style::UNDERLINED, Style::BOLD])
                )
            );
            $this->output->notice(PHP_EOL);

            $branch = $result->options['branch'];
            $this->startCommand();
            $release_branch = $this->manager->createReleaseBranch(
                $branch,
                $remote,
                $next_version
            );
            if ($release_branch === null) {
                $this->handleError(
                    sprintf(
                        'could not create release branch from %s',
                        Chalk::magenta($branch)
                    ),
                    $this->manager->getLastError()
                );
            } else {
                $this->handleSuccess(
                    sprintf(
                        'created release branch %s',
                        Chalk::magenta($release_branch)
                    )
                );
            }

            if ($result->options['message'] == '') {
                $message = sprintf(
                    'Release version %s.',
                    $next_version
                );
            } else {
                $message = $result->options['message'];
            }
            $this->startCommand();
            $success = $this->manager->createReleaseTag(
                $next_version,
                $message
            );
            if ($success) {
                $this->handleSuccess(
                    sprintf(
                        'tagged release with message %s',
                        Chalk::magenta($message)
                    )
                );
            } else {
                $this->handleError(
                    sprintf(
                        'failed to create release tag for %s',
                        Chalk::magenta($next_version)
                    ),
                    $this->manager->getLastError()
                );
            }

            $this->startCommand();
            if ($this->manager->pushTagToRemote($next_version, $remote)) {
                $this->handleSuccess(
                    sprintf(
                        'pushed tag to %s',
                        Chalk::magenta($remote)
                    )
                );
            } else {
                $this->handleError(
                    sprintf(
                        'could not push tag %s to remote %s',
                        Chalk::magenta($next_version),
                        Chalk::magenta($remote)
                    ),
                    $this->manager->getLastError()
                );
            }

            $this->startCommand();
            if ($this->manager->deleteBranch($release_branch)) {
                $this->handleSuccess(
                    sprintf(
                        'removed release branch %s',
                        Chalk::magenta($release_branch)
                    )
                );
            } else {
                $this->handleError(
                    sprintf(
                        'could not delete release branch %s',
                        Chalk::magenta($release_branch)
                    ),
                    $this->manager->getLastError()
                );
            }

            $this->output->notice(PHP_EOL);
            $this->output->notice(Chalk::green('Success!') . PHP_EOL . PHP_EOL);
            $this->output->notice(
                sprintf(
                    'The composer repository will automatically update. It '.
                    'may take a few minutes for the release to appear at %s.'
                    . PHP_EOL . PHP_EOL,
                    Chalk::blue('https://composer/')
                )
            );
        } catch (\Console_CommandLine_Exception $e) {
            $this->output->error($e->getMessage());
            exit(1);
        } catch (\Exception $e) {
            $this->output->error($e->getMessage());
            $this->output->error($e->getTraceAsString());
            exit(1);
        }
    }

    protected function startCommand()
    {
        $this->output->notice(Chalk::dark_gray('… '));
    }

    protected function handleSuccess($message)
    {
        $this->output->notice(
            sprintf(
                "\r%s %s" . PHP_EOL,
                Chalk::green('✓'),
                $message
            )
        );
    }

    protected function handleError($message, $debug_output)
    {
        $this->output->error(
            sprintf(
                "\r%s %s" . PHP_EOL . PHP_EOL . '%s' . PHP_EOL,
                Chalk::red('✗'),
                $message,
                Chalk::style(
                    $this->output->wrap(
                        $debug_output,
                        76,
                        '  '
                    ),
                    new Style([ Style::DIM ])
                )
            )
        );
        exit(1);
    }
}
