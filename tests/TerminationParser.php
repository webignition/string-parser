<?php

namespace webignition\StringParser\Tests;

use webignition\StringParser\ConcreteStringParser;

/**
 * A demonstration parser that returns exactly what is has been given up to a chosen character limit.
 */
class TerminationParser
{
    private const STATE_IN_VALUE = 1;

    private ConcreteStringParser $stringParser;
    private int $count = 0;

    public function __construct(
        private int $limit = 10,
    ) {
        $this->stringParser = new ConcreteStringParser([
            ConcreteStringParser::STATE_UNKNOWN => function (ConcreteStringParser $stringParser) {
                $stringParser->setCurrentState(self::STATE_IN_VALUE);
            },
            self::STATE_IN_VALUE => function (ConcreteStringParser $stringParser) {
                $this->count++;

                if ($this->count <= $this->limit) {
                    $stringParser->appendOutputString();
                }

                $stringParser->incrementCurrentCharacterPointer();
            },
        ]);
    }

    public function parse(string $input): string
    {
        $this->count = 0;

        return $this->stringParser->parse($input);
    }
}
