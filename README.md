Package Release
===============
Command-line tool to release new versions of composer packages.

<pre>
Usage:
  ./bin/package-release [options]

Options:
  -b branch, --branch=branch     Remote branch to use for release. If not
                                 specified, "master" is used.
  -m message, --message=message  Message to use for the release tag.
  -t type, --type=type           Release type. Must be one of "major",
                                 "minor", or "patch". If not specified,
                                 "minor" is used. Semver 2.0
                                 (https://semver.org/) is used to pick the
                                 next release number.
  -v, --verbose                  Sets verbosity level. Use multiples for
                                 more detail (e.g. "-vv").
  -q, --quiet                    Turn off all output.
  -y, --yes                      Non-interactive mode. Assume yes for
                                 prompts.
  -h, --help                     show this help message and exit
  --version                      show the program version and exit
</pre>

Development
-----------
This tool uses [composer](https://getcomposer.org/). To test during
development, make sure you have the required packages installed by running
`composer install`.

You can run to tool using `./bin/package-release`.
