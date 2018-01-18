<?php

namespace Silverorange\PackageRelease\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Silverorange\PackageRelease\Git\Manager;
use Silverorange\PackageRelease\Console\Question\ConfirmationPrompt;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2017-2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class PackageReleaseCommand extends Command
{
    /**
     * @var Silverorange\PackageRelease\Manager
     */
    protected $manager = null;

    public function __construct(Manager $manager)
    {
        parent::__construct();
        $this->setManager($manager);
    }

    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('package-release')

            // the short description shown while running "php bin/console list"
            ->setDescription('Releases new versions of composer packages.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp(
                'This tool is used to release new versions of composer '
                . 'packages. It uses Semver 2.0 to automatically pick the '
                . 'next version number and tag the release on GitHub.'
            )
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption(
                        'branch',
                        'b',
                        InputOption::VALUE_REQUIRED,
                        'Remote branch to use for release.',
                        'master'
                    ),
                    new InputOption(
                        'message',
                        'm',
                        InputOption::VALUE_REQUIRED,
                        'Message to use for the release tag.'
                    ),
                    new InputOption(
                        'type',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Release type. Must be one of "major", "minor", or '
                        . '"patch". Semver 2.0 (https://semver.org/) is used '
                        . 'pick the next release number.',
                        'minor'
                    ),
                ))
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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
            new OutputFormatterStyle()
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

        if (!$this->manager->isInGitRepo()) {
            $output->writeln([
                'This tool must be run from a git repository.',
                '',
            ]);
            exit(1);
        }

        if (!$this->manager->isComposerPackage()) {
            $output->writeln([
                'Could not find <variable>composer.json</variable>. Make '
                . 'sure you are in the project '
                . 'root and the project is a composer package.',
                '',
            ]);
            exit(1);
        }

        $repo_name = $this->manager->getRepoName();
        if ($repo_name === null) {
            $output->writeln([
                'Could not find git repository name. Git repository '
                . 'must have a remote named <variable>origin</variable>.',
                '',
            ]);
            exit(1);
        }

        $remote_url = sprintf(
            'git@github.com:silverorange/%s.git',
            $repo_name
        );
        $remote = $this->manager->getRemoteByUrl($remote_url);
        if ($remote === null) {
            $output->writeln([
                sprintf(
                    'Could not find silverorange remote. A remote with the '
                    . 'URL <variable>%s</variable> must exist.',
                    OutputFormatter::escape($remote_url)
                ),
                ''
            ]);
            exit(1);
        }

        $current_version = $this->manager->getCurrentVersionFromRemote(
            $remote
        );
        if ($current_version === '0.0.0') {
            $output->writeln(
                '<tip>No existing release. Next release will be first '
                . 'release.</tip>'
            );
        }

        $next_version = $this->manager->getNextVersion(
            $current_version,
            $input->getOption('type')
        );

        // Prompt to continue release.
        if ($input->isInteractive() &&
            !$output->isQuiet()
        ) {
            $prompt = new ConfirmationPrompt($this->getHelper('question'));
            $continue = $prompt->ask(
                $input,
                $output,
                sprintf(
                    'Ready to release new %s version <variable>%s</variable>. '
                    . 'Continue? <prompt>[Y/N]</prompt>',
                    $input->getOption('type'),
                    OutputFormatter::escape($next_version)
                )
            );

            /*$continue = $this->prompt->ask(
                sprintf(
                    'Ready to release new %s version %s. '
                    . 'Continue? %s' . PHP_EOL,
                    $result->options['type'],
                    Chalk::magenta($next_version),
                    Chalk::yellow('[Y/N]')
                ),
                Chalk::yellow('> ')
            );*/

            if (!$continue) {
                $output->writeln([
                    '<bold>Got it. Not releasing.</bold>',
                    ''
                ]);
                exit(0);
            }
        }

        $output->writeln([
            sprintf(
                '<header>Releasing version %s:</header>',
                OutputFormatter::escape($next_version)
            ),
            '',
        ]);

        $branch = $input->getOption('branch');
        $this->startCommand($output);
        $release_branch = $this->manager->createReleaseBranch(
            $branch,
            $remote,
            $next_version
        );
        if ($release_branch === null) {
            $this->handleError(
                $output,
                sprintf(
                    'could not create release branch from '
                    . '<variable>%s</variable>',
                    OutputFormatter::escape($branch)
                ),
                $this->manager->getLastError()
            );
        } else {
            $this->handleSuccess(
                $output,
                sprintf(
                    'created release branch <variable>%s</variable>',
                    OutputFormatter::escape($release_branch)
                )
            );
        }

        $message = $input->getOption('message');
        if ($message === '') {
            $message = sprintf(
                'Release version %s.',
                $next_version
            );
        }
        $this->startCommand($output);
        $success = $this->manager->createReleaseTag(
            $next_version,
            $message
        );
        if ($success) {
            $this->handleSuccess(
                $output,
                sprintf(
                    'tagged release with message <variable>%s</variable>',
                    OutputFormatter::escape($message)
                )
            );
        } else {
            $this->handleError(
                $output,
                sprintf(
                    'failed to create release tag for <variable>%s</variable>',
                    OutputFormatter::escape($next_version)
                ),
                $this->manager->getLastError()
            );
        }

        $this->startCommand($output);
        if ($this->manager->pushTagToRemote($next_version, $remote)) {
            $this->handleSuccess(
                $output,
                sprintf(
                    'pushed tag to <variable>%s</variable>',
                    OutputFormatter::escape($remote)
                )
            );
        } else {
            $this->handleError(
                $output,
                sprintf(
                    'could not push tag <variable>%s</variable> to remote '
                    . '<variable>%s</variable>',
                    OutputFormatter::escape($next_version),
                    OutputFormatter::escape($remote)
                ),
                $this->manager->getLastError()
            );
        }

        $this->startCommand($output);
        if ($this->manager->deleteBranch($release_branch)) {
            $this->handleSuccess(
                $output,
                sprintf(
                    'removed release branch <variable>%s</variable>',
                    OutputFormatter::escape($release_branch)
                )
            );
        } else {
            $this->handleError(
                $output,
                sprintf(
                    'could not delete release branch <variable>%s</variable>',
                    OutputFormatter::escape($release_branch)
                ),
                $this->manager->getLastError()
            );
        }

        $output->writeln([
            '',
            '<info>Success!</info>',
            '',
            'The composer repository will automatically update. It may take a '
            . 'few minutes for the release to appear at '
            . '<link>https://composer/</link>.',
            ''
        ]);
    }

    protected function startCommand(OutputInterface $output)
    {
        $output->write('<waiting>…</waiting> ');
    }

    protected function handleSuccess(OutputInterface $output, string $message)
    {
        $output->writeln(
            sprintf(
                "\r<success>✓</success> %s",
                $message
            )
        );
    }

    protected function handleError(
        OutputInterface $output,
        string $message,
        array $debug_output
    ) {
        $output->writeln([
            sprintf(
                "\r<failure>✗</failure> %s",
                $message
            ),
            ''
        ]);

// TODO
        /*
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
        */

        // END TODO
        exit(1);
    }
}
