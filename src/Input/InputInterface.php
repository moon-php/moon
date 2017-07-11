<?php

declare(strict_types=1);

namespace Moon\Core\Input;

interface InputInterface
{
    /**
     * Return an associative array
     *
     * @return array
     */
    public function options(): array;

    /**
     * Return true if the option has been passed, false otherwise
     *
     * @param string $option
     * @param string $alias
     *
     * @return bool
     */
    public function hasOption(string $option, string $alias = ''): bool;

    /**
     * Return the passed option value, null if no value was found or option has not been passed
     *
     * @param string $option
     * @param string $alias
     *
     * @return null|string
     */
    public function getOptionValue(string $option, string $alias = ''):? string;

    /**
     * Return an array of arguments
     *
     * @return array
     */
    public function arguments(): array;

    /**
     * Return true if an argument has been passed, false otherwise
     *
     * @param int $index
     *
     * @return bool
     */
    public function hasArgument(int $index): bool;

    /**
     * Return the passed argument value, null if no value was found or argument has not been passed
     *
     * @param int $index
     *
     * @return null|string
     */
    public function getArgumentValue(int $index):? string;

    /**
     * Return the name of the command
     *
     * @return string
     */
    public function commandName(): string;
}