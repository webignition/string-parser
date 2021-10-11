<?php

namespace webignition\StringParser\Tests;

class TerminationParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider terminationParserDataProvider
     */
    public function testPassThroughParser(string $input, int $limit, string $expectedOutput): void
    {
        $parser = new TerminationParser();
        $parser->setLimit($limit);

        $this->assertEquals($expectedOutput, $parser->parse($input));
    }

    /**
     * @return array<mixed>
     */
    public function terminationParserDataProvider(): array
    {
        return [
            'non-broken ascii' => [
                'input' => 'foo',
                'limit' => 2,
                'expectedOutput' => 'fo',
            ],
            'ascii with whitespace' => [
                'input' => 'comes out the same as it goes in',
                'limit' => 7,
                'expectedOutput' => 'comes o',
            ],
            'utf8, cantonese, limit=9' => [
                'input' => '我隻氣墊船裝滿晒鱔',
                'limit' => 9,
                'expectedOutput' => '我隻氣墊船裝滿晒鱔',
            ],
            'utf8, cantonese, limit=3' => [
                'input' => '我隻氣墊船裝滿晒鱔',
                'limit' => 3,
                'expectedOutput' => '我隻氣',
            ]
        ];
    }
}
