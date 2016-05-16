<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

namespace silverorange\ModuleRelease;

/**
 * @package   ModuleRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2016 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class Manager
{
	const VERSION_MAJOR = 'major';
	const VERSION_MINOR = 'minor';
	const VERSION_MICRO = 'micro';

	/**
	 * Checks if the current directory is a git repository
	 *
	 * @return boolean true if the current directory is a git repository,
	 *                 otherwise false.
	 */
	public function isInGitRepo()
	{
		$package_git_repo = `git rev-parse --is-inside-work-tree 2>/dev/null`;
		return (trim($package_git_repo) === 'true');
	}

	/**
	 * Checks if the current directory is a composer module
	 *
	 * @return boolean true if the current directory is a composer module,
	 *                 otherwise false.
	 */
	public function isComposerModule()
	{
		return (file_exists('composer.json') && is_readable('composer.json'));
	}

	/**
	 * Gets the name of the current git repository
	 *
	 * @return string the name of the current git repository or null if it
	 *                could not be determined.
	 */
	public function getRepoName()
	{
		$repo = null;

		$remotes = explode("\n", `git remote -v`);
		$matches = array();
		$exp = '/^origin\s+.*\/([a-zA-Z0-9_-]+)\.git\s+\((?:fetch|push)\)$/';
		foreach ($remotes as $remote) {
			if (preg_match($exp, $remote, $matches) === 1) {
				$repo = $matches[1];
				break;
			}
		}

		return $repo;
	}

	/**
	 * Gets the name of the current composer module
	 *
	 * @return string the name of the current composer module or null if the
	 *                name could not be parsed from the composer.json file.
	 */
	public function getComposerModuleName()
	{
		$name = null;

		$data = json_decode('composer.json', true);
		if (isset($data['name'])) {
			$name = $data['name'];
		}

		return $name;
	}

	/**
	 * Gets a git remote's name based on its URL
	 *
	 * @param string $url the git URL of the remote.
	 *
	 * @return string the remote name, or null if no such remote exists in the
	 *                git repository.
	 */
	public function getRemoteByUrl($url)
	{
		$the_remote = null;

		$remotes = explode("\n", `git remote`);
		foreach ($remotes as $remote) {
			$escaped_remote = escapeshellarg($remote);
			$info = explode("\n", `git remote show -n $escaped_remote`);
			$expression = sprintf('/^  Fetch URL: %s/', preg_quote($url, '/'));
			if (preg_match($expression, $info[1]) === 1) {
				$the_remote = $remote;
				break;
			}
		}

		return $the_remote;
	}

	/**
	 * Creates a release branch based on a remote branch name
	 *
	 * @param string $parent  the parent branch name.
	 * @param string $remote  the remote repository name.
	 * @param string $version the release version.
	 *
	 * @return string the name of the new branch, or null if the branch could
	 *                not be created.
	 */
	public function createReleaseBranch($parent, $remote, $version)
	{
		$release = 'release-' . str_replace('.', '-', $version);

		$escaped_remote = escapeshellarg($remote);
		$escaped_parent = escapeshellarg($parent);
		$escaped_release = escapeshellarg($release);

		`git fetch $escaped_remote $escaped_parent`;

		$command = sprintf(
			'git checkout -b %s %s/%s',
			$escaped_release,
			$escaped_remote,
			$escaped_parent
		);

		$output = array();
		$return = 0;
		exec($command, $output, $return);

		if ($return !== 0) {
			$release = null;
		}

		return $release;
	}

	/**
	 * Gets the most recent version tag from the specified remote
	 *
	 * @param string $remote the name of the remote.
	 *
	 * @return string the most recent version tag, or 0.0.0 if no release
	 *                exists.
	 */
	public function getCurrentVersionFromRemote($remote)
	{
		$remote = escapeshellarg($remote);

		// get remote tags
		$tags = `git ls-remote --tags --refs $remote`;
		$tags = explode("\n", $tags);

		// filter out version rows and strip out commit ids
		$tags = array_filter(
			array_map(
				function ($line) {
					$matches = array();
					preg_match('/([0-9]+\.[0-9]+\.[0-9]+)/', $line, $matches);

					if (count($matches) === 2) {
						return $matches[1];
					}

					return null;
				},
				$tags
			),
			function ($line) {
				return ($line !== null);
			}
		);

		// sort by version number
		usort(
			$tags,
			'version_compare'
		);

		if (count($tags) === 0) {
			$tag = '0.0.0';
		} else {
			// get last tag
			$tag = end($tags);
		}

		return $tag;
	}

	/**
	 * Gets the next version for a release based on the specified version and
	 * release type
	 *
	 * @param string  $current_version the version of the current release.
	 * @param integer $type            optional. The release type. Should be
	 *                                 one of
	 *                                 {@link PackageRelease::VERSION_MAJOR},
	 *                                 {@link PackageRelease::VERSION_MINOR}, or
	 *                                 {@link PackageRelease::VERSION_MICRO}. If
	 *                                 not specified, a minor release is
	 *                                 used.
	 *
	 * @return string the next release version.
	 */
	public function getNextVersion(
		$current_version,
		$type = self::VERSION_MINOR
	) {
		$parts = explode('.', $current_version);

		if (count($parts) !== 3) {
			$next = '0.1.0';
		} else {
			switch ($type) {
			case self::VERSION_MAJOR:
				$next = ($parts[0] + 1) . '.' . $parts[1] . '.' . $parts[2];
				break;
			case self::VERSION_MICRO:
				$next = $parts[0] . '.' . $parts[1] . '.' . ($parts[2] + 1);
				break;
			case self::VERSION_MINOR:
			default:
				$next = $parts[0] . '.' . ($parts[1] + 1) . '.' . $parts[2];
				break;
			}
		}

		return $next;
	}
}
