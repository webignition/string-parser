<?php

namespace webignition\StringParser\Tests;

use webignition\StringParser\StringParser;

/**
 * A simple demonstration parser that does nothing other than parse over and
 * return exactly what it has been given
 */
class TerminationParser extends StringParser
{
    private const STATE_IN_VALUE = 1;

    /**
     * @var int
     */
    private $limit = 10;

    /**
     * @var int
     */
    private $count = 0;

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    protected function parseCurrentCharacter(): void
    {
        switch ($this->getCurrentState()) {
            case self::STATE_UNKNOWN:
                $this->setCurrentState(self::STATE_IN_VALUE);
                break;

            case self::STATE_IN_VALUE:
                $this->count++;

                if ($this->count <= $this->limit) {
                    $this->appendOutputString();
                }

                $this->incrementCurrentCharacterPointer();

                break;
        }
    }
}
