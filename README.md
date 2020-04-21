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

Release Metadata File
---------------------
The **prepare-site** and **release-site** tools can use a file named
`release-metadata.ini` if it exists. The format of the file is as follows:

```ini
[site]
title = "My Human-Readable Title"  ; Human-readable site title for messages

[testing]
url = "https://$hostname/testpath" ; URL to visit to perform manual testing
command = "composer run test"      ; command to run automated tests
```

### Variable Interpolation

The metadata file may use the following variables inside values:

 - **$hostname** - the hostname of the machine where the release is being prepared.
 - **$branch** - the name of the current Git branch.

Other variables will be ignored and used verbatim.

### Lerna Monorepos

For sites using a lerna monorepo setup, the `scope.is_web` setting is used to control
which packages are built for a web deployment. Packages are built in the order they appear
in the metadata file, for example:

```
[shared]
scope.is_web = true

[web]
scope.is_web = true

```

Development
-----------
These tools uses [composer](https://getcomposer.org/). To test during
development, make sure you have the required packages installed by running
`composer install`.

You can run to tool using `./bin/package-release`.

Deployment
----------
To update tools used in staging after changes have been merged you first
must release the package using `package-release` for a working dir in 
`/so/packages/package-release` -- then ansible should be run against
staging servers using the composer_global tag. E.x.
`ansible-playbook -l dev site.yml --tags composer_global`
