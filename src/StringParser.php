<?php

namespace webignition\StringParser;

/**
 * Abstract parser for parsing a string one character at a time, taking an input
 * string and returning an output string.
 *
 * The parser is state-based and provides a default state of 0 (STATE_UNKNOWN).
 *
 * Loops indefinitely until the current character pointer reaches the end of the
 * string, unless an exception breaks the flow.
 *
 * Concrete classes must implement parseCurrentCharacter() and in this method
 * must decide, based on the current state, the current character and the
 * characters surrounding it, whether to add the current character to the output,
 * whether to increment the current character pointer and whether to change the
 * current state.
 *
 * Within parseCurrentCharacter(), make good use of:
 *
 * - getCurrentState(): you might want to create a switch statement to behave
 *                     dependent on the state
 *
 * - getCurrentCharacter()
 * - getPreviousCharacter()
 * - getNextCharacter()
 * - getCurrentCharacterPointer()
 * - incrementCurrentCharacterPointer()
 * - setCurrentState()
 * - isCurrentCharacterFirstCharacter()
 * - stop(): if you're done all you need to, stop the parser
 *
 * Concrete class implementation thoughts:
 *
 * - consider what states your parser can be in, what are all the possible
 *   situations you could encounter when parsing a particular type of string?
 *
 * - list all states
 *
 * - implement a switch statement in parseCurrentCharacter() that takes into
 *   account all states
 *
 * - consider in each state what conditons cause the parser to change to a
 *   different state, or simpy stay in the same state
 *
 * - consider how an examination of the current, previous and next characters
 *   determine where state changes occur
 *
 * - consider in which states you want to append the current character to what is
 *   to be output
 *
 * - consider what states are invalid, and in those states throw exceptions
 *
 * - don't assume you're starting in a valid state, make use of the initial
 *   'unknown' state and figure out what state you're in
 *
 * - override the parse() method if you want to return not a string but perhaps
 *   an object instantiated from the parsed string
 *
 * - define your states as class constants, make it clear through the constant
 *   name what state you're in
 *
 */
abstract class StringParser
{
    const STATE_UNKNOWN = 0;

    /**
     * @var int
     */
    private $currentState = self::STATE_UNKNOWN;

    /**
     * @var string
     */
    private $inputString;

    /**
     * @var string
     */
    private $outputString;

    /**
     * Pointer to position of current character
     *
     * @var int
     */
    private $currentCharacterPointer = 0;

    /**
     * @var int
     */
    private $inputStringLength = 0;

    public function parse(string $inputString): string
    {
        $this->reset();
        $this->inputString = $inputString;
        $this->inputStringLength = strlen($inputString);

        while ($this->getCurrentCharacterPointer() < $this->getInputStringLength()) {
            $this->parseCurrentCharacter();
        }

        return $this->outputString;
    }

    protected function clearOutputString(): void
    {
        $this->outputString = '';
    }

    private function reset(): void
    {
        $this->outputString = '';
        $this->currentCharacterPointer = 0;
        $this->currentState = self::STATE_UNKNOWN;
    }

    abstract protected function parseCurrentCharacter();

    /**
     * Stop parsing
     */
    protected function stop(): void
    {
        $this->currentCharacterPointer = $this->getInputStringLength();
    }

    protected function getCurrentState(): int
    {
        return $this->currentState;
    }

    protected function setCurrentState(int $currentState): void
    {
        $this->currentState = $currentState;
    }

    protected function appendOutputString(): void
    {
        $this->outputString .= $this->getCurrentCharacter();
    }

    protected function getCurrentCharacter(): ?string
    {
        return ($this->getCurrentCharacterPointer() < $this->getInputStringLength())
            ? $this->inputString[$this->getCurrentCharacterPointer()]
            : null;
    }

    protected function getPreviousCharacter(): ?string
    {
        if ($this->getCurrentCharacterPointer() == 0) {
            return null;
        }

        $previousCharacterIndex = $this->getCurrentCharacterPointer() - 1;
        return ($previousCharacterIndex > $this->getInputStringLength())
            ? null
            : $this->inputString[$previousCharacterIndex];
    }

    protected function getNextCharacter(): ?string
    {
        return ($this->getCurrentCharacterPointer() == $this->getInputStringLength() - 1)
            ? null
            : $this->inputString[$this->getCurrentCharacterPointer() + 1];
    }

    protected function getInputStringLength(): int
    {
        return $this->inputStringLength;
    }

    protected function getCurrentCharacterPointer(): int
    {
        return $this->currentCharacterPointer;
    }

    protected function incrementCurrentCharacterPointer(): void
    {
        $this->currentCharacterPointer++;
    }

    protected function isCurrentCharacterFirstCharacter(): bool
    {
        if ($this->getCurrentCharacterPointer() != 0) {
            return false;
        }

        return !is_null($this->getCurrentCharacter());
    }

    protected function isCurrentCharacterLastCharacter(): bool
    {
        return is_null($this->getNextCharacter());
    }

    protected function getInputString(): string
    {
        return $this->inputString;
    }
}
