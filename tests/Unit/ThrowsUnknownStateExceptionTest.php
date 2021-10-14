<?php

declare(strict_types=1);

namespace webignition\StringParser\Tests\Unit;

use webignition\StringParser\StringParser;
use webignition\StringParser\UnknownStateException;

class ThrowsUnknownStateExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testPassThroughParser(): void
    {
        $parser = new StringParser([]);

        self::expectExceptionObject(new UnknownStateException(0));

        $parser->parse('non-empty string');
    }
}
