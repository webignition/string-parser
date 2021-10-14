<?php

declare(strict_types=1);

namespace webignition\StringParser;

class UnknownStateException extends \Exception
{
    public function __construct(private int $state)
    {
        parent::__construct(sprintf(
            'Unknown state: %d',
            $state
        ));
    }

    public function getState(): int
    {
        return $this->state;
    }
}
