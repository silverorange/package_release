<?php

namespace Silverorange\PackageRelease\Exception;

/**
 * Exception thrown when remote Git file can not be read
 *
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2019 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class GitRemoteFileException extends \Exception
{
    /**
     * @var string
     */
    protected $remote;

    /**
     * @var string
     */
    protected $branch;

    /**
     * @var string
     */
    protected $path;

    /**
     * Creates a new Git remote file exception
     *
     * @param string $message the exception message.
     * @param int    $code    optional. The exception code.
     * @param string $remote  optional. The remote name.
     * @param string $branch  optional. The remote branch name.
     * @param string $path    optional. The Git file path.
     */
    public function __construct(
        string $message,
        int $code = 0,
        string $remote = '',
        string $branch = '',
        string $path = ''
    ) {
        parent::__construct($message, $code);

        $this->remote = $remote;
        $this->branch = $branch;
        $this->path = $path;
    }

    /**
     * Gets the remote name for this exception.
     *
     * @return string
     */
    public function getRemote(): string
    {
        return $this->remote;
    }

    /**
     * Gets the branch name for this exception.
     *
     * @return string
     */
    public function getBranch(): string
    {
        return $this->branch;
    }

    /**
     * Gets the Git file path for this exception.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
