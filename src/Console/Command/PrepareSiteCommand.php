<?php

namespace Silverorange\PackageRelease\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Silverorange\PackageRelease\Git\Manager;
use Silverorange\PackageRelease\Console\Formatter\Style;
use Silverorange\PackageRelease\Console\Formatter\LineWrapper;
use Silverorange\PackageRelease\Console\Question\ConfirmationPrompt;
use Silverorange\PackageRelease\Builder\BuilderInterface;
use Silverorange\PackageRelease\Builder\EmberBuilder;
use Silverorange\PackageRelease\Builder\LaravelBuilder;
use Silverorange\PackageRelease\Builder\LegacyPHPBuilder;
use Silverorange\PackageRelease\Builder\NodeBuilder;
use Silverorange\PackageRelease\Builder\ReactBuilder;
use Silverorange\PackageRelease\Builder\StaticBuilder;
/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2017-2018 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class PrepareSiteCommand extends Command
{
    /**
     * @var Silverorange\PackageRelease\Manager
     */
    protected $manager = null;

    /**
     * @var Silverorange\PackageRelease\Style
     */
    protected $style = null;

    public function __construct(Manager $manager, Style $style)
    {
        parent::__construct();
        $this->setManager($manager);
        $this->setStyle($style);
    }

    public function setManager(Manager $manager): self
    {
        $this->manager = $manager;
        return $this;
    }

    public function setStyle(Style $style): self
    {
        $this->style = $style;
        return $this;
    }

    protected function configure(): void
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('prepare-site')

            // the short description shown while running "php bin/console list"
            ->setDescription('Prepares a release-branch of a site for testing.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp(
                'Prepares a release-branch of a site for testing and release. '
                . 'Must be used in a site’s live directory. This script '
                . 'should be run before <comment>release-site</comment>.'
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

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): void {
        $this->validateInputOptions($input, $output);
        $this->style->execute($input, $output);

        if (!$this->manager->isInGitRepo()) {
            $output->writeln([
                'This tool must be run from a git repository.',
                '',
            ]);
            return 1;
        }

        $repo_name = $this->manager->getRepoName();
        if ($repo_name === null) {
            $output->writeln([
                'Could not find git repository name. Git repository '
                . 'must have a remote named <variable>origin</variable>.',
                '',
            ]);
            return 1;
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
            return 1;
        }

        $current_version = $this->manager->getCurrentVersionFromRemote(
            $remote
        );
        if ($current_version === '0.0.0') {
            $output->writeln([
                '<tip>No existing release. Next release will be first '
                . 'release.</tip>',
                '',
            ]);
        }

        $next_version = $this->manager->getNextVersion(
            $current_version,
            $input->getOption('type')
        );
        $output->writeln([
            sprintf(
                '<header>Preparing release branch for version %s:</header>',
                OutputFormatter::escape($next_version)
            ),
            '',
        ]);

        $branch = $input->getOption('branch');
        $this->startCommand($output);
        $release_branch = $this->manager->createReleaseBranch(
            $branch,
            $remote,
            $next_version,
            $input->getOption('type') === 'patch' ? 'patch' : 'release'
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
            return 1;
        } else {
            $this->handleSuccess(
                $output,
                sprintf(
                    'created release branch <variable>%s</variable>',
                    OutputFormatter::escape($release_branch)
                )
            );
        }

        $builder = $this->getBuilder();
        $output->writeln([
            '',
            sprintf(
                '<header>Building %s project:</header>',
                OutputFormatter::escape($builder->getTitle())
            ),
            '',
        ]);

        if ($builder->build($output)) {
            $output->writeln([
                '',
                sprintf(
                    '<info>%s project built successfully!</info>',
                    OutputFormatter::escape($builder->getTitle())
                ),
                '',
            ]);

            if ($this->getOption('type') === 'patch') {
                $output->writeln([
                    'This is a patch release. Make and commit any required '
                    . 'changes to this branch before testing.',
                    ''
                    sprintf(
                        'The site is can be tested at <link>%s</link>. If '
                        . 'testing is successful, the site may be released '
                        . 'using the <variable>release-site</variable> tool.',
                        OutputFormatter::escape($this->getTestingURL())
                    ),
                ]);
            } else {
                $output->writeln([
                    sprintf(
                        'The site is ready to test at <link>%s</link>. If '
                        . 'testing is successful, the site may be released '
                        . 'using the <variable>release-site</variable> tool.',
                        OutputFormatter::escape($this->getTestingURL())
                    )
                ]);
            }

            $output->writeln([
                '',
                sprintf(
                    'Automated tests may be run with <variable>%s</variable>',
                    OutputFormatter::escape($this->getTestingCommand())
                ),
                '',
                'If testing fails, you can revert back to the live branch:',
                '  <variable>git checkout live</variable>',
                sprintf(
                    '  <variable>git branch -D %s</variable>',
                    OutputFormatter::escape($release_branch)
                ),
                '',
            ]);
        } else {
            return 1;
        }

        return 0;
    }

    protected function validateInputOptions(
        InputInterface $input,
        OutputInterface $output
    ): void {
        $type = $input->getOption('type');

        if (!in_array($type, ['major', 'minor', 'patch', 'micro'])) {
            throw new InvalidOptionException(
                sprintf(
                    'Option "type" must be one of the following: "major", '
                    . '"minor", "patch" (got "%s").',
                    $type
                )
            );
        }
    }

    protected function getTestingCommand(): string
    {
        //return 'yarn test';
        //return 'npm test';
        //return 'ember test';
        return 'composer run test';
    }

    protected function getTestingURL(): string
    {
        return 'https://www.google.com';
    }

    protected function getBuilder(): BuilderInterface
    {
        // Order is important. First appropriate builder is used.
        $builders = [
            new LaravelBuilder(),
            new LegacyPHPBuilder(),
            new EmberBuilder(),
            new ReactBuilder(),
            new NodeBuilder(),
            new StaticBuilder(),
        ];

        $found_builder = false;
        foreach ($builders as $builder) {
            if ($builder->isAppropriate()) {
                return $builder;
            }
        }

        // Fall back to static site (no build process).
        return new StaticBuilder();
    }

    protected function startCommand(OutputInterface $output): void
    {
        $output->write('<waiting>…</waiting> ');
    }

    protected function handleSuccess(
        OutputInterface $output,
        string $message
    ): void {
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
    ): void {
        $output->writeln([
            sprintf(
                "\r<failure>✗</failure> %s",
                $message
            ),
            ''
        ]);

        $wrapped_lines = (new LineWrapper())->wrap($debug_output, 76, '  ');
        $output->writeln(array_map(function ($line) {
            return sprintf(
                '<output>%s</output>',
                OutputFormatter::escape($line)
            );
        }, $wrapped_lines));
        $output->writeln('');
    }
}
