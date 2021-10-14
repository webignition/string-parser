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

    private int $currentState = self::STATE_UNKNOWN;

    /**
     * @var string[]
     */
    private array $inputString = [];

    private string $outputString;

    /**
     * Pointer to position of current character.
     */
    private int $currentCharacterPointer = 0;

    private int $inputStringLength = 0;

    /**
     * @param \Closure[] $handlers
     */
    public function __construct(
        private array $handlers
    ) {
    }

    public function parse(string $inputString): string
    {
        $this->reset();

        $characters = preg_split('//u', $inputString, -1, PREG_SPLIT_NO_EMPTY);

        $this->inputString = is_array($characters) ? $characters : [];
        $this->inputStringLength = count($this->inputString);

        while ($this->getCurrentCharacterPointer() < $this->getInputStringLength()) {
            $state = $this->getCurrentState();
            $handler = $this->findHandler($state);

            if (null === $handler) {
                var_dump('Unhandled ' . $state);
                exit();
            }

            if ($handler instanceof \Closure) {
                ($handler)($this);
            }
        }

        return $this->outputString;
    }

    private function findHandler(int $state): ?callable
    {
        return $this->handlers[$state] ?? null;
    }

    protected function clearOutputString(): void
    {
        $this->outputString = '';
    }

//    abstract protected function parseCurrentCharacter(): void;

    /**
     * Stop parsing.
     */
    protected function stop(): void
    {
        $this->currentCharacterPointer = $this->getInputStringLength();
    }

    protected function getCurrentState(): int
    {
        return $this->currentState;
    }

    public function setCurrentState(int $currentState): void
    {
        $this->currentState = $currentState;
    }

    public function appendOutputString(): void
    {
        $this->outputString .= $this->getCurrentCharacter();
    }

    public function getCurrentCharacter(): ?string
    {
        return ($this->getCurrentCharacterPointer() < $this->getInputStringLength())
            ? $this->inputString[$this->getCurrentCharacterPointer()]
            : null;
    }

    public function getPreviousCharacter(): ?string
    {
        if (0 == $this->getCurrentCharacterPointer()) {
            return null;
        }

        $previousCharacterIndex = $this->getCurrentCharacterPointer() - 1;

        return ($previousCharacterIndex > $this->getInputStringLength())
            ? null
            : $this->inputString[$previousCharacterIndex];
    }

    public function getNextCharacter(): ?string
    {
        return ($this->getCurrentCharacterPointer() == $this->getInputStringLength() - 1)
            ? null
            : $this->inputString[$this->getCurrentCharacterPointer() + 1];
    }

    protected function getInputStringLength(): int
    {
        return $this->inputStringLength;
    }

    public function getCurrentCharacterPointer(): int
    {
        return $this->currentCharacterPointer;
    }

    public function incrementCurrentCharacterPointer(): void
    {
        ++$this->currentCharacterPointer;
    }

    public function isCurrentCharacterFirstCharacter(): bool
    {
        if (0 != $this->getCurrentCharacterPointer()) {
            return false;
        }

        return !is_null($this->getCurrentCharacter());
    }

    public function isCurrentCharacterLastCharacter(): bool
    {
        return is_null($this->getNextCharacter());
    }

    protected function getInputString(): string
    {
        return implode('', $this->inputString);
    }

    private function reset(): void
    {
        $this->outputString = '';
        $this->currentCharacterPointer = 0;
        $this->currentState = self::STATE_UNKNOWN;
    }
}
