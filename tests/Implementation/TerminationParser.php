<?php

declare(strict_types=1);

namespace webignition\StringParser\Tests\Implementation;

use webignition\StringParser\StringParser;

/**
 * A demonstration parser that returns exactly what is has been given up to a chosen character limit.
 */
class TerminationParser
{
    private const STATE_IN_VALUE = 1;

    private StringParser $stringParser;
    private int $count = 0;

    public function __construct(
        private int $limit = 10,
    ) {
        $this->stringParser = new StringParser([
            StringParser::STATE_UNKNOWN => function (StringParser $stringParser) {
                $stringParser->setState(self::STATE_IN_VALUE);
            },
            self::STATE_IN_VALUE => function (StringParser $stringParser) {
                ++$this->count;

                if ($this->count <= $this->limit) {
                    $stringParser->appendOutputString();
                }

                $stringParser->incrementPointer();
            },
        ]);
    }

    public function parse(string $input): string
    {
        $this->count = 0;

        return $this->stringParser->parse($input);
    }
}
