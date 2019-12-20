<?php

namespace webignition\StringParser\Tests;

use webignition\StringParser\StringParser;

/**
 * A simple demonstration parser that does nothing other than parse over and
 * return exactly what it has been given
 */
class PassThroughParser extends StringParser
{
    private const STATE_IN_VALUE = 1;

    protected function parseCurrentCharacter(): void
    {
        switch ($this->getCurrentState()) {
            case self::STATE_UNKNOWN:
                $this->setCurrentState(self::STATE_IN_VALUE);
                break;

            case self::STATE_IN_VALUE:
                    $this->appendOutputString();
                    $this->incrementCurrentCharacterPointer();

                break;
        }
    }
}
