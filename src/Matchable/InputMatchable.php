<?php

declare(strict_types=1);

namespace Moon\Core\Matchable;

use Moon\Core\Input\InputInterface;

class InputMatchable implements MatchableInterface
{
    /**
     * @var string $input
     */
    private $input;

    /**
     * InputMatchable constructor.
     *
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $criteria): bool
    {
        return $criteria['patter'] === $this->input->commandName();
    }
}