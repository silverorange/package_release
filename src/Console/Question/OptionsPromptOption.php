<?php

namespace Silverorange\PackageRelease\Console\Question;

/**
 * @package   PackageRelease
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2020 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class OptionsPromptOption
{
    /**
     * @var string
     */
    protected $key = '';

    /**
     * @var string
     */
    protected $value = '';

    /**
     * @var string
     */
    protected $prompt = '';

    public function __construct(string $key, string $value, string $prompt)
    {
        $this->setKey($key);
        $this->setValue($value);
        $this->setPrompt($prompt);
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function setPrompt(string $prompt): self
    {
        $this->prompt = $prompt;
        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function matches($value): bool
    {
        return (
            \mb_strtolower($value) === \mb_strtolower($this->key)
            || \mb_strtolower($value) === \mb_strtolower($this->value)
        );
    }
}
