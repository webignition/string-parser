<?php

namespace webignition\Tests\StringParser;

use PHPUnit_Framework_TestCase;

class ParseThroughParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider passThroughParserDataProvider
     *
     * @param string $input
     * @param string $expectedOutput
     */
    public function testPassThroughParser($input, $expectedOutput)
    {
        $parser = new PassThroughParser();
        $this->assertEquals($input, $parser->parse($expectedOutput));
    }

    /**
     * @return array
     */
    public function passThroughParserDataProvider()
    {
        return [
            [
                'input' => 'foo',
                'expectedOutput' => 'foo',
            ],
            [
                'input' => 'comes out the same as it goes in',
                'expectedOutput' => 'comes out the same as it goes in',
            ]
        ];
    }
}
