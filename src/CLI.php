<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */

namespace silverorange\PackageRelease;

use Psr\Log;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2016 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class CLI implements Log\LoggerAwareInterface
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
     * @var \Psr\Log\LoggingInterface
     */
    protected $logger = null;

    /**
     * @var \silverorange\PackageRelease\VerbosityHandler
     */
    protected $verbosity_handler = null;

    public function __construct(
        \Console_CommandLine $parser,
        Manager $manager,
        VerbosityHandler $handler,
        Log\LoggerInterface $logger
    ) {
        $this->setParser($parser);
        $this->setManager($manager);
        $this->setVerbosityHandler($handler);
        $this->setLogger($logger);
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

    public function setLogger(Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function run()
    {
        try {
            $result = $this->parser->parse();

            if ($result->options['quiet']) {
                $this->verbosity_handler->setVerbosity(
                    VerbosityHandler::VERBOSITY_QUIET
                );
            } else {
                $this->verbosity_handler->setVerbosity(
                    $result->options['verbose'] + 1
                );
            }

            if (!$this->manager->isInGitRepo()) {
                $this->logger->error(
                    'This tool must be run from a git repository.'
                );
                exit(1);
            }

            if (!$this->manager->isComposerPackage()) {
                $this->logger->error(
                    'Could not find "composer.json". Make sure you are in '
                    . 'the project root and the project is a composer package.'
                );
                exit(1);
            }

            $repo_name = $this->manager->getRepoName();
            if ($repo_name === null) {
                $this->logger->error(
                    'Could not find git repository name. Git repository '
                    . 'must have a remote named "origin".'
                );
                exit(1);
            }

            $remote_url = sprintf(
                'git@github.com:silverorange/%s.git',
                $repo_name
            );
            $remote = $this->manager->getRemoteByUrl($remote_url);
            if ($remote === null) {
                $this->logger->error(
                    'Could not find silverorange remote. A remote with the '
                    . 'URL "{remote}" must exist.',
                    array(
                        'remote' => $remote_url,
                    )
                );
                exit(1);
            }

            $current_version = $this->manager->getCurrentVersionFromRemote(
                $remote
            );
            if ($current_version === '0.0.0') {
                $this->logger->warn(
                    'No existing release. Next release will be first release.'
                );
            }

            $next_version = $this->manager->getNextVersion(
                $current_version,
                $result->options['type']
            );
            $this->logger->notice(
                'Releasing version {version}:',
                array(
                    'version' => $next_version,
                )
            );
            $this->logger->notice('');

            $branch = $result->options['branch'];
            $release_branch = $this->manager->createReleaseBranch(
                $branch,
                $remote,
                $next_version
            );
            if ($release_branch === null) {
                $this->logger->error(
                    'Could not create release branch from "{branch}". A branch '
                    . 'with the same name may already exist.',
                    array(
                        'branch' => $branch,
                    )
                );
                exit(1);
            } else {
                $this->logger->notice(
                    '=> created release branch "{branch}".',
                    array(
                        'branch' => $release_branch,
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
            $success = $this->manager->createReleaseTag(
                $next_version,
                $message
            );
            if ($success) {
                $this->logger->notice(
                    '=> tagged release with message "{message}".',
                    array(
                        'message' => $message,
                    )
                );
            } else {
                $this->logger->error(
                    'Failed to create release tag for "{tag}".',
                    array(
                        'tag' => $next_version,
                    )
                );
                exit(1);
            }

            if ($this->manager->pushTagToRemote($next_version, $remote)) {
                $this->logger->notice(
                    '=> pushed tag to "{remote}".',
                    array(
                        'remote' => $remote,
                    )
                );
            } else {
                $this->logger->error(
                    'Could not push tag "{tag}" to remote "{remote}".',
                    array(
                        'tag' => $next_version,
                        'remote' => $remote,
                    )
                );
                exit(1);
            }

            if ($this->manager->deleteBranch($release_branch)) {
                $this->logger->notice(
                    '=> removed release branch "{branch}".',
                    array(
                        'branch' => $release_branch,
                    )
                );
            } else {
                $this->logger->error(
                    'Could not delete release branch "{branch}".',
                    array(
                        'branch' => $release_branch,
                    )
                );
                exit(1);
            }

            $this->logger->notice('');
            $this->logger->notice('Done.');
        } catch (\Console_CommandLine_Exception $e) {
            $this->logger->error($e->getMessage());
            exit(1);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());
            exit(1);
        }
    }
}
