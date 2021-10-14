<?php

namespace webignition\StringParser\Tests\Implementation;

use webignition\StringParser\StringParser;

/**
 * A simple demonstration parser that does nothing other than parse over and
 * return exactly what it has been given.
 */
class PassThroughParser
{
    private const STATE_IN_VALUE = 1;

    private StringParser $stringParser;

    public function __construct()
    {
        $this->stringParser = new StringParser([
            StringParser::STATE_UNKNOWN => function (StringParser $stringParser) {
                $stringParser->setState(self::STATE_IN_VALUE);
            },
            self::STATE_IN_VALUE => function (StringParser $stringParser) {
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
