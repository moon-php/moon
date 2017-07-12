<?php

declare(strict_types=1);

namespace Moon\Core\Command;

use Moon\Core\Input\InputInterface;

// TODO Think where call configure
interface CommandInterface
{
    /**
     * Configure the command
     *
     * @return void
     */
    public function configure(): void;

    /**
     * Execute the command
     *
     * @param InputInterface $input
     *
     * @return void
     */
    public function __invoke(InputInterface $input);

    /**
     * Print the description of the Command
     *
     * @return void
     */
    public function describe(): void;

    /**
     * Set a description for the command
     *
     * @param string $description
     *
     * @return void
     */
    public function setDescription(string $description): void;

    /**
     * Print the arguments of the Command
     *
     * @return void
     */
    public function arguments(): void;

    /**
     * Add an argument to the Command
     *
     * @param string $name
     * @param int $position
     *
     * @return void
     */
    public function addArgument(string $name, int $position): void;

    /**
     * Print the options of the Command
     *
     * @return void
     */
    public function options(): void;

    /**
     * Add an option to the Command
     *
     * @param string $name
     * @param string $alias
     *
     * @return void
     */
    public function addOption(string $name, string $alias = null): void;
}