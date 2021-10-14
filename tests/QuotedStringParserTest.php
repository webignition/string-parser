<?php

namespace webignition\StringParser\Tests;

use webignition\StringParser\QuotedStringParser;

class QuotedStringParserTest extends \PHPUnit\Framework\TestCase
{
    private QuotedStringParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new QuotedStringParser();
    }

    /**
     * @dataProvider parseValidInputDataProvider
     */
    public function testParseValidInput(string $input, string $expectedValue): void
    {
        $quotedString = $this->parser->parse($input);

        $this->assertEquals($expectedValue, $quotedString);
    }

    /**
     * @return array<mixed>
     */
    public function parseValidInputDataProvider(): array
    {
        return [
            'without inner quotes' => [
                'input' => '"foo"',
                'expectedValue' => 'foo',
            ],
            'with inner quotes' => [
                'input' => '"foo \"bar\" foobar"',
                'expectedValue' => 'foo "bar" foobar',
            ],
            'without inner quotes, utf8 cantonese' => [
                'input' => '"我隻氣墊船裝滿晒鱔"',
                'expectedValue' => '我隻氣墊船裝滿晒鱔',
            ],
            'with inner quotes, utf8 cantonese' => [
                'input' => '"我隻氣\"墊船裝\"滿晒鱔"',
                'expectedValue' => '我隻氣"墊船裝"滿晒鱔',
            ],
        ];
    }

    /**
     * @dataProvider parseInvalidInputDataProvider
     */
    public function testParseInvalidInput(string $input, \Exception $expectedException): void
    {
        $this->expectExceptionObject($expectedException);

        $this->parser->parse($input);
    }

    /**
     * @return array<mixed>
     */
    public function parseInvalidInputDataProvider(): array
    {
        return [
            'invalid leading characters' => [
                'input' => 'foo',
                'expectedException' => new \Exception('Invalid leading characters before first quote character', 1),
            ],
            'invalid trailing characters' => [
                'input' => '"foo" bar',
                'expectedException' => new \Exception(
                    'Invalid trailing characters after last quote character at position 6',
                    2
                ),
            ],
            'invalid escape characters' => [
                'input' => '"foo \bar"',
                'expectedException' => new \Exception('Invalid escape character at position 5', 3),
            ],
        ];
    }
}
