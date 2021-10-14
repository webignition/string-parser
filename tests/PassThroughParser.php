<?php

namespace webignition\StringParser\Tests;

use webignition\StringParser\ConcreteStringParser;

/**
 * A simple demonstration parser that does nothing other than parse over and
 * return exactly what it has been given.
 */
class PassThroughParser
{
    private const STATE_IN_VALUE = 1;

    private ConcreteStringParser $stringParser;

    public function __construct()
    {
        $this->stringParser = new ConcreteStringParser([
            ConcreteStringParser::STATE_UNKNOWN => function (ConcreteStringParser $stringParser) {
                $stringParser->setCurrentState(self::STATE_IN_VALUE);
            },
            self::STATE_IN_VALUE => function (ConcreteStringParser $stringParser) {
                $stringParser->appendOutputString();
                $stringParser->incrementCurrentCharacterPointer();
            },
        ]);
    }

    public function parse(string $input): string
    {
        return $this->stringParser->parse($input);
    }
}
