<?php

declare(strict_types=1);

namespace Moon\Core\Command;

use Moon\Core\Input\InputInterface;

abstract class AbstractCommand implements CommandInterface
{
    /**
     * @var string $description
     */
    private $description;

    /**
     * @var array $arguments
     */
    private $arguments = [];

    /**
     * @var array $options
     */
    private $options = [];

    /**
     * {@inheritdoc}
     */
    abstract public function __invoke(InputInterface $input);

    /**
     * {@inheritdoc}
     */
    abstract public function configure(): void;

    /**
     * {@inheritdoc}
     */
    public function describe(): void
    {
        echo $this->description . PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function arguments(): void
    {
        if (empty($this->arguments)) {
            echo 'No argument' . PHP_EOL;

            return;
        }

        foreach ($this->arguments as $k => $argument) {
            echo "$argument number $k" . PHP_EOL;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addArgument(string $name, int $position): void
    {
        $this->arguments[$position] = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function options(): void
    {
        if (empty($this->options)) {
            echo 'No option' . PHP_EOL;

            return;
        }

        foreach ($this->options as $option) {
            if ($option['alias'] === null) {
                echo "{$option['name']} with no alias" . PHP_EOL;
                continue;
            }
            echo "{$option['name']} with alias '{$option['alias']}'" . PHP_EOL;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addOption(string $name, string $alias = null): void
    {
        $this->options[] = ['name' => $name, 'alias' => ''];
    }
}