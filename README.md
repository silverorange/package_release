Module Release
==============
Command-line tool to release new versions of composer modules.

<pre>
Usage:
  ./bin/module-release [options]

Options:
  -b branch, --branch=branch     Remote branch to use for release. If not
                                 specified, "master" is used.
  -m message, --message=message  Message to use for the release tag.
  -t type, --type=type           Release type. Must be one of "major",
                                 "minor", or "micro". If not specified,
                                 "minor" is used.
  -v, --verbose                  Sets verbosity level. Use multiples for
                                 more detail (e.g. "-vv").
  -h, --help                     show this help message and exit
  --version                      show the program version and exit
</pre>

Development
-----------
This tool uses [composer](https://getcomposer.org/). To test during
development, make sure you have the required modules installed by running
`composer update`.

You can run to tool using `./bin/module-release`.
