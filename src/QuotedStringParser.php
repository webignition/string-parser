<?php

namespace webignition\StringParser;

class QuotedStringParser
{
    private const QUOTE_DELIMITER = '"';
    private const ESCAPE_CHARACTER = '\\';

    private const STATE_IN_QUOTED_STRING = 1;
    private const STATE_LEFT_QUOTED_STRING = 2;
    private const STATE_INVALID_LEADING_CHARACTERS = 3;
    private const STATE_INVALID_TRAILING_CHARACTERS = 4;
    private const STATE_ENTERING_QUOTED_STRING = 5;
    private const STATE_INVALID_ESCAPE_CHARACTER = 6;

    private ConcreteStringParser $stringParser;

    public function __construct()
    {
        $this->stringParser = new ConcreteStringParser([
            self::STATE_ENTERING_QUOTED_STRING => function (ConcreteStringParser $instance) {
                $this->handleEnteringQuotedStringState($instance);
            },
            ConcreteStringParser::STATE_UNKNOWN => function (ConcreteStringParser $instance) {
                $this->handleUnknownState($instance);
            },
            self::STATE_IN_QUOTED_STRING => function (ConcreteStringParser $instance) {
                $this->handleInQuotedStringState($instance);
            },
            self::STATE_LEFT_QUOTED_STRING => function (ConcreteStringParser $instance) {
                $this->handleLeftQuotedStringState($instance);
            },
            self::STATE_INVALID_LEADING_CHARACTERS => function () {
                $this->handleInvalidLeadingCharactersState();
            },
            self::STATE_INVALID_ESCAPE_CHARACTER => function (ConcreteStringParser $instance) {
                $this->handleInvalidEscapeCharacterState($instance);
            },
            self::STATE_INVALID_TRAILING_CHARACTERS => function (ConcreteStringParser $instance) {
                $this->handleInvalidTrailingCharactersState($instance);
            },
        ]);
    }

    public function parse(string $input): string
    {
        return $this->stringParser->parse($input);
    }

    private function handleEnteringQuotedStringState(ConcreteStringParser $instance): void
    {
        $instance->incrementCurrentCharacterPointer();
        $instance->setCurrentState(self::STATE_IN_QUOTED_STRING);
    }

    private function handleUnknownState(ConcreteStringParser $instance): void
    {
        $instance->setCurrentState(
            self::QUOTE_DELIMITER === $instance->getCurrentCharacter()
                ? self::STATE_ENTERING_QUOTED_STRING
                : self::STATE_INVALID_LEADING_CHARACTERS
        );
    }

    private function handleInQuotedStringState(ConcreteStringParser $instance): void
    {
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
    }

    private function handleLeftQuotedStringState(ConcreteStringParser $instance): void
    {
        if (false === $instance->isCurrentCharacterLastCharacter()) {
            $instance->setCurrentState(self::STATE_INVALID_TRAILING_CHARACTERS);
            $instance->incrementCurrentCharacterPointer();
        }
    }

    private function handleInvalidLeadingCharactersState(): void
    {
        throw new \Exception('Invalid leading characters before first quote character', 1);
    }

    private function handleInvalidTrailingCharactersState(ConcreteStringParser $instance): void
    {
        throw new \Exception(
            sprintf(
                'Invalid trailing characters after last quote character at position %d',
                $instance->getCurrentCharacterPointer()
            ),
            2
        );
    }

    private function handleInvalidEscapeCharacterState(ConcreteStringParser $instance): void
    {
        throw new \Exception(
            'Invalid escape character at position ' . $instance->getCurrentCharacterPointer(),
            3
        );
    }
}
