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
 * - consider in each state what conditions cause the parser to change to a
 *   different state, or simply stay in the same state
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
 */
class StringParser
{
    public const STATE_UNKNOWN = 0;

    private int $state = self::STATE_UNKNOWN;

    /**
     * @var string[]
     */
    private array $characters = [];

    private string $output;

    /**
     * Pointer to position of current character.
     */
    private int $pointer = 0;

    private int $inputLength = 0;

    /**
     * @param \Closure[] $handlers
     */
    public function __construct(
        private array $handlers
    ) {
    }

    /**
     * @throws UnknownStateException
     */
    public function parse(string $input): string
    {
        $this->reset();

        $characters = preg_split('//u', $input, -1, PREG_SPLIT_NO_EMPTY);

        $this->characters = is_array($characters) ? $characters : [];
        $this->inputLength = count($this->characters);

        while ($this->pointer < $this->inputLength) {
            $handler = $this->handlers[$this->state] ?? null;
            if (null === $handler) {
                throw new UnknownStateException($this->state);
            }

            if ($handler instanceof \Closure) {
                ($handler)($this);
            }
        }

        return $this->output;
    }

    public function clearOutput(): void
    {
        $this->output = '';
    }

    public function stop(): void
    {
        $this->pointer = $this->inputLength;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function appendOutputString(): void
    {
        $this->output .= $this->getCurrentCharacter();
    }

    public function getCurrentCharacter(): ?string
    {
        return ($this->pointer < $this->inputLength)
            ? $this->characters[$this->pointer]
            : null;
    }

    public function getPreviousCharacter(): ?string
    {
        if (0 == $this->pointer) {
            return null;
        }

        $previousCharacterIndex = $this->pointer - 1;

        return ($previousCharacterIndex > $this->inputLength)
            ? null
            : $this->characters[$previousCharacterIndex];
    }

    public function getNextCharacter(): ?string
    {
        return ($this->pointer == $this->inputLength - 1)
            ? null
            : $this->characters[$this->pointer + 1];
    }

    public function getPointer(): int
    {
        return $this->pointer;
    }

    public function incrementPointer(): void
    {
        ++$this->pointer;
    }

    public function isCurrentCharacterFirstCharacter(): bool
    {
        if (0 != $this->pointer) {
            return false;
        }

        return !is_null($this->getCurrentCharacter());
    }

    public function isCurrentCharacterLastCharacter(): bool
    {
        return is_null($this->getNextCharacter());
    }

    public function getInput(): string
    {
        return implode('', $this->characters);
    }

    private function reset(): void
    {
        $this->output = '';
        $this->pointer = 0;
        $this->state = self::STATE_UNKNOWN;
    }
}
