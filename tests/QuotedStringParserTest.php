<?php

namespace webignition\StringParser\Tests;

use webignition\StringParser\ConcreteStringParser;

class QuotedStringParserTest extends \PHPUnit\Framework\TestCase
{
    private const QUOTE_DELIMITER = '"';
    private const ESCAPE_CHARACTER = '\\';

    private const STATE_IN_QUOTED_STRING = 1;
    private const STATE_LEFT_QUOTED_STRING = 2;
    private const STATE_INVALID_LEADING_CHARACTERS = 3;
    private const STATE_INVALID_TRAILING_CHARACTERS = 4;
    private const STATE_ENTERING_QUOTED_STRING = 5;
    private const STATE_INVALID_ESCAPE_CHARACTER = 6;

    private ConcreteStringParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new ConcreteStringParser([
            self::STATE_ENTERING_QUOTED_STRING => function (ConcreteStringParser $instance) {
                $instance->incrementCurrentCharacterPointer();
                $instance->setCurrentState(self::STATE_IN_QUOTED_STRING);
            },
            ConcreteStringParser::STATE_UNKNOWN => function (ConcreteStringParser $instance) {
                $state = self::QUOTE_DELIMITER === $instance->getCurrentCharacter()
                    ? self::STATE_ENTERING_QUOTED_STRING
                    : self::STATE_INVALID_LEADING_CHARACTERS;

                $instance->setCurrentState($state);
            },
            self::STATE_IN_QUOTED_STRING => function (ConcreteStringParser $instance) {
                $current = $instance->getCurrentCharacter();

                $isQuoteDelimiter = self::QUOTE_DELIMITER === $current;
                $isEscapeCharacter = self::ESCAPE_CHARACTER === $current;

                if ($isQuoteDelimiter) {
                    $isPreviousCharacterEscapeCharacter = self::ESCAPE_CHARACTER === $instance->getPreviousCharacter();

                    if ($isPreviousCharacterEscapeCharacter) {
                        $instance->appendOutputString();
                    } else {
                        $instance->setCurrentState(self::STATE_LEFT_QUOTED_STRING);
                    }

                    $instance->incrementCurrentCharacterPointer();
                }

                if ($isEscapeCharacter) {
                    $isNextCharacterQuoteDelimiter = self::QUOTE_DELIMITER == $instance->getNextCharacter();

                    if ($isNextCharacterQuoteDelimiter) {
                        $instance->incrementCurrentCharacterPointer();
                    } else {
                        $instance->setCurrentState(self::STATE_INVALID_ESCAPE_CHARACTER);
                    }
                }

                if (false === $isQuoteDelimiter && false === $isEscapeCharacter) {
                    $instance->appendOutputString();
                    $instance->incrementCurrentCharacterPointer();
                }
            },
            self::STATE_LEFT_QUOTED_STRING => function (ConcreteStringParser $instance) {
                if (false === $instance->isCurrentCharacterLastCharacter()) {
                    $instance->setCurrentState(self::STATE_INVALID_TRAILING_CHARACTERS);
                    $instance->incrementCurrentCharacterPointer();
                }
            },
            self::STATE_INVALID_LEADING_CHARACTERS => function (ConcreteStringParser $instance) {
                throw new \Exception('Invalid leading characters before first quote character', 1);
            },
            self::STATE_INVALID_ESCAPE_CHARACTER => function (ConcreteStringParser $instance) {
                throw new \Exception(
                    'Invalid escape character at position ' . $instance->getCurrentCharacterPointer(),
                    3
                );
            },
            self::STATE_INVALID_TRAILING_CHARACTERS => function (ConcreteStringParser $instance) {
                $exceptionMessage = implode(' ', [
                    'Invalid trailing characters after last quote character at position',
                    $instance->getCurrentCharacterPointer(),
                ]);

                throw new \Exception($exceptionMessage, 2);
            },
        ]);
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
