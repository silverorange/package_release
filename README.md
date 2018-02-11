Package Release Tool Suite
==========================

Package Release Tool
--------------------
Command-line tool to release new versions of composer packages.

<pre>
Usage:
  package-release [options]

Options:
  -b, --branch=BRANCH    Remote branch to use for release. [default: "master"]
  -m, --message=MESSAGE  Message to use for the release tag.
  -t, --type=TYPE        Release type. Must be one of "major", "minor", or
                         "patch". Semver 2.0 (https://semver.org/) is used to
                         pick the next release number. [default: "minor"]
  -h, --help             Display this help message
  -q, --quiet            Do not output any message
  -V, --version          Display this application version
      --ansi             Force ANSI output
      --no-ansi          Disable ANSI output
  -n, --no-interaction   Do not ask any interactive question
  -v|vv|vvv, --verbose   Increase the verbosity of messages: 1 for normal
                         output, 2 for more verbose output and 3 for debug

Help:
  This tool is used to release new versions of composer packages. It uses
  Semver 2.0 to automatically pick the next version number and tag the release
  on GitHub.
</pre>

Prepare Site Tool
-----------------
Command-line tool to prepare websites for testing before release.

<pre>
Usage:
  prepare-site [options]

Options:
  -t, --type=TYPE       Release type. Must be one of "major", "minor", or
                        "patch". Semver 2.0 (https://semver.org/) is used to
                        pick the next release number. [default: "minor"]
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal
                        output, 2 for more verbose output and 3 for debug

Help:
  Prepares a release-branch of a site for testing and release. Must be used in
  a site’s live directory. This script should be run before release-site.
</pre>

Development
-----------
These tools uses [composer](https://getcomposer.org/). To test during
development, make sure you have the required packages installed by running
`composer install`.

You can run to tool using `./bin/package-release`.
