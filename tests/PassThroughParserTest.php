<?php

namespace webignition\StringParser\Tests;

class PassThroughParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider passThroughParserDataProvider
     *
     * @param string $input
     * @param string $expectedOutput
     */
    public function testPassThroughParser($input, $expectedOutput): void
    {
        $parser = new PassThroughParser();
        $this->assertEquals($input, $parser->parse($expectedOutput));
    }

    /**
     * @return array<mixed>
     */
    public function passThroughParserDataProvider(): array
    {
        return [
            'non-broken ascii' => [
                'input' => 'foo',
                'expectedOutput' => 'foo',
            ],
            'ascii with whitespace' => [
                'input' => 'comes out the same as it goes in',
                'expectedOutput' => 'comes out the same as it goes in',
            ],
            'utf8' => [
                'input' => '输入项',
                'expectedOutput' => '输入项',
            ]
        ];
    }
}
