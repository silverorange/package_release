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
use Silverorange\PackageRelease\Config\ReleaseMetadata;
use Silverorange\PackageRelease\Console\Formatter\Style;
use Silverorange\PackageRelease\Console\Formatter\LineWrapper;
use Silverorange\PackageRelease\Console\Formatter\OutputFormatter as PackageReleaseOutputFormatter;
use Silverorange\PackageRelease\Console\Question\ConfirmationPrompt;
use Silverorange\PackageRelease\Builder\BuilderInterface;
use Silverorange\PackageRelease\Builder\EmberBuilder;
use Silverorange\PackageRelease\Builder\LaravelBuilder;
use Silverorange\PackageRelease\Builder\LegacyPHPBuilder;
use Silverorange\PackageRelease\Builder\LernaBuilder;
use Silverorange\PackageRelease\Builder\NodeBuilder;
use Silverorange\PackageRelease\Builder\ReactBuilder;
use Silverorange\PackageRelease\Builder\StaticBuilder;
use Silverorange\PackageRelease\Builder\TypeScriptBuilder;

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

    /**
     * @var Silverorange\PackageRelease\Config\ReleaseMetadata
     */
    protected $release_metadata = null;

    /**
     * @var string
     */
    protected $lernaPackage = null;

    public function __construct(
        ReleaseMetadata $metadata,
        Manager $manager,
        Style $style
    ) {
        parent::__construct();
        $this->setReleaseMetadata($metadata);
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

    public function setReleaseMetadata(ReleaseMetadata $metadata): self
    {
        $this->release_metadata = $metadata;
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
                        'type',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Release type. Must be one of "major", "minor", or '
                        . '"patch". Semver 2.0 (https://semver.org/) is used '
                        . 'to pick the next release number.',
                        'minor'
                    ),
                    new InputOption(
                        'lerna-package',
                        'l',
                        InputOption::VALUE_REQUIRED,
                        'Lerna package to release. Applicable only for lerna monorepos.',
                        'web'
                    ),
                ))
            );
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->validateInputOptions($input, $output);

        // Use custom OutputFormatter that works with mbstring function
        // overloading enabled.
        $formatter = $output->getFormatter();
        $output->setFormatter(
            new PackageReleaseOutputFormatter($formatter->isDecorated())
        );

        $this->style->execute($input, $output);

        $this->lernaPackage = $input->getOption('lerna-package');

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

        // if (!$this->isMonoRepo() && !$this->isInLiveDirectory()) {
        //     $output->writeln([
        //         'You must be in the site’s <variable>live</variable> '
        //         . 'directory to prepare a release.',
        //         ''
        //     ]);
        //     return 1;
        // }

        if ($this->isMonoRepo() && !$this->isInMonoRepoModule()) {
            $output->writeln([
                "You must be in a subdirectory of the monorepo’s <variable>"
                . 'live</variable> directory to prepare a release.',
                ''
            ]);
            return 1;
        }

        // This relies on the project's live repository being set up correctly,
        // but means it will work for sites across GitHub organziations without
        // any extra configuration.
        $remote = 'origin';

        $current_version = $this->manager->getCurrentVersionFromRemote(
            $remote,
            $this->getMonoRepoModuleName()
        );

        if ($current_version === '0.0.0') {
            $output->writeln([
                '<tip>No existing release. Next release will be first '
                . 'release.</tip>',
                '',
            ]);
        }

        $type = $input->getOption('type');
        if ($type === 'hot') { // support deprecated 'hot' release type.
            $type = 'patch';
        }

        $next_version = $this->manager->getNextVersion(
            $current_version,
            $type
        );

        $output->writeln([
            sprintf(
                '<header>Preparing release branch of %s for version '
                . '%s:</header>',
                OutputFormatter::escape($this->getSiteTitle()),
                OutputFormatter::escape($next_version)
            ),
            '',
        ]);

        $branch = ($type === 'patch') ? 'live' : 'master';
        $this->startCommand($output);
        $release_branch = $this->manager->createReleaseBranch(
            $branch,
            $remote,
            $next_version,
            ($type === 'patch') ? 'patch' : 'release'
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
                    '<info>%s project %s built successfully!</info>',
                    OutputFormatter::escape($builder->getTitle()),
                    OutputFormatter::escape($this->getSiteTitle())
                ),
                '',
            ]);

            if ($input->getOption('type') === 'patch') {
                $output->writeln(
                    'This is a patch release. Make and commit any required '
                    . 'changes to this branch before testing.'
                );

                if ($this->getTestingURL() !== '') {
                    $output->writeln('');
                    $output->write(
                        sprintf(
                            'The site can be tested at <link>%s</link>. ',
                            OutputFormatter::escape($this->getTestingURL())
                        )
                    );
                }
            } elseif ($this->getTestingURL() !== '') {
                $output->write(
                    sprintf(
                        'The site is ready to test at <link>%s</link>. ',
                        OutputFormatter::escape($this->getTestingURL())
                    )
                );
            }

            $output->writeln(
                'If testing is successful, the site may be released using '
                . 'the <variable>release-site</variable> tool.'
            );

            $testing_command = $this->getTestingCommand($builder);
            if ($testing_command != '') {
                $output->writeln([
                    '',
                    'Automated tests may be run with:',
                    sprintf(
                        '  <variable>%s</variable>',
                        OutputFormatter::escape($testing_command)
                    ),
                ]);
            }

            $output->writeln([
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

        if (!in_array($type, ['major', 'minor', 'patch', 'hot'])) {
            throw new InvalidOptionException(
                sprintf(
                    'Option "type" must be one of the following: "major", '
                    . '"minor", "patch" (got "%s").',
                    $type
                )
            );
        }
    }

    protected function getSiteTitle(): string
    {
        if ($this->isMonoRepo()) {
            $module = $this->getMonoRepoModuleName();
            $title = $this->release_metadata->get($module . '.site.title');
        } else {
            $title = $this->release_metadata->get('site.title');
        }

        return ($title === '') ? basename(dirname(getcwd())) : $title;
    }

    protected function isInLiveDirectory(): bool
    {
        $current_dir = getcwd();
        $site = basename(dirname($current_dir));

        // Strip drive letter in Windows paths
        $current_dir = str_replace('/^[A-Za-z]:/', '', $current_dir);

        // Consistify path with forward-slashes
        $path_parts = explode(DIRECTORY_SEPARATOR, $current_dir);
        $current_dir = implode('/', $path_parts);

        return ($current_dir === '/so/sites/' . $site . '/live');
    }

    protected function getMonoRepoModuleName(): string
    {
        $metadata = $this->release_metadata->all();
        $current_dir = getcwd();
        $module_name = basename($current_dir);

        if (array_key_exists($module_name, $metadata)) {
            return $module_name;
        }

        if ((new LernaBuilder())->isAppropriate() && $this->lernaPackage !== null) {
            return $this->lernaPackage;
        }

        // Return empty string if we cannot determine the module we are in. In
        // that case, we are not in a monorepo.
        return '';
    }

    protected function isInMonoRepoModule(): bool
    {
        return $this->getMonoRepoModuleName() !== '';
    }

    protected function isMonoRepo(): bool
    {
        $metadata = $this->release_metadata->all();
        return count($metadata) > 0 &&
            !(array_key_exists('site', $metadata) &&
            array_key_exists('testing', $metadata));
    }

    protected function getTestingCommand(BuilderInterface $builder): string
    {
        if ($this->isInMonoRepoModule()) {
            $module = $this->getMonoRepoModuleName();
            $testing_command = $this->release_metadata->get(
                $module . '.testing.command'
            );
        } else {
            $testing_command = $this->release_metadata->get('testing.command');
        }

        return $testing_command;
    }

    protected function getTestingURL(): string
    {
        if ($this->isInMonoRepoModule()) {
            $module = $this->getMonoRepoModuleName();
            $testing_url = $this->release_metadata->get(
                $module . '.testing.url'
            );
        } else {
            $testing_url = $this->release_metadata->get('testing.url');
        }

        return $testing_url;
    }

    protected function getLernaPackages(): Array
    {
        $module = $this->getMonoRepoModuleName();
        $module_metadata = $this->release_metadata->get($module);
        $packages = array_key_exists('build.prereqs', $module_metadata) ?
            explode(',', $module_metadata['build.prereqs']) : [];

        $packages[] = $module;
        return $packages;
    }

    protected function getBuilder(): BuilderInterface
    {
        // Order is important. First appropriate builder is used.
        $builders = [
            new LaravelBuilder(),
            new LegacyPHPBuilder(),
            new LernaBuilder($this->getLernaPackages()),
            new EmberBuilder(),
            new ReactBuilder(),
            new TypeScriptBuilder(),
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
